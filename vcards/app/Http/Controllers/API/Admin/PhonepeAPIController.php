<?php

namespace App\Http\Controllers\API\Admin;

use App\Models\Nfc;
use App\Models\Plan;
use App\Models\Vcard;
use App\Models\NfcOrders;
use App\Models\CouponCode;
use App\Models\Transaction;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Models\AffiliateUser;
use App\Mail\AdminNfcOrderMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\NfcOrderTransaction;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\AppBaseController;
use App\Mail\SubscriptionPaymentSuccessMail;
use App\Repositories\SubscriptionRepository;

class PhonepeAPIController extends AppBaseController
{
    private SubscriptionRepository $subscriptionRepository;

    public function __construct(SubscriptionRepository $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }
    public function onBoard(Request $request): JsonResponse
    {
        $planId = $request->input('planId');
        $customFieldId = $request->input('customFieldId');
        $couponCodeId = $request->input('couponCodeId');
        $couponCode = $request->input('couponCode');

        $plan = Plan::with('currency')->findOrFail($planId);

        if ($plan->currency->currency_code !== 'INR') {
            return $this->sendError(
                __('messages.placeholder.this_currency_is_not_supported_phonepe')
            );
        }

        if ($plan->custom_select == 1) {
            if (empty($customFieldId)) {
                return $this->sendError(
                    'Please select a custom option for this plan.'
                );
            }

            $validCustomField = $plan->planCustomFields()
                ->where('id', $customFieldId)
                ->exists();

            if (!$validCustomField) {
                return $this->sendError(
                    'Invalid custom field selection for this plan.'
                );
            }
        } else {
            if (!empty($customFieldId)) {
                return $this->sendError(
                    'This plan does not have custom options.'
                );
            }
        }

        if (!empty($couponCodeId) && !empty($couponCode)) {
            $couponCodeRecord = CouponCode::where('id', $couponCodeId)
                ->where('coupon_name', $couponCode)
                ->where('status', 1)
                ->first();

            if (!$couponCodeRecord) {
                return $this->sendError('Invalid or inactive coupon code.');
            }

            if (now()->gt($couponCodeRecord->expire_at)) {
                return $this->sendError('This coupon code has expired.');
            }

            if (
                $couponCodeRecord->coupon_limit !== null &&
                $couponCodeRecord->coupon_limit_left <= 0
            ) {
                return $this->sendError(
                    'This coupon code has reached its usage limit.'
                );
            }
        }

        $currentSubscription = getCurrentSubscription();

        if (
            $currentSubscription &&
            $currentSubscription->plan_id == $planId &&
            now()->lt($currentSubscription->ends_at)
        ) {
            return $this->sendError(
                'You already have this active subscription plan.'
            );
        }

        $data = $this->subscriptionRepository->manageSubscription(
            $request->all()
        );

        if (isset($data['status']) && $data['status'] === true) {
            return $this->sendResponse([
                'plan' => $data['subscriptionPlan'],
            ], 'Plan activated successfully.');
        }

        if (!isset($data['subscription'])) {
            return $this->sendError('Failed to create subscription.');
        }

        $subscription = $data['subscription'];
        $amount = $data['amountToPay'];

        $merchantOrderId = 'sub_' . now()->format('dmYHis') . rand(100000, 999999);

        $callbackUrl = route('phonepe.subscription.success', [
            'subscriptionId' => $subscription->id,
            'merchantOrderId' => $merchantOrderId,
        ]);

        $config = $this->getPhonePeConfig($callbackUrl);
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            return $this->sendError('PhonePe authentication failed.', 500);
        }

        $payload = [
            'merchantOrderId' => $merchantOrderId,
            'amount' => (int) ($amount * 100),
            'callbackUrl' => $callbackUrl,
            'expireAfter' => 1200,
            'paymentFlow' => [
                'type' => 'PG_CHECKOUT',
                'merchantUrls' => [
                    'redirectUrl' => $callbackUrl,
                ],
            ],
        ];

        $response = $this->makePhonePePaymentRequest(
            $payload,
            $config,
            $accessToken
        );

        if (!$response || empty($response->redirectUrl)) {
            Log::error('PhonePe subscription create failed', [
                'response' => $response,
            ]);

            return $this->sendError(
                'Unable to create PhonePe payment.',
                500
            );
        }

        return $this->sendResponse([
            'subscription_id' => $subscription->id,
            'payment_type' => 'phonepe',
            'checkout_url' => $response->redirectUrl,
            'merchant_order_id' => $merchantOrderId,
            'amount' => $amount,
            'currency' => 'INR',
        ], 'PhonePe subscription checkout created successfully.');
    }

    public function paymentSuccess(Request $request): JsonResponse
    {
        $user = Auth::user();
        if ($user && $user->email === 'admin@vcard.com') {
            return $this->sendError(
                'Seems, you are not allowed to access this record.',
                403
            );
        }

        $request->validate([
            'subscriptionId'   => 'required|integer|exists:subscriptions,id',
            'merchantOrderId'  => 'required|string',
        ]);

        $subscriptionId   = $request->subscriptionId;
        $merchantOrderId  = $request->merchantOrderId;

        $subscription = Subscription::findOrFail($subscriptionId);

        $merchantId = getSelectedPaymentGateway('phonepe_merchant_id');

        if (empty($merchantId)) {
            return $this->sendError('PhonePe credentials is not set');
        }

        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return $this->sendError('PhonePe authentication failed.', 500);
        }

        $response = $this->verifyPhonePePayment(
            $merchantId,
            $merchantOrderId,
            $this->getPhonePeConfig(),
            $accessToken
        );

        if (
            !$response ||
            !isset($response->state) ||
            $response->state !== 'COMPLETED'
        ) {
            return $this->sendError('Payment verification failed.', 400);
        }

        if (Transaction::where('transaction_id', $merchantOrderId)->exists()) {
            return $this->sendError('Payment already processed.', 409);
        }

        DB::beginTransaction();

        try {
            $amount = $response->amount / 100;

            $subscription->update([
                'payment_type' => Subscription::PHONEPE,
                'status'       => Subscription::ACTIVE,
            ]);

            Subscription::whereTenantId($subscription->tenant_id)
                ->where('id', '!=', $subscriptionId)
                ->where('status', '!=', Subscription::REJECT)
                ->update(['status' => Subscription::INACTIVE]);

            $transaction = Transaction::create([
                'tenant_id'     => $subscription->tenant_id,
                'transaction_id'=> $merchantOrderId,
                'type'          => Subscription::PHONEPE,
                'amount'        => $amount,
                'status'        => Subscription::ACTIVE,
                'meta'          => json_encode($response),
            ]);

            $subscription->update([
                'transaction_id' => $transaction->id,
            ]);

            $affiliateAmount     = getSuperAdminSettingValue('affiliation_amount');
            $affiliateAmountType = getSuperAdminSettingValue('affiliation_amount_type');

            if ($affiliateAmountType == 1) {
                AffiliateUser::whereUserId(getLogInUserId())
                    ->where('amount', 0)
                    ->withoutGlobalScopes()
                    ->update([
                        'amount'      => $affiliateAmount,
                        'is_verified' => 1,
                    ]);
            } elseif ($affiliateAmountType == 2) {
                $affiliateValue = $amount * $affiliateAmount / 100;

                AffiliateUser::whereUserId(getLogInUserId())
                    ->where('amount', 0)
                    ->withoutGlobalScopes()
                    ->update([
                        'amount'      => $affiliateValue,
                        'is_verified' => 1,
                    ]);
            }

            $user = getLogInUser();

            Mail::to($user->email)->send(
                new SubscriptionPaymentSuccessMail([
                    'subscriptionID' => $subscriptionId,
                    'amountToPay'    => $amount,
                    'planName'       => $subscription->plan->name,
                    'first_name'     => $user->first_name,
                    'last_name'      => $user->last_name,
                ])
            );

            DB::commit();

            return $this->sendResponse([
                'subscription_id' => $subscriptionId,
                'transaction_id'  => $transaction->id,
                'amount'          => $amount,
                'status'          => 'active',
            ], 'Payment completed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PhonePe subscription success error: ' . $e->getMessage());

            return $this->sendError(
                'Payment processing failed: ' . $e->getMessage(),
                500
            );
        }
    }

    public function nfcOrder(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|integer|exists:nfc_orders,id',
        ]);

        $nfcOrder = NfcOrders::with('nfcCard')->findOrFail($request->order_id);

        $baseAmount = $nfcOrder->nfcCard->price * $nfcOrder->quantity;

        $taxValue = getSuperAdminSettingValue('nfc_tax_value') ?? 0;
        $taxAmount = $taxValue > 0 ? ($baseAmount * $taxValue / 100) : 0;
        $totalAmount = $baseAmount + $taxAmount;

        $merchantOrderId = 'nfc_' . now()->format('dmYHis') . rand(100000, 999999);

        $callbackUrl = route('phonepe.nfc.success', [
            'order_id' => $nfcOrder->id,
            'merchantOrderId' => $merchantOrderId,
        ]);

        $config = $this->getPhonePeConfig($callbackUrl);
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            return $this->sendError('PhonePe authentication failed.', 500);
        }

        $payload = [
            'merchantOrderId' => $merchantOrderId,
            'amount' => (int) ($totalAmount * 100),
            'callbackUrl' => $callbackUrl,
            'expireAfter' => 1200,
            'paymentFlow' => [
                'type' => 'PG_CHECKOUT',
                'merchantUrls' => [
                    'redirectUrl' => $callbackUrl,
                ],
            ],
        ];

        $response = $this->makePhonePePaymentRequest($payload, $config, $accessToken);

        if (empty($response->redirectUrl)) {
            return $this->sendError('Unable to create PhonePe payment.', 500);
        }

        return $this->sendResponse([
            'checkout_url' => $response->redirectUrl,
            'order_id' => $nfcOrder->id,
            'merchant_order_id' => $merchantOrderId,
            'amount' => round($totalAmount, 2),
        ], 'PhonePe NFC checkout created successfully.');
    }

    public function nfcOrderSuccess(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|integer|exists:nfc_orders,id',
            'merchantOrderId' => 'required|string',
        ]);

        $orderId = $request->order_id;
        $merchantOrderId = $request->merchantOrderId;

        $nfcOrder = NfcOrders::findOrFail($orderId);
        $config = $this->getPhonePeConfig();
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            return $this->sendError('PhonePe authentication failed.', 500);
        }

        $merchantId = getSelectedPaymentGateway('phonepe_merchant_id');

        $response = $this->verifyPhonePePayment(
            $merchantId,
            $merchantOrderId,
            $config,
            $accessToken
        );

        if ($response->state !== 'COMPLETED') {
            return $this->sendError('Payment not completed.', 400);
        }

        if (
            NfcOrderTransaction::where('transaction_id', $merchantOrderId)->exists()
        ) {
            return $this->sendError('Payment already processed.', 409);
        }

        DB::beginTransaction();

        try {
            $amount = $response->amount / 100;

            $taxValue = getSuperAdminSettingValue('nfc_tax_value') ?? 0;
            $tax = $taxValue > 0 ? $taxValue : null;

            $transaction = NfcOrderTransaction::create([
                'nfc_order_id' => $orderId,
                'type' => NfcOrders::PHONEPE,
                'transaction_id' => $merchantOrderId,
                'amount' => $amount,
                'user_id' => $nfcOrder->user_id,
                'status' => NfcOrders::SUCCESS,
                'tax' => $tax,
                'meta' => json_encode($response),
            ]);

            $nfcOrder->update([
                'status' => NfcOrders::SUCCESS,
                'payment_type' => NfcOrders::PHONEPE,
            ]);

            $vcardName = optional(Vcard::find($nfcOrder->vcard_id))->name;
            $cardType = optional(Nfc::find($nfcOrder->card_type))->name;

            Mail::to(getSuperAdminSettingValue('email'))
                ->send(new AdminNfcOrderMail($nfcOrder, $vcardName, $cardType));

            DB::commit();

            return $this->sendResponse([
                'order_id' => $orderId,
                'transaction_id' => $transaction->id,
                'amount' => $amount,
                'status' => 'success',
            ], 'NFC order payment completed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PhonePe NFC success error: ' . $e->getMessage());

            return $this->sendError('Payment processing failed.', 500);
        }
    }

    protected function getPhonePeConfig(?string $callbackUrl = null): array
    {
        return [
            'merchantId' => getSelectedPaymentGateway('phonepe_merchant_id'),
            'clientId' => getSelectedPaymentGateway('phonepe_merchant_user_id'),
            'clientVersion' => getSelectedPaymentGateway('phonepe_salt_index'),
            'clientSecret' => getSelectedPaymentGateway('phonepe_salt_key'),
            'tokenUrl' => getSelectedPaymentGateway('phonepe_env') === 'production'
                ? 'https://api.phonepe.com/apis/identity-manager/v1/oauth/token'
                : 'https://api-preprod.phonepe.com/apis/pg-sandbox/v1/oauth/token',
            'baseUrl' => getSelectedPaymentGateway('phonepe_env') === 'production'
                ? 'https://api.phonepe.com/apis/pg'
                : 'https://api-preprod.phonepe.com/apis/pg-sandbox',
            'callbackUrl' => $callbackUrl,
        ];
    }

    public function getAccessToken(): ?string
    {
        $merchantId    = getSelectedPaymentGateway('phonepe_merchant_id');
        $clientId      = getSelectedPaymentGateway('phonepe_merchant_user_id');
        $clientVersion = getSelectedPaymentGateway('phonepe_salt_index');
        $clientSecret  = getSelectedPaymentGateway('phonepe_salt_key');

        $tokenUrl = getSelectedPaymentGateway('phonepe_env') === 'production'
            ? 'https://api.phonepe.com/apis/identity-manager/v1/oauth/token'
            : 'https://api-preprod.phonepe.com/apis/pg-sandbox/v1/oauth/token';

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $tokenUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'client_version'=> $clientVersion,
                'grant_type'    => 'client_credentials',
            ]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        $data = json_decode($response, true);

        return $data['access_token'] ?? null;
    }

    protected function makePhonePePaymentRequest(array $payload, array $config, string $accessToken)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $config['baseUrl'] . '/checkout/v2/pay',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: O-Bearer ' . $accessToken,
                'X-MERCHANT-ID: ' . $config['merchantId'],
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response);
    }

    protected function verifyPhonePePayment(
        string $merchantId,
        string $merchantOrderId,
        array $config,
        string $accessToken
    ) {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $config['baseUrl'] .
                '/checkout/v2/order/' . $merchantOrderId . '/status?details=true',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: O-Bearer ' . $accessToken,
                'X-MERCHANT-ID: ' . $merchantId,
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response);
    }
}
