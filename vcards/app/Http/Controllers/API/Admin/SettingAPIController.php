<?php

namespace App\Http\Controllers\API\Admin;

use App\Models\Vcard;
use App\Models\Setting;
use App\Models\Language;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use App\Models\PaymentGateway;
use Illuminate\Support\Facades\DB;
use App\Models\ScheduleAppointment;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\AppBaseController;
use App\Repositories\UserSettingRepository;
use App\Http\Requests\UpdateUserSettingRequest;

class SettingAPIController extends AppBaseController
{
    /**
     * @var UserSettingRepository
     */
    private $userSettingRepository;

    /**
     * SettingController constructor.
     */
    public function __construct(UserSettingRepository $userSettingRepository)
    {
        $this->userSettingRepository = $userSettingRepository;
    }

    public function editSettings()
    {
        $setting = UserSetting::where('user_id', getLogInUserId())->pluck('value', 'key')->toArray();

        $language = Language::where('iso_code', getCurrentLanguageName())->value('name');

        $data[] = [
            'paypal_email' => $setting['paypal_email'] ?? '',
            'currency_id' => $setting['currency_id'] ?? '',
            'subscription_model_time' => $setting['subscription_model_time'] ?? '',
            'time_format' => $setting['time_format'] ?? '',
            'ask_details_before_downloading_contact' => $setting['ask_details_before_downloading_contact'] ?? '',
            'enable_attachment_for_inquiry' => $setting['enable_attachment_for_inquiry'] ?? '',
            'enable_pwa' => $setting['enable_pwa'] ?? '',
            'pwa_icon' => $setting['pwa_icon'] ?? '',
            'language' => $language ?? '',
        ];

        return $this->sendResponse($data, 'Setting data retrieved successfully.');
    }

    public function updateSettings(UpdateUserSettingRequest $request)
    {
        $input = $request->all();
        $id = Auth::id();
        $setting = UserSetting::where('user_id', getLogInUserId())->where('key', 'time_format')->first();
        $userVcards = Vcard::where('tenant_id', getLogInTenantId())->pluck('id')->toArray();
        $bookedAppointment = ScheduleAppointment::whereIn('vcard_id', $userVcards)->where(
            'status',
            ScheduleAppointment::PENDING
        )->count();
        if ($setting) {
            $timeFormat = $setting->value == UserSetting::HOUR_24 ? UserSetting::HOUR_24  : UserSetting::HOUR_12;
        }
        $requestTimeFormat = isset($request->time_format) ? $request->time_format : $timeFormat;

        $this->userSettingRepository->updateAPI($input, $id);


        return $this->sendSuccess("Setting updated successfully");
    }

    public function getPaymentConfig()
    {
        $setting = UserSetting::where('user_id', getLogInUserId())->pluck('value', 'key')->toArray();

        return $this->sendResponse($setting, 'Setting data retrieved successfully.');
    }

    public  function updatePaymentConfig(Request $request)
    {
        $id = Auth::id();
        $this->userSettingRepository->paymentMethodUpdate($request->all(), $id);
        return $this->sendSuccess("Setting updated successfully");
    }

    public function getSuperAdminPaymentStatus(Request $request)
    {
        try {
            DB::connection()->getPdo();

            $allSettings = Setting::all()->pluck('value', 'key')->toArray();

            $paymentKeys = [
                'stripe_key','stripe_secret',
                'paypal_client_id','paypal_secret','paypal_mode',
                'razorpay_key','razorpay_secret',
                'manual_payment_guide',
                'paystack_key','paystack_secret',
                'phonepe_merchant_id','phonepe_merchant_user_id','phonepe_env','phonepe_salt_key','phonepe_salt_index',
                'flutterwave_key','flutterwave_secret',
                'mp_public_key','mp_access_token',
                'iyzico_key','iyzico_secret','iyzico_mode',
                'payfast_merchant_id','payfast_merchant_key','payfast_passphrase_key','payfast_mode',
            ];

            $settings = collect($allSettings)->only($paymentKeys)->toArray();

            $enabledGateways = PaymentGateway::pluck('payment_gateway')->map(fn($g) => strtolower($g))->toArray();

            $status = [
                'stripe_enabled'      => in_array('stripe', $enabledGateways)
                                    && !empty($settings['stripe_key'])
                                    && !empty($settings['stripe_secret']),

                'paypal_enabled'      => in_array('paypal', $enabledGateways)
                                    && !empty($settings['paypal_client_id'])
                                    && !empty($settings['paypal_secret']),

                'razorpay_enabled'    => in_array('razorpay', $enabledGateways)
                                    && !empty($settings['razorpay_key'])
                                    && !empty($settings['razorpay_secret']),

                'flutterwave_enabled' => in_array('flutterwave', $enabledGateways)
                                    && !empty($settings['flutterwave_key'])
                                    && !empty($settings['flutterwave_secret']),

                'paystack_enabled'    => in_array('paystack', $enabledGateways)
                                    && !empty($settings['paystack_key'])
                                    && !empty($settings['paystack_secret']),

                'phonepe_enabled'     => in_array('phonepe', $enabledGateways)
                                    && !empty($settings['phonepe_merchant_id'])
                                    && !empty($settings['phonepe_salt_key']),

                'iyzico_enabled'      => in_array('iyzico', $enabledGateways)
                                    && !empty($settings['iyzico_key'])
                                    && !empty($settings['iyzico_secret']),

                'payfast_enabled'     => in_array('payfast', $enabledGateways)
                                    && !empty($settings['payfast_merchant_id'])
                                    && !empty($settings['payfast_merchant_key']),

                'mercadopago_enabled'     => in_array('mercadopago', $enabledGateways)
                                    && !empty($settings['mp_public_key'])
                                    && !empty($settings['mp_access_token']),

                'manual_enabled' => in_array('manual', $enabledGateways) || in_array('manually', $enabledGateways),
            ];

            return [
                'credentials' => $settings,
                'status'      => $status,
            ];
        } catch (\Exception $e) {
            Log::error('Payment Config Error: ' . $e->getMessage());
            return [
                'credentials' => [],
                'status'      => [],
            ];
        }
    }

        function getSuperAdminPaymentConfig()
    {
        try {
                DB::connection()->getPdo();

            $allSettings = Setting::all()->pluck('value', 'key')->toArray();

            $paymentKeys = [
                'stripe_key', 'stripe_secret',
                'paypal_client_id', 'paypal_secret', 'paypal_mode',
                'razorpay_key', 'razorpay_secret',
                'flutterwave_key', 'flutterwave_secret',
                'paystack_key', 'paystack_secret',
                'phonepe_merchant_id', 'phonepe_merchant_user_id', 'phonepe_env', 'phonepe_salt_key', 'phonepe_salt_index',
                'iyzico_key', 'iyzico_secret', 'iyzico_mode',
                'payfast_merchant_id', 'payfast_merchant_key', 'payfast_passphrase_key', 'payfast_mode',
                'mp_public_key','mp_access_token',
                'manual_payment_guide',
            ];

            $settings = collect($allSettings)->only($paymentKeys)->toArray();

            $enabledGateways = \App\Models\PaymentGateway::pluck('payment_gateway')->map(fn($g) => strtolower($g))->toArray();

            $status = [
                'stripe_enabled'      => in_array('stripe', $enabledGateways)
                                    && !empty($settings['stripe_key'])
                                    && !empty($settings['stripe_secret']),

                'paypal_enabled'      => in_array('paypal', $enabledGateways)
                                    && !empty($settings['paypal_client_id'])
                                    && !empty($settings['paypal_secret']),

                'razorpay_enabled'    => in_array('razorpay', $enabledGateways)
                                    && !empty($settings['razorpay_key'])
                                    && !empty($settings['razorpay_secret']),

                'flutterwave_enabled' => in_array('flutterwave', $enabledGateways)
                                    && !empty($settings['flutterwave_key'])
                                    && !empty($settings['flutterwave_secret']),

                'paystack_enabled'    => in_array('paystack', $enabledGateways)
                                    && !empty($settings['paystack_key'])
                                    && !empty($settings['paystack_secret']),

                'phonepe_enabled'     => in_array('phonepe', $enabledGateways)
                                    && !empty($settings['phonepe_merchant_id'])
                                    && !empty($settings['phonepe_salt_key']),

                'iyzico_enabled'      => in_array('iyzico', $enabledGateways)
                                    && !empty($settings['iyzico_key'])
                                    && !empty($settings['iyzico_secret']),

                'payfast_enabled'     => in_array('payfast', $enabledGateways)
                                    && !empty($settings['payfast_merchant_id'])
                                    && !empty($settings['payfast_merchant_key']),

                'mercadopago_enabled'     => in_array('mercadopago', $enabledGateways)
                                    && !empty($settings['mp_public_key'])
                                    && !empty($settings['mp_access_token']),

                'manual_enabled' => in_array('manual', $enabledGateways) || in_array('manually', $enabledGateways),
            ];

            return [
                'credentials' => $settings,
                'status'      => $status,
                'message'     => 'Super Admin Payment Config retrieved successfully.',
            ];
        } catch (\Exception $e) {
            \Log::error('Payment Config Error: ' . $e->getMessage());
            return [
                'credentials' => [],
                'status'      => [],
            ];
        }
    }

    public function updateSuperAdminPaymentConfig(Request $request)
    {
        // Allowed payment keys
        $paymentKeys = [
            'stripe_key','stripe_secret',
            'paypal_client_id','paypal_secret','paypal_mode',
            'razorpay_key','razorpay_secret',
            'manual_payment_guide',
            'paystack_key','paystack_secret',
            'phonepe_merchant_id','phonepe_merchant_user_id','phonepe_env','phonepe_salt_key','phonepe_salt_index',
            'flutterwave_key','flutterwave_secret',
            'mp_public_key','mp_access_token',
            'iyzico_key','iyzico_secret','iyzico_mode',
            'payfast_merchant_id','payfast_merchant_key','payfast_passphrase_key','payfast_mode',
        ];

        // Boolean toggle mapping
        $boolToggleMap = [
            'stripe_enabled'      => 'Stripe',
            'paypal_enabled'      => 'Paypal',
            'razorpay_enabled'    => 'Razorpay',
            'manual_enabled'    => 'Manually',
            'paystack_enabled'    => 'Paystack',
            'phonepe_enabled'     => 'Phonepe',
            'flutterwave_enabled' => 'Flutterwave',
            'mercadopago_enabled' => 'Mercadopago',
            'iyzico_enabled'      => 'Iyzico',
            'payfast_enabled'     => 'Payfast',
        ];

        // Gateway credential fields
        $gatewayKeyMap = [
            'Stripe'     => ['stripe_key','stripe_secret'],
            'Paypal'     => ['paypal_client_id','paypal_secret','paypal_mode'],
            'Razorpay'   => ['razorpay_key','razorpay_secret'],
            'Manually'   => ['manual_payment_guide'],
            'Paystack'   => ['paystack_key','paystack_secret'],
            'Phonepe'    => ['phonepe_merchant_id','phonepe_salt_key','phonepe_merchant_user_id','phonepe_env','phonepe_salt_index'],
            'Flutterwave'=> ['flutterwave_key','flutterwave_secret'],
            'Mercadopago'=> ['mp_public_key','mp_access_token'],
            'Iyzico'     => ['iyzico_key','iyzico_secret','iyzico_mode'],
            'Payfast'    => ['payfast_merchant_id','payfast_merchant_key','payfast_passphrase_key','payfast_mode'],
        ];

        try {
            DB::beginTransaction();

            $gatewayMap = \App\Models\Plan::PAYMENT_METHOD;
            $lowerGatewayMap = array_map('strtolower', $gatewayMap);

            $hasToggle = false;

            foreach ($boolToggleMap as $boolKey => $gatewayName) {
                if (! $request->has($boolKey)) {
                    continue;
                }

                $hasToggle = true;

                $search = strtolower($gatewayName);
                $gatewayId = array_search($search, $lowerGatewayMap, true);

                if ($gatewayId === false) {
                    continue;
                }

                if (filter_var($request->input($boolKey), FILTER_VALIDATE_BOOLEAN)) {
                    $label = $gatewayMap[$gatewayId];
                    PaymentGateway::updateOrCreate(
                        ['payment_gateway_id' => $gatewayId],
                        ['payment_gateway' => $label]
                    );
                } else {
                    // explicitly disabled -> remove row
                    PaymentGateway::where('payment_gateway_id', $gatewayId)->delete();
                }
            }

            // After processing toggles (or none), build normalized enabled names list for validation
            $enabledGatewayNames = PaymentGateway::pluck('payment_gateway')
                ->map(fn($g) => strtolower($g))
                ->toArray();

            $requiredErrors = [];
            foreach ($gatewayKeyMap as $gateway => $keys) {
                $gatewayLower = strtolower($gateway);

                // only validate gateways that are enabled
                if (in_array($gatewayLower, $enabledGatewayNames)) {
                    foreach ($keys as $field) {
                        // field missing or empty => error
                        $dbValue = getSuperAdminSettingValue($field);
                        $reqValue = $request->input($field);

                        if (
                            (
                                (!$request->has($field) || $reqValue === null || $reqValue === '')
                                && ($dbValue === null || $dbValue === '')
                            )
                        ) {
                            $requiredErrors[] = "$field is required when $gateway is enabled.";
                        }
                    }
                }
            }

            if (!empty($requiredErrors)) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Required fields missing.',
                    'errors'  => $requiredErrors
                ], 400);
            }

            // Validate credential updates only allowed for enabled gateways
            $errors = [];
            foreach ($gatewayKeyMap as $gateway => $keys) {
                foreach ($keys as $keyName) {
                    if ($request->has($keyName) && !in_array(strtolower($gateway), $enabledGatewayNames)) {
                        $errors[] = ucfirst($gateway) . " is disabled. Cannot update its credentials.";
                        break;
                    }
                }
            }

            if (!empty($errors)) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Some gateways are disabled.',
                    'errors'  => $errors
                ], 400);
            }

            // Save settings
            $input = $request->only($paymentKeys);

            foreach ($input as $key => $value) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value ?? '']
                );
            }

            DB::commit();

            return response()->json([
                'message' => 'Payment configuration updated successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Update failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
