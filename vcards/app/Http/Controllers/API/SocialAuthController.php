<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\Controller;
use App\Models\MultiTenant;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\User;
use App\Services\AppleToken;
use Carbon\Carbon;
use Exception;
use Google\Service\ApigeeRegistry\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends AppBaseController
{
    public function googleLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'name' => 'required|string',
        ]);
        $input = $request->all();

        $existingUser = User::whereEmail($input['email'])->first();

        if ($existingUser) {
            $token = $existingUser->createToken('token')->plainTextToken;

            if ($existingUser->hasRole(Role::ROLE_SUPER_ADMIN)) {
                $data = [
                    'token' => $token,
                    'user_id' => $existingUser->id,
                    'role' => 'Super Admin',
                ];
            } elseif ($existingUser->hasRole(Role::ROLE_ADMIN)) {
                $data = [
                    'token' => $token,
                    'user_id' => $existingUser->id,
                    'role' => 'Admin',
                ];
            } elseif ($existingUser->hasRole(Role::ROLE_USER)) {
                $data = [
                    'token' => $token,
                    'user_id' => $existingUser->id,
                    'role' => 'User',
                ];
            }
            return $this->sendResponse($data, __('Logged in successfully.'));
        }

        $tenant = MultiTenant::create(['tenant_username' => $input['name']]);
        $userDefaultLanguage = Setting::where('key', 'user_default_language')->first()->value ?? 'en';

        $user = User::create([
            'first_name' => $input['name'],
            'last_name' => $input['last_name'] ?? '',
            'email' => $input['email'],
            'language' => $userDefaultLanguage,
            'tenant_id' => $tenant->id,
            'affiliate_code' => generateUniqueAffiliateCode(),
        ])->assignRole(Role::ROLE_ADMIN);

        $plan = Plan::whereIsDefault(true)->first();

        Subscription::create([
            'plan_id' => $plan->id,
            'plan_amount' => $plan->price,
            'plan_frequency' => Plan::MONTHLY,
            'starts_at' => Carbon::now(),
            'ends_at' => Carbon::now()->addDays($plan->trial_days),
            'trial_ends_at' => Carbon::now()->addDays($plan->trial_days),
            'status' => Subscription::ACTIVE,
            'tenant_id' => $tenant->id,
            'no_of_vcards' => $plan->no_of_vcards,
        ]);

        $user->assignRole(Role::ROLE_ADMIN);

        $token = $user->createToken('token')->plainTextToken;

        $data = [
            'token' => $token,
            'user_id' => $user->id,
            'role' => Role::ROLE_ADMIN,
        ];

        return $this->sendResponse($data, __('Logged in successfully.'));
    }

    public function loginWithApple(Request $request, AppleToken $appleToken)
    {
        config()->set('services.apple.client_secret', $appleToken->generate());

        try {
            $loginUser = Socialite::driver('apple')->userFromToken($request->token);

            $email = $loginUser->user['email'] ?? $loginUser->email;
            $existingUser = User::whereEmail($email)->first();

            $name = Str::before($email, '@');

            if (empty($existingUser)) {

                DB::beginTransaction();

                $tenant = MultiTenant::create([
                    'tenant_username' => $name
                ]);

                $userDefaultLanguage = Setting::where('key', 'user_default_language')->first()->value ?? 'en';

                $user = User::create([
                    'email' => $email,
                    'first_name' => $name,
                    'last_name' => '',
                    'email_verified_at' => Carbon::now(),
                    'password' => bcrypt(Str::random(40)),
                    'language' => $userDefaultLanguage,
                    'tenant_id' => $tenant->id,
                    'affiliate_code' => generateUniqueAffiliateCode(),
                ])->assignRole(Role::ROLE_ADMIN);

                $plan = Plan::whereIsDefault(true)->first();

                Subscription::create([
                    'plan_id' => $plan->id,
                    'plan_amount' => $plan->price,
                    'plan_frequency' => $plan->frequency,
                    'starts_at' => Carbon::now(),
                    'ends_at' => Carbon::now()->addDays($plan->trial_days),
                    'trial_ends_at' => Carbon::now()->addDays($plan->trial_days),
                    'status' => Subscription::ACTIVE,
                    'tenant_id' => $tenant->id,
                    'no_of_vcards' => $plan->no_of_vcards,
                ]);

                DB::commit();

                $token = $user->createToken('token')->plainTextToken;

                $data = [
                    'token' => $token,
                    'user_id' => $user->id,
                    'role' => 'Admin',
                ];

                return $this->sendResponse($data, __('Logged in successfully.'));

            } else {

                $token = $existingUser->createToken('token')->plainTextToken;

                if ($existingUser->hasRole(Role::ROLE_SUPER_ADMIN)) {
                    $data = [
                        'token' => $token,
                        'user_id' => $existingUser->id,
                        'role' => 'Super Admin',
                    ];
                } elseif ($existingUser->hasRole(Role::ROLE_ADMIN)) {
                    $data = [
                        'token' => $token,
                        'user_id' => $existingUser->id,
                        'role' => 'Admin',
                    ];
                } elseif ($existingUser->hasRole(Role::ROLE_USER)) {
                    $data = [
                        'token' => $token,
                        'user_id' => $existingUser->id,
                        'role' => 'User',
                    ];
                }

                return $this->sendResponse($data, __('Logged in successfully.'));
            }

        } catch (Exception $e) {
            return $this->sendError('Invalid token', 498);
        }
    }

    public function callback(Request $request)
    {
        Log::info('callback');
        Log::info($request->all());
    }
}
