<?php

namespace App\Http\Middleware;

use App\Models\Vcard;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $subscription = getCurrentSubscription();

        if (! $subscription || $subscription->isExpired()) {
            Vcard::where('tenant_id', getLogInUser()->tenant_id)->update([
                'status' => 0,
            ]);

            if (isOrganisationStaffSubscriptionExpired()) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'email' => __('messages.subscription.organization_subscription_expired'),
                ]);
            }

            return redirect(route('subscription.upgrade'))
                ->withErrors(__('messages.subscription.subscription_expired'));
        }

        return $next($request);
    }
}
