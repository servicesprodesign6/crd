<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\AppBaseController;
use App\Mail\SubscriptionPaymentSuccessMail;
use App\Models\AffiliateUser;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Repositories\SubscriptionRepository;
use App\Services\GooglePlayService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class InAppPurchaseAPIController extends AppBaseController
{
    protected $googlePlayService;
    private $subscriptionRepository;

    public function __construct(GooglePlayService $googlePlayService, SubscriptionRepository $subscriptionRepository)
    {
        $this->googlePlayService = $googlePlayService;
        $this->subscriptionRepository = $subscriptionRepository;
    }

    public function verifySubscription(Request $request)
    {
        if ($request->type == 'ios') {

            $request->validate([
                'purchase_token' => 'required',
            ]);

            return $this->iosSubscriptionStatus($request);
        }

        // ANDROID
        $request->validate([
            'subscription_id' => 'required', // Google product id
            'purchase_token' => 'required',
        ]);

        return $this->googleSubscriptionStatus($request);
    }
    public function googleSubscriptionStatus(Request $request)
    {
        try {

            // 🔹 Step 1: Verify Google purchase FIRST
            $response = $this->googlePlayService->getSubscriptionPurchase(
                config('app.pkg_name'),
                $request->subscription_id,
                $request->purchase_token
            );

            $response = (array) $response;

            if (!isset($response['paymentState']) || !in_array($response['paymentState'], [1, 2])) {
                return $this->sendError('Subscription is not active.', 404);
            }

            // 🔹 Step 2: Get Plan using name
            $plan = Plan::where('name', $request->subscription_id)->first();

            if (!$plan) {
                return $this->sendError('Plan not found.');
            }

            // 🔹 Inject planId into request for manageSubscription
            $request->merge(['planId' => $plan->id]);

            $data = $this->subscriptionRepository->manageSubscription($request->all());

            if (!isset($data['subscription'])) {
                return $this->sendError('Failed to create subscription.');
            }

            $subscription = $data['subscription'];

            return $this->activateSubscription(
                $subscription,
                $request->purchase_token,
                Subscription::GOOGLE,
                $response
            );

        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), 500);
        }
    }

    public function iosSubscriptionStatus(Request $request)
    {
        try {
            $sandboxURL = 'https://sandbox.itunes.apple.com/verifyReceipt';
            $productionURL = 'https://buy.itunes.apple.com/verifyReceipt';

            $response = Http::post($productionURL, [
                'receipt-data' => $request->purchase_token,
                'password' => config('app.ios_shared_secret'),
                'exclude-old-transactions' => true,
            ]);

            $data = $response->json();

            if (isset($data['status']) && $data['status'] == 21007) {
                $response = Http::post($sandboxURL, [
                    'receipt-data' => $request->purchase_token,
                    'password' => config('app.ios_shared_secret'),
                    'exclude-old-transactions' => true,
                ]);
                $data = $response->json();
            }

            if (!isset($data['latest_receipt_info'][0])) {
                return $this->sendError('Subscription is not active.', 404);
            }

            $productId = $data['latest_receipt_info'][0]['product_id'];
            $transactionId = $data['latest_receipt_info'][0]['transaction_id'];

            // 🔹 Find plan using name
            $plan = Plan::where('name', $productId)->first();

            if (!$plan) {
                return $this->sendError('Plan not found.');
            }

            $request->merge(['planId' => $plan->id]);

            $subscriptionData = $this->subscriptionRepository->manageSubscription($request->all());

            if (!isset($subscriptionData['subscription'])) {
                return $this->sendError('Failed to create subscription.');
            }

            $subscription = $subscriptionData['subscription'];

            return $this->activateSubscription(
                $subscription,
                $transactionId,
                Subscription::IOS,
                $data
            );

        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), 500);
        }
    }


    private function activateSubscription($subscription, $gatewayTransactionId, $type, $meta)
    {
        if (Subscription::where('transaction_id', $gatewayTransactionId)->exists()) {
            return $this->sendError('Subscription already activated.');
        }

        $subscription->update([
            'payment_type' => $type,
            'status' => Subscription::ACTIVE
        ]);

        Subscription::whereTenantId(getLogInTenantId())
            ->where('id', '!=', $subscription->id)
            ->where('status', '!=', Subscription::REJECT)
            ->update(['status' => Subscription::INACTIVE]);

        $transaction = Transaction::create([
            'tenant_id' => $subscription->tenant_id,
            'transaction_id' => $gatewayTransactionId,
            'type' => $type,
            'amount' => $subscription->payable_amount,
            'status' => Subscription::ACTIVE,
            'meta' => json_encode($meta),
        ]);

        $subscription->update([
            'transaction_id' => $transaction->id
        ]);

        // Affiliate logic
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
            $amount = $subscription->payable_amount * $affiliateAmount / 100;
            AffiliateUser::whereUserId(getLogInUserId())
                ->where('amount', 0)
                ->withoutGlobalScopes()
                ->update([
                    'amount' => $amount,
                    'is_verified' => 1
                ]);
        }

        // Send email
        Mail::to(getLogInUser()->email)->send(
            new SubscriptionPaymentSuccessMail([
                'subscriptionID' => $subscription->id,
                'amountToPay' => $subscription->payable_amount,
                'planName' => $subscription->plan->name,
                'first_name' => getLogInUser()->first_name,
                'last_name' => getLogInUser()->last_name,
            ])
        );

        manageVcards();

        return $this->sendResponse([
            'subscription_id' => $subscription->id,
            'transaction_id' => $transaction->id,
            'plan_name' => $subscription->plan->name,
            'amount' => $subscription->payable_amount,
            'expires_at' => $subscription->ends_at,
            'status' => 'active'
        ], 'Payment completed successfully.');
    }
}
