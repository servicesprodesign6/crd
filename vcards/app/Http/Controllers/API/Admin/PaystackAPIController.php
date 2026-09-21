<?php

namespace App\Http\Controllers\API\Admin;

use App\Models\Plan;
use App\Models\NfcOrders;
use App\Models\CouponCode;
use App\Models\Transaction;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Mail\AdminNfcOrderMail;
use Illuminate\Http\JsonResponse;
use App\Models\NfcOrderTransaction;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\AppBaseController;
use App\Mail\SubscriptionPaymentSuccessMail;
use App\Repositories\SubscriptionRepository;

class PaystackAPIController extends AppBaseController
{
    private SubscriptionRepository $subscriptionRepository;

    public function __construct(SubscriptionRepository $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }
    public function redirectToGateway(Request $request): JsonResponse
    {

        $planId = $request->input('planId');
        $customFieldId = $request->input('customFieldId');
        $couponCodeId = $request->input('couponCodeId');
        $couponCode = $request->input('couponCode');

        //Fetch selected plan
        $plan = Plan::with('currency')->findOrFail($planId);

        //Custom Field Validation
        if ($plan->custom_select == 1) {
            if (empty($customFieldId)) {
                return $this->sendError('Please select a custom option for this plan.');
            }

            $validCustomField = $plan->planCustomFields()->where('id', $customFieldId)->exists();
            if (!$validCustomField) {
                return $this->sendError('Invalid custom field selection for this plan.');
            }
        } elseif (!empty($customFieldId)) {
            return $this->sendError('This plan does not have custom options.');
        }

        //Coupon Validation
        if (!empty($couponCodeId) && !empty($couponCode)) {
            $couponCodeRecord = CouponCode::where('id', $couponCodeId)
                ->where('coupon_name', $couponCode)
                ->where('status', 1)
                ->first();

            if (!$couponCodeRecord) {
                return $this->sendError('Invalid or inactive coupon code.');
            }

            $currentDate = Carbon::now();
            if ($currentDate->gt(Carbon::parse($couponCodeRecord->expire_at))) {
                return $this->sendError('This coupon code has expired.');
            }

            if ($couponCodeRecord->coupon_limit !== null && $couponCodeRecord->coupon_limit_left <= 0) {
                return $this->sendError('This coupon code has reached its usage limit.');
            }
        }

        //Active subscription check
        $currentSubscription = getCurrentSubscription();
        if ($currentSubscription && $currentSubscription->plan_id == $planId) {
            if (Carbon::parse($currentSubscription->ends_at)->gt(Carbon::now())) {
                return $this->sendError('You already have this active subscription plan.');
            }
        }

        //Create subscription
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

        //Handle Free Plan
        if (empty($data['amountToPay']) || $data['amountToPay'] == 0) {
            return $this->sendResponse([
                'plan' => $plan,
            ], 'Plan activated successfully (Free Plan).');
        }

        //Paystack Keys
        $key = getSelectedPaymentGateway('paystack_key');
        $secret = getSelectedPaymentGateway('paystack_secret');

        if (empty($key) || empty($secret)) {
            return $this->sendError('Paystack credentials are missing.');
        }

        //Supported Currency Check
        $supportedCurrency = ['NGN', 'USD', 'GHS', 'ZAR', 'KES'];
        if (!in_array(strtoupper($plan->currency->currency_code), $supportedCurrency)) {
            return $this->sendError(__('messages.placeholder.this_currency_is_not_supported_paystack'));
        }

        //Generate Reference
        $reference = 'psk_' . uniqid();

        //Callback URL
        $callbackUrl = route('paystack.subscription.success');

        //Prepare Paystack Payload
        $payload = [
            'email' => getLogInUser()->email,
            'amount' => $data['amountToPay'] * 100,
            'currency' => $plan->currency->currency_code,
            'reference' => $reference,
            'callback_url' => $callbackUrl,
            'metadata' => [
                'subscription_id' => $subscription->id,
                'amount' => $data['amountToPay'],
                'payment_mode' => Subscription::PAYSTACK,
            ],
        ];

        //Initialize Payment
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $secret,
            'Content-Type' => 'application/json',
        ])->post('https://api.paystack.co/transaction/initialize', $payload);

        $res = $response->json();

        if (!isset($res['status']) || $res['status'] !== true) {
            Log::error('Paystack Error:', $res);
            return $this->sendError($res['message'] ?? 'Unable to create Paystack payment session.');
        }

        $checkoutUrl = $res['data']['authorization_url'];

        //Return API Response
        return $this->sendResponse([
            'plan_id' => $plan->id,
            'subscription_id' => $subscription->id,
            'payment_type' => 'paystack',
            'checkout_url' => $checkoutUrl,
            'currency' => $plan->currency->currency_code,
            'amount' => $data['amountToPay'],
            'email' => getLogInUser()->email,
            'reference' => $reference,
        ], 'Paystack checkout session created successfully.');
    }

    public function paymentSuccess(Request $request): JsonResponse
    {
        $user = Auth::user();

        if ($user->email == 'admin@vcard.com') {
            return $this->sendError('You are not allowed to access this record.', 403);
        }

        try {
            $paystackSecret = getSelectedPaymentGateway('paystack_secret');

            if (empty($paystackSecret)) {
                return $this->sendError('Paystack credentials missing.');
            }

            $reference = $request->input('reference');   //Paystack returns this always

            if (!$reference) {
                return $this->sendError('Reference missing.', 400);
            }

            //Verify from Paystack
            $verifyUrl = "https://api.paystack.co/transaction/verify/" . $reference;

            $response = Http::withToken($paystackSecret)->get($verifyUrl);
            $verifyData = $response->json();

            if (!isset($verifyData['status']) || $verifyData['status'] !== true) {
                return $this->sendError('Unable to verify Paystack transaction.');
            }

            $data = $verifyData['data'];

            if ($data['status'] !== 'success') {
                return $this->sendError('Payment was not successful.');
            }

            //Extract metadata
            $subscriptionId = $data['metadata']['subscription_id'] ?? null;
            $amountToPay = $data['amount'] / 100;
            $planName = $data['plan'] ?? "";

            if (!$subscriptionId) {
                return $this->sendError('Subscription reference missing.');
            }

            $subscription = Subscription::findOrFail($subscriptionId);

            //Activate subscription
            $subscription->update([
                'payment_type' => Subscription::PAYSTACK,
                'status' => Subscription::ACTIVE,
            ]);

            //Deactivate old subscriptions
            Subscription::whereTenantId(getLogInTenantId())
                ->where('id', '!=', $subscriptionId)
                ->where('status', '!=', Subscription::REJECT)
                ->update(['status' => Subscription::INACTIVE]);

            //Create transaction record
            $transaction = Transaction::create([
                'tenant_id' => $subscription->tenant_id,
                'transaction_id' => $reference,
                'type'       => Subscription::PAYSTACK,
                'amount'     => $amountToPay,
                'status'     => Subscription::ACTIVE,
                'meta'       => json_encode($verifyData),
            ]);

            //Save transaction id
            $subscription->update([
                'transaction_id' => $transaction->id
            ]);

            //Send success email
            $emailData = [
                'subscriptionID' => $subscriptionId,
                'amountToPay'    => $amountToPay,
                'planName'       => $subscription->plan->name,
                'first_name'     => $user->first_name,
                'last_name'      => $user->last_name,
            ];

            manageVcards();
            Mail::to($user->email)->send(new SubscriptionPaymentSuccessMail($emailData));

            //FINAL API RESPONSE
            return $this->sendResponse([
                'subscription_id' => $subscriptionId,
                'transaction_id'  => $transaction->id,
                'plan_name'       => $subscription->plan->name,
                'amount'          => $amountToPay,
                'status'          => 'active'
            ], 'Payment completed successfully.');

        } catch (\Exception $e) {
            Log::error('Paystack Payment Error: ' . $e->getMessage());
            return $this->sendError($e->getMessage(), 500);
        }
    }

    public function redirectToGatewayNfcCard(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|integer|exists:nfc_orders,id',
            'email' => 'required|email',
            'currency' => 'required|string',
        ]);

        try {
            $paystackSecret = getSelectedPaymentGateway('paystack_secret');

            if (empty($paystackSecret)) {
                return $this->sendError('Paystack credentials missing.');
            }

            // Fetch NFC Order
            $nfcOrder = NfcOrders::with('nfcCard')->find($request->order_id);
            if (!$nfcOrder || !$nfcOrder->nfcCard) {
                return $this->sendError('Invalid NFC order.');
            }

            // Supported currency check (same as subscription)
            $supportedCurrency = ['NGN', 'USD', 'GHS', 'ZAR', 'KES'];
            if (!in_array(strtoupper($request->currency), $supportedCurrency)) {
                return $this->sendError(__('messages.placeholder.this_currency_is_not_supported_paystack'));
            }

            // 🔹 Amount calculation
            $baseAmount = $nfcOrder->nfcCard->price * $nfcOrder->quantity;

            $isTaxEnabled = getSuperAdminSettingValue('nfc_tax_enabled') ?? false;
            $taxValue = getSuperAdminSettingValue('nfc_tax_value') ?? 0;
            $taxAmount = 0;

            if ($isTaxEnabled && $taxValue > 0) {
                $taxAmount = ($baseAmount * $taxValue) / 100;
            }

            $totalAmount = $baseAmount + $taxAmount;

            // Generate reference (IMPORTANT)
            $reference = 'nfc_psk_' . uniqid();

            // Prepare Paystack payload
            $payload = [
                'email' => $request->email,
                'amount' => (int) ($totalAmount * 100),
                'currency' => strtoupper($request->currency),
                'reference' => $reference,
                'callback_url' => route('paystack.nfc.card.success'),
                'metadata' => [
                    'order_type' => 'nfc',
                    'nfc_order_id' => $nfcOrder->id,
                    'amount' => $totalAmount,
                    'payment_mode' => NfcOrders::PAYSTACK,
                ],
            ];

            // Initialize Paystack payment
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $paystackSecret,
                'Content-Type' => 'application/json',
            ])->post('https://api.paystack.co/transaction/initialize', $payload);

            $res = $response->json();

            if (!isset($res['status']) || $res['status'] !== true) {
                Log::error('Paystack NFC Error:', $res);
                return $this->sendError($res['message'] ?? 'Unable to create Paystack payment session.');
            }

            return $this->sendResponse([
                'checkout_url' => $res['data']['authorization_url'],
                'reference' => $reference,
                'amount' => round($totalAmount, 2),
                'currency' => strtoupper($request->currency),
                'order_id' => $nfcOrder->id,
            ], 'Paystack checkout session for NFC Card created successfully.');

        } catch (\Exception $e) {
            Log::error('Paystack NFC API Error: ' . $e->getMessage());
            return $this->sendError($e->getMessage(), 500);
        }
    }

    public function nfcPaystackPaymentSuccess(Request $request): JsonResponse
    {
        $request->validate([
            'reference' => 'required|string',
            'nfc_order_id' => 'required|integer|exists:nfc_orders,id',
        ]);

        try {
            $paystackSecret = getSelectedPaymentGateway('paystack_secret');

            if (empty($paystackSecret)) {
                return $this->sendError('Paystack credentials missing.', 500);
            }

            // Verify transaction from Paystack
            $verifyUrl = 'https://api.paystack.co/transaction/verify/' . $request->reference;

            $response = Http::withToken($paystackSecret)->get($verifyUrl);
            $verifyData = $response->json();

            if (!isset($verifyData['status']) || $verifyData['status'] !== true) {
                return $this->sendError('Unable to verify Paystack transaction.');
            }

            $data = $verifyData['data'];

            if ($data['status'] !== 'success') {
                return $this->sendError('Payment was not successful.');
            }

            // Fetch NFC Order
            $nfcOrder = NfcOrders::with('nfcCard')->findOrFail($request->nfc_order_id);

            // Prevent duplicate transactions
            if (NfcOrderTransaction::where('transaction_id', $data['id'])->exists()) {
                return $this->sendError('Payment already processed.', 409);
            }

            // Amount (Paystack returns kobo)
            $amount = $data['amount'] / 100;

            // Tax handling
            $isTaxEnabled = getSuperAdminSettingValue('nfc_tax_enabled') ?? false;
            $taxValue = getSuperAdminSettingValue('nfc_tax_value') ?? 0;
            $tax = null;

            if ($isTaxEnabled && $taxValue > 0) {
                $tax = $taxValue;
            }

            // Create NFC transaction
            $transaction = NfcOrderTransaction::create([
                'nfc_order_id' => $nfcOrder->id,
                'type' => NfcOrders::PAYSTACK,
                'transaction_id' => $data['id'],
                'amount' => $amount,
                'user_id' => $nfcOrder->user_id,
                'status' => NfcOrders::SUCCESS,
                'tax' => $tax,
                'meta' => json_encode($verifyData),
            ]);

            // Update NFC order status
            $nfcOrder->update([
                'payment_type' => NfcOrders::PAYSTACK,
                'status' => NfcOrders::SUCCESS,
            ]);

            // Send admin email
            $vcardName = optional($nfcOrder->vcard)->name;
            $cardType  = optional($nfcOrder->nfcCard)->name;

            Mail::to(getSuperAdminSettingValue('email'))
                ->send(new AdminNfcOrderMail($nfcOrder, $vcardName, $cardType));

            // FINAL API RESPONSE
            return $this->sendResponse([
                'order_id' => $nfcOrder->id,
                'transaction_id' => $transaction->id,
                'paystack_reference' => $request->reference,
                'amount' => $amount,
                'status' => 'success',
            ], 'NFC order payment completed successfully.');

        } catch (\Exception $e) {
            Log::error('Paystack NFC Payment Error: ' . $e->getMessage());
            return $this->sendError('Payment verification failed.', 500);
        }
    }

}
