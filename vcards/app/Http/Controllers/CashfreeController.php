<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Nfc;
use App\Models\Plan;
use App\Models\Vcard;
use App\Models\Product;
use App\Models\NfcOrders;
use Laracasts\Flash\Flash;
use App\Models\Appointment;
use App\Models\Transaction;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Models\AffiliateUser;
use App\Mail\AdminNfcOrderMail;
use App\Mail\ProductOrderSendUser;
use App\Models\ProductTransaction;
use Illuminate\Support\Facades\DB;
use App\Models\NfcOrderTransaction;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\ProductOrderSendCustomer;
use App\Models\AppointmentTransaction;
use App\Repositories\AppointmentRepository;
use App\Mail\SubscriptionPaymentSuccessMail;
use App\Models\User;
use App\Repositories\SubscriptionRepository;

class CashfreeController extends AppBaseController
{
    protected $subscriptionRepository;

    public function __construct(SubscriptionRepository $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }

    private function getBaseUrl($mode)
    {
        return ($mode === 'live')
            ? 'https://api.cashfree.com/pg'
            : 'https://sandbox.cashfree.com/pg';
    }

public function cashfreeSubscription(Request $request)
{
    try {
        $clientId = getSelectedPaymentGateway('cashfree_key');
        $clientSecret = getSelectedPaymentGateway('cashfree_secret');
        $mode = getSelectedPaymentGateway('cashfree_mode');

        if (empty($clientId) || empty($clientSecret)) {
            Flash::error(__('messages.placeholder.please_add_payment_credentials'));
            return redirect()->back();
        }

        $baseUrl = $this->getBaseUrl($mode);
        $plan = Plan::with('currency')->findOrFail($request->planId);
        $currency = $plan->currency->currency_code;

        if ($currency != "INR") {
            Flash::error(__('messages.placeholder.currency_supported_cashfree'));
            return redirect()->back();
        }

        $data = $this->subscriptionRepository->manageSubscription($request->all());

        if (!isset($data['plan'])) {
            if (isset($data['status']) && $data['status'] == true) {
                Flash::error(__('messages.subscription_pricing_plans.has_been_subscribed'));
                return redirect()->back();
            } else {
                if (isset($data['status']) && $data['status'] == false) {
                    Flash::error(__('messages.placeholder.cannot_switch_to_zero'));
                    return redirect()->back();
                }
            }
        }

        $subscriptionsPricingPlan = $data['plan'];
        $subscription = $data['subscription'];
        $amount = number_format($data['amountToPay'], 2, '.', '');
        $orderId = 'sub_' . $subscription->id . '_' . time();

        $returnData = [
            'subscription' => $subscription->id,
            'amountToPay' => $amount,
            'm_payment_id' => $orderId,
        ];

        $customerId = 'customer_' . getLogInUser()->id;

        // Create order with Cashfree API
        $orderData = [
            'order_id' => $orderId,
            'order_amount' => (float)$amount,
            'order_currency' => 'INR',
            'customer_details' => [
                'customer_id' => $customerId,
                'customer_name' => $request->user()->full_name,
                'customer_email' => $request->user()->email,
                'customer_phone' => $request->user()->contact,
            ],
            'order_meta' => [
                'return_url' => route('cashfree.subscription.success') . '?' . http_build_query($returnData),
                'cancel_url' => route('cashfree.subscription.failed', $returnData),
            ],
            'order_note' => 'Subscription: ' . $subscriptionsPricingPlan->name,
        ];

        $response = Http::withHeaders([
            'x-client-id' => $clientId,
            'x-client-secret' => $clientSecret,
            'x-api-version' => '2023-08-01',
            'Content-Type' => 'application/json',
        ])->post($baseUrl . '/orders', $orderData);

        if (!$response->successful()) {
            Log::error('Cashfree API Error: ' . $response->body());
            throw new Exception('Cashfree API Error: ' . ($response->json()['message']));
        }

        $responseData = $response->json();

        if (empty($responseData['payment_session_id'])) {
            throw new Exception(__('messages.placeholder.payment_session_id_not_found'));
        }

        // Determine mode for SDK
        $jsMode = ($mode === 'live' || $mode === 'production') ? 'production' : 'sandbox';

        // Return checkout page
        return view('payments.cashfree-checkout', [
            'paymentSessionId' => $responseData['payment_session_id'],
            'orderId' => $orderId,
            'mode' => $jsMode,
            'returnUrl' => route('cashfree.subscription.success') . '?' . http_build_query($returnData),
        ]);

    } catch (Exception $e) {
        Log::error('Cashfree Error: ' . $e->getMessage());
        Flash::error($e->getMessage());
        return redirect()->back();
    }
}

    public function cashfreeSubscriptionSuccess(Request $request)
    {
        $input = $request->all();
        $subscriptionID = $input['subscription'] ?? null;

        if (!$subscriptionID) {
            Flash::error(__('messages.placeholder.invalid_subscription_reference'));
            return redirect()->route('subscription.index');
        }

        try {
            $clientId = getSelectedPaymentGateway('cashfree_key');
            $clientSecret = getSelectedPaymentGateway('cashfree_secret');
            $mode = getSelectedPaymentGateway('cashfree_mode');
            $baseUrl = $this->getBaseUrl($mode);

            $orderId = $input['m_payment_id'];

            if (!$orderId) {
                Flash::error(__('messages.placeholder.order_id_missing'));
                return redirect()->route('subscription.index');
            }

            // Verify payment with Cashfree
            $response = Http::withHeaders([
                'x-client-id' => $clientId,
                'x-client-secret' => $clientSecret,
                'x-api-version' => '2025-01-01',
            ])->get($baseUrl . '/orders/' . $orderId);

            if (!$response->successful()) {
                Log::error('Verification failed: ' . $response->body());
                Subscription::findOrFail($subscriptionID)->delete();
                Flash::error(__('messages.placeholder.payment_verification_failed'));
                return redirect()->route('subscription.index');
            }

            $orderData = $response->json();

            // Check if payment is successful
            if ($orderData['order_status'] !== 'PAID') {
                Subscription::findOrFail($subscriptionID)->delete();
                Flash::error(__('messages.placeholder.payment_not_complete'));
                return redirect()->route('subscription.index');
            }

            // Activate subscription
            Subscription::findOrFail($subscriptionID)->update(['status' => Subscription::ACTIVE]);

            // Deactivate other subscriptions
            Subscription::whereTenantId(getLogInTenantId())
                ->where('id', '!=', $subscriptionID)
                ->where('status', '!=', Subscription::REJECT)
                ->update(['status' => Subscription::INACTIVE]);

            // Create transaction record
            $transaction = Transaction::create([
                'tenant_id' => getLogInTenantId(),
                'transaction_id' => $orderId,
                'type' => Subscription::CASHFREE,
                'amount' => $input['amountToPay'],
                'status' => Subscription::ACTIVE,
                'meta' => $orderData,
            ]);

            Subscription::findOrFail($subscriptionID)->update(['transaction_id' => $transaction->id, 'payment_type' => Subscription::CASHFREE]);

            // Handle affiliate commissions
            $affiliateAmount = getSuperAdminSettingValue('affiliation_amount');
            $affiliateAmountType = getSuperAdminSettingValue('affiliation_amount_type');

            if ($affiliateAmountType == 1) {
                AffiliateUser::whereUserId(getLogInUserId())->where('amount', 0)->withoutGlobalScopes()
                    ->update(['amount' => $affiliateAmount, 'is_verified' => 1]);
            } else if ($affiliateAmountType == 2) {
                $commission = $input['amountToPay'] * $affiliateAmount / 100;
                AffiliateUser::whereUserId(getLogInUserId())->where('amount', 0)->withoutGlobalScopes()
                    ->update(['amount' => $commission, 'is_verified' => 1]);
            }

            // Send email
            $user = getLogInUser();
            Mail::to($user->email)->send(new SubscriptionPaymentSuccessMail([
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'planName' => Subscription::findOrFail($subscriptionID)->plan->name,
            ]));

            // Slack notification
            if (moduleExists('SlackIntegration')) {
                $slackIntegration = SlackIntegration::first();
                if ($slackIntegration && $slackIntegration->user_plan_purchase_notification == 1 && !empty($slackIntegration->webhook_url)) {
                    $message = "🔔 New Plan Purchased !!!\nPlan {$user->plan->name} Purchased by {$user->first_name} {$user->last_name} Successfully.";
                    $slackIntegration->notify(new SlackNotification($message));
                }
            }

            return view('sadmin.plans.payment.paymentSuccess');

        } catch (Exception $e) {
            Log::error('Payment Success Handler Error: ' . $e->getMessage());
            Flash::error('An error occurred: ' . $e->getMessage());
            return redirect()->route('subscription.index');
        }
    }

    public function cashfreeSubscriptionCancel(Request $request)
    {
        $input = $request->all();
        $subscriptionID = $input['subscription'] ?? null;

        if ($subscriptionID) {
            $subscription = Subscription::find($subscriptionID);
            if ($subscription) {
                $subscription->delete();
            }
        }

        return view('sadmin.plans.payment.paymentcancel');
    }

    public function nfcOrder($input,$orderId, $email, $nfc)
    {
        try {
            $clientId = getSelectedPaymentGateway('cashfree_key');
            $clientSecret = getSelectedPaymentGateway('cashfree_secret');
            $mode = getSelectedPaymentGateway('cashfree_mode');

            $baseUrl = $this->getBaseUrl($mode);

            $baseAmount = $nfc->nfcCard->price * $nfc->quantity;
            $isTaxEnabled = getSuperAdminSettingValue('nfc_tax_enabled') ?? false;
            $taxValue = getSuperAdminSettingValue('nfc_tax_value') ?? 0;
            $taxAmount = 0;

            if ($isTaxEnabled && $taxValue > 0) {
                $taxAmount = ($baseAmount * $taxValue) / 100;
            }

            $amount = $baseAmount + $taxAmount;

            $orderRef = 'nfc_' . uniqid();

            $data = [
                'order_id' => $orderId,
                'nfc' => $nfc,
                'amountToPay' => $amount,
                'm_payment_id' => $orderRef,
            ];

            $customerId = 'customer_' . getLogInUser()->id;
            $phoneNumber = $input['phone'];

            $orderData = [
                'order_id' => $orderRef,
                'order_amount' => (float)$amount,
                'order_currency' => 'INR',
                'customer_details' => [
                    'customer_id' => $customerId,
                    'customer_name' => getLogInUser()->full_name,
                    'customer_email' => $email,
                    'customer_phone' => $phoneNumber,
                ],
                'order_note' => 'NFC Order: ' . $nfc->nfcCard->name,
                'order_meta' => [
                    'return_url' => route('nfc.cashfree.success') . '?' . http_build_query($data),
                    'cancel_url' => route('nfc.cashfree.failed', $data),
                ],
            ];

            $response = Http::withHeaders([
                'x-client-id' => $clientId,
                'x-client-secret' => $clientSecret,
                'x-api-version' => '2023-08-01',
                'Content-Type' => 'application/json',
            ])->post($baseUrl . '/orders', $orderData);

            if (!$response->successful()) {
                Log::error('Cashfree NFC Order Error: ' . $response->body());
                Flash::error($response->json()['message']);
                return [
                    'success' => false,
                    'message' => $response->json()['message'],
                ];
            }

            $responseData = $response->json();

            if (empty($responseData['payment_session_id'])) {
                throw new Exception(__('messages.placeholder.payment_session_id_not_found'));
            }
            $jsMode = ($mode === 'live' || $mode === 'production') ? 'production' : 'sandbox';
            $paymentUrl = "https://{$jsMode}.cashfree.com/pg/orders/{$responseData['payment_session_id']}";

            return [
                'success' => true,
                'payment_url' => $paymentUrl,
                'payment_session_id' => $responseData['payment_session_id'],
                'order_id' => $orderRef,
                'return_url' => route('nfc.cashfree.success') . '?' . http_build_query($data),
                'mode' => $jsMode,
            ];

        } catch (Exception $e) {
            Log::error('Cashfree NFC Order Error: ' . $e->getMessage());
            Flash::error($e->getMessage());
            return redirect()->back();
        }
    }

    public function nfcPurchaseSuccess(Request $request)
    {
        $input = $request->all();

        $nfcOrder = NfcOrders::find($input['order_id']);

        $isTaxEnabled = getSuperAdminSettingValue('nfc_tax_enabled') ?? false;
        $taxValue = getSuperAdminSettingValue('nfc_tax_value') ?? 0;
        $tax = null;

        if ($isTaxEnabled && $taxValue > 0) {
            $tax = $taxValue;
        }

        try {
            $clientId = getSelectedPaymentGateway('cashfree_key');
            $clientSecret = getSelectedPaymentGateway('cashfree_secret');
            $mode = getSelectedPaymentGateway('cashfree_mode');
            $baseUrl = $this->getBaseUrl($mode);

            $orderId = $input['m_payment_id'];

            if (!$orderId) {
                Flash::error(__('messages.placeholder.order_id_missing'));
                return redirect(route('user.orders'));
            }

            $response = Http::withHeaders([
                'x-client-id' => $clientId,
                'x-client-secret' => $clientSecret,
                'x-api-version' => '2023-08-01',
            ])->get($baseUrl . '/orders/' . $orderId);

            if (!$response->successful()) {
                Log::error('Cashfree NFC Verification failed: ' . $response->body());
                NfcOrderTransaction::create([
                    'nfc_order_id' => $input['order_id'],
                    'type' => NfcOrders::CASHFREE,
                    'transaction_id' => $input['m_payment_id'],
                    'amount' => $input['amountToPay'],
                    'user_id' => getLogInUser()->id,
                    'status' => NfcOrders::FAIL,
                    'tax' => $tax,
                ]);
                Flash::error(__('messages.placeholder.payment_verification_failed'));
                return redirect(route('user.orders'));
            }

            $orderData = $response->json();

            if ($orderData['order_status'] !== 'PAID') {
                NfcOrderTransaction::create([
                    'nfc_order_id' => $input['order_id'],
                    'type' => NfcOrders::CASHFREE,
                    'transaction_id' => $input['m_payment_id'],
                    'amount' => $input['amountToPay'],
                    'user_id' => getLogInUser()->id,
                    'status' => NfcOrders::FAIL,
                    'tax' => $tax,
                ]);
                Flash::error(__('messages.placeholder.payment_not_complete'));
                return redirect(route('user.orders'));
            }

            NfcOrderTransaction::create([
                'nfc_order_id' => $input['order_id'],
                'type' => NfcOrders::CASHFREE,
                'transaction_id' => $input['m_payment_id'],
                'amount' => $input['amountToPay'],
                'user_id' => getLogInUser()->id,
                'status' => NfcOrders::SUCCESS,
                'tax' => $tax,
            ]);

            $vcardName = Vcard::find($nfcOrder['vcard_id'])->name;
            $cardType = Nfc::find($nfcOrder['card_type'])->name;

            App::setLocale(getLogInUser()->language);

            Mail::to(getSuperAdminSettingValue('email'))->send(new AdminNfcOrderMail($nfcOrder, $vcardName, $cardType));

            Flash::success(__('messages.nfc.order_placed_success'));

            return redirect(route('user.orders'));
        } catch (Exception $e) {
            Log::error('Cashfree NFC Success Handler Error: ' . $e->getMessage());
            Flash::error('An error occurred: ' . $e->getMessage());
            return redirect(route('user.orders'));
        }
    }

    public function nfcPurchaseFailed(Request $request)
    {
        $input = $request->all();

        $isTaxEnabled = getSuperAdminSettingValue('nfc_tax_enabled') ?? false;
        $taxValue = getSuperAdminSettingValue('nfc_tax_value') ?? 0;
        $tax = null;

        if ($isTaxEnabled && $taxValue > 0) {
            $tax = $taxValue;
        }

        NfcOrderTransaction::create([
            'nfc_order_id' => $input['order_id'],
            'type' => NfcOrders::CASHFREE,
            'amount' => $input['amountToPay'],
            'user_id' => getLogInUser()->id,
            'status' => NfcOrders::FAIL,
            'tax' => $tax,
        ]);

        Flash::error(__('messages.placeholder.payment_cancel'));

        return redirect(route('user.orders'));
    }

    public function nfcCheckout(Request $request)
    {
        $input = $request->all();

        if (is_array($input)) {
            return view('payments.cashfree-checkout', [
                'paymentSessionId' => $input['paymentSessionId'],
                'orderId' => $input['order_id'],
                'returnUrl' => $input['returnUrl'],
                'mode' => $input['mode'],
            ]);
        } else {
            Flash::error(__('messages.placeholder.failed_to_initialize_payment_session'));
            return redirect()->route('user.orders');
        }
    }

    public function appointmentBookCashfree($userId, $vcard, $input)
    {
        try {
            $user = User::whereId($userId)->first();
            if ($user && $user->organisation_id) {
                $userId = $user->organisation_id;
            }
            $clientId  = getUserSettingValue('cashfree_key', $userId);
            $clientSecret = getUserSettingValue('cashfree_secret', $userId);
            $mode = getUserSettingValue('cashfree_mode', $userId);

            $baseUrl = $this->getBaseUrl($mode);

            $amount = $input['amount'];
            $reference = 'appointment_' . uniqid();
            $input['m_payment_id'] = $reference;

            $returnData = [
                'vcard_id' => $vcard->id,
                'vcard_name' => $vcard->name,
                'name'=> $input['name'],
                'toName' => $vcard->user->full_name,
                'email' => $input['email'],
                'phone' => $input['phone'],
                'region_code' => $input['region_code'] ?? null,
                'date' => $input['date'],
                'from_time' => $input['from_time'],
                'to_time' => $input['to_time'],
                'alias' => $vcard->url_alias,
                'm_payment_id' => $reference,
                'amount' => $amount,
            ];

            $customerId = 'customer_' . $userId;
            $orderData = [
                'order_id' => $reference,
                'order_amount' => number_format($amount, 2, '.', ''),
                'order_currency' => 'INR',
                'customer_details' => [
                    'customer_id' => $customerId,
                    'customer_name' => $input['name'],
                    'customer_email' => $input['email'],
                    'customer_phone' => $input['phone'],
                ],
                'order_note' => 'Appointment - ' . $vcard->name,
                'order_meta' => [
                    'return_url' => route('appointment.cashfree.success', $returnData),
                    'cancel_url' => route('appointment.cashfree.failed', $returnData),
                ],
            ];

            $response = Http::withHeaders([
                'x-client-id' => $clientId,
                'x-client-secret' => $clientSecret,
                'x-api-version' => '2023-08-01',
                'Content-Type' => 'application/json',
            ])->post($baseUrl . '/orders', $orderData);

            if (!$response->successful()) {
                Log::error('Cashfree Appointment Booking Error: ' . $response->body());
                Flash::error($response->json()['message']);
                return [
                    'success' => false,
                    'message' => $response->json()['message'],
                ];
            }

            $responseData = $response->json();
            if (empty($responseData['payment_session_id'])) {
                throw new Exception(__('messages.placeholder.payment_session_id_not_found'));
            }

            $jsMode = ($mode === 'live' || $mode === 'production') ? 'production' : 'sandbox';
            $paymentUrl = "https://{$jsMode}.cashfree.com/pg/orders/{$responseData['payment_session_id']}";
            // Redirect to a dedicated checkout view
            return [
                'success' => true,
                'payment_url' => $paymentUrl,
                'payment_session_id' => $responseData['payment_session_id'],
                'order_id' => $reference,
                'return_url' => route('nfc.cashfree.success') . '?' . http_build_query($returnData),
                'mode' => $jsMode,
            ];
        } catch (Exception $e) {
            Log::error('Cashfree Appointment Booking Error: ' . $e->getMessage());
            Flash::error($e->getMessage());
            return redirect()->back();
        }
    }

    public function appointmentBookCashfreeSuccess(Request $request)
    {
        $input = $request->all();

        $vcard = Vcard::with('tenant.user', 'template')->where('url_alias', $input['alias'])->first();
        $userId = $vcard->tenant->user->id;
        $user = User::whereId($userId)->first();
        if ($user && $user->organisation_id) {
            $userId = $user->organisation_id;
        }

        try {
            DB::beginTransaction();

            // Verify payment with Cashfree
            $clientId = getUserSettingValue('cashfree_key', $userId);
            $clientSecret = getUserSettingValue('cashfree_secret', $userId);
            $mode = getUserSettingValue('cashfree_mode', $userId);
            $baseUrl = $this->getBaseUrl($mode);

            $orderId = $input['m_payment_id'];

            if (!$orderId) {
                Flash::error(__('messages.placeholder.order_id_missing'));
                return redirect(route('vcard.show', $input['alias']));
            }

            $response = Http::withHeaders([
                'x-client-id' => $clientId,
                'x-client-secret' => $clientSecret,
                'x-api-version' => '2023-08-01',
            ])->get($baseUrl . '/orders/' . $orderId);

            if (!$response->successful() || $response->json()['order_status'] !== 'PAID') {
                Flash::error(__('messages.placeholder.payment_verification_failed'));
                return redirect(route('vcard.show', $input['alias']));
            }

            // Transaction record, appointment booking, etc.
            $appointmentTran = AppointmentTransaction::create([
                'vcard_id' => $vcard->id,
                'transaction_id' => $input['m_payment_id'],
                'currency_id' => $input['currency_id'] ?? getUserSettingValue('currency_id', $userId),
                'amount' => $input['amount'],
                'tenant_id' => $vcard->tenant->id,
                'type' => Appointment::CASHFREE,
                'status' => Transaction::SUCCESS,
            ]);

            $input['appointment_tran_id'] = $appointmentTran->id;

            $appointmentRepo = App::make(AppointmentRepository::class);
            $vcardEmail = is_null($vcard->email) ? $vcard->tenant->user->email : $vcard->email;
            $appointmentRepo->appointmentStoreOrEmail($input, $vcardEmail);

            DB::commit();

            $url = ($vcard->template->name == 'vcard11') ? $vcard->url_alias . '/contact' : $vcard->url_alias;
            return redirect(route('vcard.show', [$url, __('messages.placeholder.appointment_created')]));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Cashfree Success Handler Error: ' . $e->getMessage());
            Flash::error($e->getMessage());
            return redirect(route('vcard.show', $input['alias']));
        }
    }

    public function appointmentBookCashfreeFailed(Request $request)
    {
        $input = $request->all();
        Flash::error(__('messages.placeholder.payment_cancel'));
        return redirect(route('vcard.show', $input['alias']));
    }

    public function appointmentBookCheckoutCashfree(Request $request)
    {
        $input = $request->all();

        if (is_array($input)) {
            return view('payments.cashfree-checkout', [
                'paymentSessionId' => $input['paymentSessionId'],
                'orderId' => $input['order_id'],
                'returnUrl' => $input['returnUrl'],
                'mode' => $input['mode'],
            ]);
        } else {
            Flash::error(__('messages.placeholder.failed_to_initialize_payment_session'));
            return redirect()->route('user.orders');
        }
    }

    public function productBuy($input, $product, $userId)
    {
        try {
            $user = User::whereId($userId)->first();
            if ($user && $user->organisation_id) {
                $userId = $user->organisation_id;
            }

            $clientId = getUserSettingValue('cashfree_key', $userId);
            $clientSecret = getUserSettingValue('cashfree_secret', $userId);
            $mode = getUserSettingValue('cashfree_mode', $userId);

            $baseUrl = $this->getBaseUrl($mode);

            $amount = $product->price;

            $reference = 'product_' . uniqid();

            $returnData = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'm_payment_id' => $reference,
                'amount' => $amount,
                'name' => $input['name'],
                'email' => $input['email'],
                'phone' => $input['phone'],
                'address' => $input['address'],
            ];

            $customerId = 'customer_' . $userId;

            $orderData = [
                'order_id' => $reference,
                'order_amount' => number_format($amount, 2, '.', ''),
                'order_currency' => 'INR',
                'customer_details' => [
                    'customer_id' => $customerId,
                    'customer_name' => $input['name'],
                    'customer_email' => $input['email'],
                    'customer_phone' => $input['phone'],
                ],
                'order_note' => 'Product - ' . $product->name,
                'order_meta' => [
                    'return_url' => route('product.cashfree.success', $returnData),
                    'cancel_url' => route('product.cashfree.failed', $returnData),
                ],
            ];

            $response = Http::withHeaders([
                'x-client-id' => $clientId,
                'x-client-secret' => $clientSecret,
                'x-api-version' => '2023-08-01',
                'Content-Type' => 'application/json',
            ])->post($baseUrl . '/orders', $orderData);

            if (!$response->successful()) {
                Log::error('Cashfree Product Order Error: ' . $response->body());
                Flash::error($response->json()['message']);
                return [
                    'success' => false,
                    'message' => $response->json()['message'],
                ];
            }

            $responseData = $response->json();
            if (empty($responseData['payment_session_id'])) {
                throw new Exception(__('messages.placeholder.payment_session_id_not_found'));
            }

            $jsMode = ($mode === 'live' || $mode === 'production') ? 'production' : 'sandbox';

            $paymentUrl = "https://{$jsMode}.cashfree.com/pg/orders/{$responseData['payment_session_id']}";
            return [
                'success' => true,
                'payment_url' => $paymentUrl,
                'payment_session_id' => $responseData['payment_session_id'],
                'order_id' => $reference,
                'return_url' => route('product.cashfree.success', $returnData),
                'mode' => $jsMode,
            ];
        } catch (Exception $e) {
            Log::error('Cashfree Product Order Error: ' . $e->getMessage());
            Flash::error($e->getMessage());
            return redirect()->back();
        }
    }

    public function productBuySuccess(Request $request)
    {
        $input = $request->all();

        $product = Product::find($input['product_id']);
        $userId = $product->vcard->user->id;
        $user = User::whereId($userId)->first();
        if ($user && $user->organisation_id) {
            $userId = $user->organisation_id;
        }

        try {
            DB::beginTransaction();

            $clientId = getUserSettingValue('cashfree_key', $userId);
            $clientSecret = getUserSettingValue('cashfree_secret', $userId);
            $mode = getUserSettingValue('cashfree_mode', $userId);
            $baseUrl = ($mode === 'live') ? 'https://api.cashfree.com/pg' : 'https://sandbox.cashfree.com/pg';

            $orderId = $input['m_payment_id'];

            if (!$orderId) {
                Flash::error(__('messages.placeholder.order_id_missing'));
                return redirect(route('showProducts', [$product->vcard->id, $product->vcard->url_alias]));
            }

            $response = Http::withHeaders([
                'x-client-id' => $clientId,
                'x-client-secret' => $clientSecret,
                'x-api-version' => '2023-08-01',
            ])->get($baseUrl . '/orders/' . $orderId);

            if (!$response->successful() || $response->json()['order_status'] !== 'PAID') {
                Flash::error(__('messages.placeholder.payment_verification_failed'));
                return redirect(route('showProducts', [$product->vcard->id, $product->vcard->url_alias]));
            }

            ProductTransaction::create([
                'product_id' => $input['product_id'],
                'name' => $input['name'],
                'email' => $input['email'],
                'phone' => $input['phone'],
                'address' => $input['address'],
                'currency_id' => $product->currency_id ?? getUserSettingValue('currency_id', $userId),
                'type' => Product::CASHFREE,
                'transaction_id' => $orderId,
                'amount' => $input['amount'],
            ]);

            $orderMailData = [
                'user_name' => $product->vcard->user->full_name,
                'customer_name' => $input['name'],
                'product_name' => $product->name,
                'product_price' => $product->price,
                'phone' => $input['phone'] ?? '',
                'address' => $input['address'] ?? '',
                'payment_type' => __('messages.cashfree'),
                'order_date' => Carbon::now()->format('d M Y'),
            ];

            if (getUserSettingValue('product_order_send_mail_customer', $userId)) {
                Mail::to($input['email'])->send(new ProductOrderSendCustomer($orderMailData));
            }

            if (getUserSettingValue('product_order_send_mail_user', $userId)) {
                Mail::to($product->vcard->user->email)->send(new ProductOrderSendUser($orderMailData));
            }

            DB::commit();

            Flash::success(__('messages.placeholder.product_purchase'));

            return redirect(route('showProducts', [$product->vcard->id, $product->vcard->url_alias, __('messages.placeholder.product_purchase')]));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Cashfree Product Success Handler Error: ' . $e->getMessage());
            Flash::error($e->getMessage());
            return redirect(route('showProducts', [$product->vcard->id, $product->vcard->url_alias]));
        }
    }

    public function productBuyFailed(Request $request)
    {
        $input = $request->all();
        $product = Product::find($input['product_id']);
        $vcard = $product->vcard;

        Flash::error(__('messages.placeholder.payment_cancel'));

        return redirect(route('showProducts', [$vcard->id, $vcard->url_alias]));
    }

    public function productBuyCheckout(Request $request)
    {
        $input = $request->all();
        if (is_array($input)) {
            return view('payments.cashfree-checkout', [
                'paymentSessionId' => $input['payment_session_id'],
                'orderId' => $input['order_id'],
                'returnUrl' => $input['return_url'],
                'mode' => $input['mode'],
            ]);
        } else {
            Flash::error(__('messages.placeholder.failed_to_initialize_payment_session'));
            return redirect()->route('user.orders');
        }
    }
}
