<?php

namespace App\Http\Controllers\API\Admin;

use App\Models\Nfc;
use App\Models\Plan;
use Iyzipay\Options;
use App\Models\Vcard;
use App\Models\NfcOrders;
use App\Models\CouponCode;
use Laracasts\Flash\Flash;
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

class IyzicoAPIController extends AppBaseController
{
    protected Options $options;
    protected ?int $userId = null;

    public function setUserId(?int $userId = null)
    {
        $this->userId = $userId;
        $this->initializeOptions();
    }

    private $subscriptionRepository;

    public function __construct(SubscriptionRepository $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    protected function initializeOptions()
    {
        $clientId = getSelectedPaymentGateway('iyzico_key');

        $clientSecret = getSelectedPaymentGateway('iyzico_secret');

        $mode = getSelectedPaymentGateway('iyzico_mode');

        if (!$clientId || !$clientSecret) {
            throw new \Exception('Iyzico credentials are missing.');
        }

        $this->options = new Options();
        $this->options->setApiKey($clientId);
        $this->options->setSecretKey($clientSecret);
        $this->options->setBaseUrl(
            $mode === 'sandbox'
                ? 'https://sandbox-api.iyzipay.com'
                : 'https://api.iyzipay.com'
        );
    }

    public function iyzicoOnBoard(Request $request): JsonResponse
    {
        $planId = $request->input('planId');
        $customFieldId = $request->input('customFieldId');
        $couponCodeId = $request->input('couponCodeId');
        $couponCode = $request->input('couponCode');

        $plan = Plan::with('currency')->findOrFail($planId);

        if (strtoupper($plan->currency->currency_code) !== 'TRY') {
            return $this->sendError('Iyzico supports only TRY currency.');
        }

        if ($plan->custom_select == 1) {
            if (empty($customFieldId)) {
                return $this->sendError('Please select a custom option for this plan.');
            }

            if (!$plan->planCustomFields()->where('id', $customFieldId)->exists()) {
                return $this->sendError('Invalid custom field selection.');
            }
        } elseif (!empty($customFieldId)) {
            return $this->sendError('This plan does not have custom options.');
        }

        if (!empty($couponCodeId) && !empty($couponCode)) {
            $coupon = CouponCode::where('id', $couponCodeId)
                ->where('coupon_name', $couponCode)
                ->where('status', 1)
                ->first();

            if (!$coupon || now()->gt($coupon->expire_at) || $coupon->coupon_limit_left <= 0) {
                return $this->sendError('Invalid or expired coupon code.');
            }
        }

        $current = getCurrentSubscription();
        if ($current && $current->plan_id == $planId && now()->lt($current->ends_at)) {
            return $this->sendError('You already have this active subscription plan.');
        }

        // Create subscription
        $data = $this->subscriptionRepository->manageSubscription($request->all());

        if (isset($data['status']) && $data['status'] === true) {
            return $this->sendResponse([
                'plan' => $data['subscriptionPlan'],
            ], 'Plan activated successfully.');
        }

        if (!isset($data['subscription'])) {
            return $this->sendError('Failed to create subscription.');
        }

        $subscription = $data['subscription'];

        // Initialize Iyzico
        $this->initializeOptions();

        $paymentData = [
            'amount' => $data['amountToPay'],
            'currency' => 'TRY',
            'itemName' => $subscription->plan->name,
            'conversationId' => (string) $subscription->id,
            'callbackUrl' => route('iyzico.subscription.success'),
        ];

        try {
            $checkoutUrl = $this->iyzicoPayment($paymentData);

            return $this->sendResponse([
                'subscription_id' => $subscription->id,
                'payment_type' => 'iyzico',
                'checkout_url' => $checkoutUrl,
                'amount' => $data['amountToPay'],
                'currency' => 'TRY',
            ], 'Iyzico checkout session created successfully.');

        } catch (\Exception $e) {
            Log::error('Iyzico Subscription OnBoard Error: ' . $e->getMessage());
            return $this->sendError('Unable to create Iyzico checkout.', 500);
        }
    }


    public function iyzicoPaymentSuccess(Request $request): JsonResponse
    {
        $user = Auth::user();

        if ($user->email === 'admin@vcard.com') {
            return $this->sendError(
                'Seems, you are not allowed to access this record.',
                403
            );
        }

        $token = $request->input('token');

        if (empty($token)) {
            return $this->sendError('Token is required.', 400);
        }

        $request->validate([
            'subscription_id' => 'required|integer|exists:subscriptions,id',
        ]);

        try {
            $this->initializeOptions();

            $retrieveRequest = new \Iyzipay\Request\RetrieveCheckoutFormRequest();
            $retrieveRequest->setLocale(\Iyzipay\Model\Locale::EN);
            $retrieveRequest->setConversationId($request->subscription_id);
            $retrieveRequest->setToken($token);

            $checkoutForm = \Iyzipay\Model\CheckoutForm::retrieve(
                $retrieveRequest,
                $this->options
            );

            if (
                $checkoutForm->getStatus() !== 'success' ||
                $checkoutForm->getPaymentStatus() !== 'SUCCESS'
            ) {
                return $this->sendError(
                    $checkoutForm->getErrorMessage() ?? 'Payment verification failed.',
                    400
                );
            }

            $subscriptionID = $checkoutForm->getConversationId();
            $amountToPay = $checkoutForm->getPaidPrice();

            $subscription = Subscription::findOrFail($subscriptionID);

            DB::beginTransaction();

            $subscription->update([
                'payment_type' => Subscription::IYZICO,
                'status' => Subscription::ACTIVE,
            ]);

            Subscription::whereTenantId(getLogInTenantId())
                ->where('id', '!=', $subscriptionID)
                ->where('status', '!=', Subscription::REJECT)
                ->update([
                    'status' => Subscription::INACTIVE,
                ]);

            $transaction = Transaction::create([
                'tenant_id'     => $subscription->tenant_id,
                'transaction_id'=> $checkoutForm->getPaymentId(),
                'type'          => Subscription::IYZICO,
                'amount'        => $amountToPay,
                'status'        => Subscription::ACTIVE,
                'meta'          => json_encode($checkoutForm->getRawResult()),
            ]);

            $subscription->update([
                'transaction_id' => $transaction->id,
                'payment_type'   => Subscription::IYZICO,
            ]);

            $affiliateAmount = getSuperAdminSettingValue('affiliation_amount');
            $affiliateAmountType = getSuperAdminSettingValue('affiliation_amount_type');

            if ($affiliateAmountType == 1) {
                AffiliateUser::whereUserId(getLogInUserId())
                    ->where('amount', 0)
                    ->withoutGlobalScopes()
                    ->update([
                        'amount' => $affiliateAmount,
                        'is_verified' => 1
                    ]);
            } elseif ($affiliateAmountType == 2) {
                $amount = $amountToPay * $affiliateAmount / 100;
                AffiliateUser::whereUserId(getLogInUserId())
                    ->where('amount', 0)
                    ->withoutGlobalScopes()
                    ->update([
                        'amount' => $amount,
                        'is_verified' => 1
                    ]);
            }

            $planName = $subscription->plan->name;

            $emailData = [
                'subscriptionID' => $subscriptionID,
                'amountToPay'    => $amountToPay,
                'planName'       => $planName,
                'first_name'     => $user->first_name,
                'last_name'      => $user->last_name,
            ];

            manageVcards();
            Mail::to($user->email)->send(
                new SubscriptionPaymentSuccessMail($emailData)
            );

            DB::commit();

            return $this->sendResponse([
                'subscription_id' => $subscriptionID,
                'transaction_id'  => $transaction->id,
                'plan_name'       => $planName,
                'amount'          => $amountToPay,
                'status'          => 'active',
            ], 'Payment completed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Iyzico Payment Error: ' . $e->getMessage());

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
            'currency' => 'required|string',
        ]);

        if (strtoupper($request->currency) !== 'TRY') {
            return $this->sendError('Iyzico supports only TRY currency.');
        }

        $nfcOrder = NfcOrders::with('nfcCard')->findOrFail($request->order_id);

        // Initialize Iyzico options
        $this->setUserId($nfcOrder->user_id);

        // Amount calculation
        $baseAmount = $nfcOrder->nfcCard->price * $nfcOrder->quantity;
        $taxValue = getSuperAdminSettingValue('nfc_tax_value') ?? 0;
        $taxAmount = ($taxValue > 0) ? ($baseAmount * $taxValue / 100) : 0;
        $totalAmount = $baseAmount + $taxAmount;

        try {
            $paymentData = [
                'amount' => $totalAmount,
                'currency' => strtoupper($request->currency),
                'itemName' => $nfcOrder->nfcCard->name,
                'conversationId' => $nfcOrder->id,
                'callbackUrl' => route('iyzico.nfc.card.success'),
            ];

            $paymentUrl = $this->iyzicoPayment($paymentData);

            if (!filter_var($paymentUrl, FILTER_VALIDATE_URL)) {
                return $this->sendError($paymentUrl);
            }

            parse_str(parse_url($paymentUrl, PHP_URL_QUERY), $query);

            $token = $query['token'] ?? null;

            if (!$token) {
                return $this->sendError('Iyzico token missing for NFC order ID: ' . $nfcOrder->id);
            }

            return $this->sendResponse([
                'checkout_url' => $paymentUrl,
                'token' => $token,
                'order_id' => $nfcOrder->id,
                'amount' => round($totalAmount,2),
                'currency' => strtoupper($request->currency),
            ], 'Iyzico NFC checkout created successfully.');

        } catch (\Exception $e) {
            Log::error('Iyzico NFC onboard error: ' . $e->getMessage());
            return $this->sendError('Unable to create Iyzico payment.', 500);
        }
    }

    public function nfcPurchaseSuccess(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'order_id' => 'required|integer|exists:nfc_orders,id',
        ]);

        try {
            $this->initializeOptions();

            $retrieveRequest = new \Iyzipay\Request\RetrieveCheckoutFormRequest();
            $retrieveRequest->setLocale(\Iyzipay\Model\Locale::EN);
            $retrieveRequest->setConversationId($request->order_id);
            $retrieveRequest->setToken($request->token);

            $checkoutForm = \Iyzipay\Model\CheckoutForm::retrieve(
                $retrieveRequest,
                $this->options
            );

            if ($checkoutForm->getStatus() !== 'success' ||
                $checkoutForm->getPaymentStatus() !== 'SUCCESS') {

                return $this->sendError(
                    $checkoutForm->getErrorMessage() ?? 'Payment failed',
                    400
                );
            }

            $orderId = $checkoutForm->getConversationId();
            if (empty($orderId)) {
                return $this->sendError('Invalid order ID.', 400);
            }
            $amount  = $checkoutForm->getPaidPrice();

            $nfcOrder = NfcOrders::findOrFail($orderId);

            // Prevent duplicate processing
            if (NfcOrderTransaction::where('transaction_id', $checkoutForm->getPaymentId())->exists()) {
                return $this->sendError('Payment already processed.', 409);
            }

            // Tax
            $taxValue = getSuperAdminSettingValue('nfc_tax_value') ?? 0;
            $tax = $taxValue > 0 ? $taxValue : null;

            DB::beginTransaction();

            $transaction = NfcOrderTransaction::create([
                'nfc_order_id' => $orderId,
                'type' => NfcOrders::IYZICO,
                'transaction_id' => $checkoutForm->getPaymentId(),
                'amount' => $amount,
                'user_id' => $nfcOrder->user_id,
                'status' => NfcOrders::SUCCESS,
                'tax' => $tax,
                'meta' => json_encode($checkoutForm),
            ]);

            $nfcOrder->update([
                'status' => NfcOrders::SUCCESS,
                'payment_type' => NfcOrders::IYZICO,
            ]);

            // Admin mail
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
            Log::error('Iyzico NFC payment success error: ' . $e->getMessage());
            return $this->sendError('Payment processing failed.', 500);
        }
    }

    public function iyzicoPayment(array $data)
    {
        try {
            $user = getLogInUser();
            $userAddress = $user?->address;

            $request = new \Iyzipay\Request\CreateCheckoutFormInitializeRequest();
            $request->setLocale(\Iyzipay\Model\Locale::EN);
            $request->setConversationId($data['conversationId']);
            $request->setPrice($data['amount']);
            $request->setPaidPrice($data['amount']);
            $request->setCurrency(strtoupper($data['currency']));
            $request->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);
            $callbackUrl = filter_var($data['callbackUrl'], FILTER_VALIDATE_URL)
                ? $data['callbackUrl']
                : route($data['callbackUrl']);
            $request->setCallbackUrl($callbackUrl);

            $buyer = new \Iyzipay\Model\Buyer();
            $buyer->setId((string) ($user?->id ?? '0'));
            $buyer->setName($user?->first_name ?? 'N/A');
            $buyer->setSurname($user?->last_name ?? 'N/A');
            $buyer->setGsmNumber($user?->phone ?? 'N/A');
            $buyer->setEmail($user?->email ?? 'dbHb0@example.com');
            $buyer->setIdentityNumber($userAddress?->identification_number ?? '123456');
            $buyer->setRegistrationAddress($userAddress?->address ?? 'N/A');
            $buyer->setIp(request()->ip());
            $buyer->setCity($userAddress?->city ?? 'N/A');
            $buyer->setCountry($userAddress?->country ?? 'N/A');
            $buyer->setZipCode($userAddress?->postal_code ?? '00000');
            $request->setBuyer($buyer);

            $request->setBuyer($buyer);

            $address = new \Iyzipay\Model\Address();
            $address->setContactName($user?->full_name ?? 'N/A');
            $address->setCity($userAddress?->city ?? 'N/A');
            $address->setCountry($userAddress?->country ?? 'N/A');
            $address->setAddress($userAddress?->address ?? 'N/A');
            $address->setZipCode($userAddress?->postal_code ?? 'N/A');
            $request->setShippingAddress($address);
            $request->setBillingAddress($address);

            $request->setBillingAddress($address);
            $request->setShippingAddress($address);

            $item = new \Iyzipay\Model\BasketItem();
            $item->setId((string) $data['conversationId']);
            $item->setName($data['itemName']);
            $item->setCategory1('NFC Card');
            $item->setItemType(\Iyzipay\Model\BasketItemType::VIRTUAL);
            $item->setPrice($data['amount']);

            $request->setBasketItems([$item]);

            $checkoutForm = \Iyzipay\Model\CheckoutFormInitialize::create($request, $this->options);

            if ($checkoutForm->getStatus() !== 'success') {
                throw new \Exception($checkoutForm->getErrorMessage());
            }

            return $checkoutForm->getPaymentPageUrl();

        } catch (\Exception $e) {
            Log::error('Iyzico Payment Init Error: ' . $e->getMessage());
            throw $e;
        }
    }


}
