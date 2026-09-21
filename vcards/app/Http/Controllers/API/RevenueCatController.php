<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Mail\SubscriptionPaymentSuccessMail;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class RevenueCatController extends AppBaseController
{
    public function handle(Request $request)
    {
        $event = $request->all();

        $type = $event['event']['type'] ?? null;
        $userId = $event['event']['app_user_id'] ?? null;
        $productId = $event['event']['product_id'] ?? null;
        $expiry = $event['event']['expiration_at_ms'] ?? null;
        $revenueCatTransactionId = $event['event']['transaction_id']
            ?? $event['event']['original_transaction_id']
            ?? null;

        $paymentType = Subscription::IOS;

        if (! $type || ! $userId) {
            return $this->sendError('Invalid webhook payload');
        }

        $user = User::find($userId);

        if (! $user) {
            return $this->sendError('User not found', 404);
        }

        switch ($type) {
            case 'INITIAL_PURCHASE':
                $productMap = [
                    'v_card_premium_plans' => 'Premium',
                    'v_card_standard_plans' => 'Standard',
                    'v_card_basic_plans' => 'Basic',
                ];
                $planName = $productMap[$productId] ?? null;
                if (!$planName) {
                    return $this->sendError('Invalid product id', 404);
                }
                $plan = Plan::where('name', $planName)->first();

                if (! $plan) {
                    return $this->sendError('Plan not found', 404);
                }

                if ($revenueCatTransactionId && Transaction::where('transaction_id', $revenueCatTransactionId)->exists()) {
                    return $this->sendResponse([
                        'event' => $type,
                        'transaction_id' => $revenueCatTransactionId,
                    ], 'Duplicate transaction already processed');
                }

                $subscription = Subscription::create([
                    'tenant_id' => $user->tenant_id,
                    'plan_id' => $plan->id,
                    'plan_amount' => $plan->price,
                    'payable_amount' => $plan->price,
                    'plan_frequency' => $plan->frequency,
                    'starts_at' => now(),
                    'ends_at' => $expiry
                        ? Carbon::createFromTimestampMs($expiry)
                        : now()->addMonth(),
                    'status' => Subscription::ACTIVE,
                    'no_of_vcards' => $plan->no_of_vcards,
                    'no_of_whatsapp_store' => $plan->no_of_whatsapp_store,
                    'payment_type' => $paymentType,
                ]);

                Subscription::where('tenant_id', $user->tenant_id)
                    ->where('id', '!=', $subscription->id)
                    ->where('status', '!=', Subscription::REJECT)
                    ->update([
                        'status' => Subscription::INACTIVE,
                    ]);

                if ($revenueCatTransactionId) {
                    $transaction = Transaction::create([
                        'transaction_id' => $revenueCatTransactionId,
                        'amount' => $plan->price,
                        'type' => $paymentType,
                        'tenant_id' => $user->tenant_id,
                        'status' => Transaction::SUCCESS,
                        'meta' => $event['event'],
                    ]);

                    $subscription->update([
                        'transaction_id' => $transaction->id,
                    ]);
                }

                Mail::to($user->email)->send(
                    new SubscriptionPaymentSuccessMail([
                        'subscriptionID' => $subscription->id,
                        'amountToPay' => $subscription->payable_amount,
                        'planName' => $plan->name,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                    ])
                );

                manageVcards($user);

                return $this->sendResponse([
                    'event' => $type,
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'subscription_id' => $subscription->id,
                    'transaction_id' => $revenueCatTransactionId,
                    'ends_at' => $subscription->ends_at,
                ], 'Subscription created successfully');

            case 'RENEWAL':
                $productMap = [
                    'v_card_premium_plans' => 'Premium',
                    'v_card_standard_plans' => 'Standard',
                    'v_card_basic_plans' => 'Basic',
                ];
                $planName = $productMap[$productId] ?? null;
                if (!$planName) {
                    return $this->sendError('Invalid product id', 404);
                }
                $plan = Plan::where('name', $planName)->first();

                if (! $plan) {
                    return $this->sendError('Plan not found', 404);
                }

                if ($revenueCatTransactionId && Transaction::where('transaction_id', $revenueCatTransactionId)->exists()) {
                    return $this->sendResponse([
                        'event' => $type,
                        'transaction_id' => $revenueCatTransactionId,
                    ], 'Duplicate transaction already processed');
                }

                $subscription = Subscription::create([
                    'tenant_id' => $user->tenant_id,
                    'plan_id' => $plan->id,
                    'plan_amount' => $plan->price,
                    'payable_amount' => $plan->price,
                    'plan_frequency' => $plan->frequency,
                    'starts_at' => now(),
                    'ends_at' => $expiry
                        ? Carbon::createFromTimestampMs($expiry)
                        : now()->addMonth(),
                    'status' => Subscription::ACTIVE,
                    'no_of_vcards' => $plan->no_of_vcards,
                    'no_of_whatsapp_store' => $plan->no_of_whatsapp_store,
                    'payment_type' => $paymentType,
                ]);

                Subscription::where('tenant_id', $user->tenant_id)
                    ->where('id', '!=', $subscription->id)
                    ->where('status', '!=', Subscription::REJECT)
                    ->update([
                        'status' => Subscription::INACTIVE,
                    ]);

                if ($revenueCatTransactionId) {
                    $transaction = Transaction::create([
                        'transaction_id' => $revenueCatTransactionId,
                        'amount' => $plan->price,
                        'type' => $paymentType,
                        'tenant_id' => $user->tenant_id,
                        'status' => Transaction::SUCCESS,
                        'meta' => $event['event'],
                    ]);

                    $subscription->update([
                        'transaction_id' => $transaction->id,
                    ]);
                }

                Mail::to($user->email)->send(
                    new SubscriptionPaymentSuccessMail([
                        'subscriptionID' => $subscription->id,
                        'amountToPay' => $subscription->payable_amount,
                        'planName' => $plan->name,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                    ])
                );

                manageVcards($user);

                return $this->sendResponse([
                    'event' => $type,
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'subscription_id' => $subscription->id,
                    'transaction_id' => $revenueCatTransactionId,
                    'ends_at' => $subscription->ends_at,
                ], 'Subscription renewed successfully');

            case 'CANCELLATION':
                break;

            case 'EXPIRATION':
                return $this->sendResponse([
                    'event' => $type,
                    'user_id' => $user->id,
                ], 'Subscription expired successfully');
        }

        return $this->sendSuccess('Webhook processed');
    }
}
