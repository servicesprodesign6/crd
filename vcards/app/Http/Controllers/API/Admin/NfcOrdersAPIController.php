<?php

namespace App\Http\Controllers\API\Admin;

use App\Models\Nfc;
use App\Models\Vcard;
use App\Models\Setting;
use App\Models\NfcOrders;
use Illuminate\Http\Request;
use App\Mail\AdminNfcOrderMail;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\NfcOrderTransaction;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\NfcOrderRequest;
use App\Http\Controllers\AppBaseController;
use App\Http\Controllers\API\Admin\StripeAPIController;
use Modules\SlackIntegration\Entities\SlackIntegration;
use App\Http\Controllers\API\Admin\PayfastAPIController;
use App\Http\Controllers\API\Admin\PhonepeAPIController;
use App\Http\Controllers\API\Admin\PaystackAPIController;
use App\Http\Controllers\API\Admin\RazorpayAPIController;
use App\Http\Controllers\API\Admin\FlutterwaveAPIController;
use App\Http\Controllers\API\Admin\MercadoPagoAPIController;
use Modules\SlackIntegration\Notifications\SlackNotification;

class NfcOrdersAPIController extends AppBaseController
{
    public function create()
    {
        $vcards = Vcard::whereTenantId(getLogInTenantId())->where('status', Vcard::ACTIVE)->pluck('name', 'id')->toArray();
        $nfcCards  = Nfc::all();
        // $paymentTypes = getPaymentGateway();
        $currency = getCurrencyIcon(getSuperAdminSettingValue('default_currency'));

        return response()->json([
            'success' => true,
            'data' => [
                'vcards' => $vcards,
                'nfc_cards' => $nfcCards,
                // 'payment_types' => $paymentTypes,
                'currency' => $currency,
            ],
        ]);
    }

    public function getVcardData(Request $request)
    {
        $vcardId = $request->input('vcardId');

        if (empty($vcardId)) {
            return response()->json([
                'success' => false,
                'message' => 'vcardId is required',
            ], 422);
        }

        $vcard = Vcard::with('socialLink')->findOrFail($vcardId);

        $data = [
            'id' => $vcard['id'],
            'first_name' => $vcard['first_name'],
            'last_name' => $vcard['last_name'],
            'email' => $vcard['email'],
            'occupation' => $vcard['occupation'],
            'location' => $vcard['location'],
            'phone' => $vcard['phone'],
            'region_code' => $vcard['region_code'],
            'company' => $vcard['company'],

        ];

        return response()->json(['data' => $data, 'success' => true]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'card_type' => ['required', 'integer', 'exists:nfcs,id'],
            'quantity' => ['required', 'integer', 'min:1'],

            'vcard_id' => [
                'required',
                Rule::exists('vcards', 'id')
                    ->where('tenant_id', getLogInTenantId())
                    ->where('status', Vcard::ACTIVE),
            ],

            'company_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'string', 'max:20'],
            'region_code' => ['nullable', 'string', 'max:10'],
            'designation' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'payment_method' => ['required', 'integer'],
        ]);
        try {
            DB::beginTransaction();

            $input = $request->all();
            $input['user_id'] = getLogInUserId();

            if (Setting::where('key', 'nfc_logo_required')->first()->value == 1) {
                $request->validate([
                    'logo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
                ]);
            }

            if (getSuperAdminSettingValue('default_currency') == null) {
                return $this->sendError('currency is not set.', 500);
            }

            $nfcOrder = NfcOrders::create($input);

            if ($request->hasFile('logo')) {
                $nfcOrder->addMedia($input['logo'])->toMediaCollection(NfcOrders::LOGO_PATH);
            }

            DB::commit();

            if ($input['payment_method'] == NfcOrders::STRIPE) {

                $stripe = app(StripeAPIController::class);

                $stripeRequest = new Request([
                    'order_id' => $nfcOrder->id,
                    'email' => $nfcOrder->email,
                    'currency' => getSuperAdminSettingValue('default_currency'),
                ]);

                return $stripe->createNfcStripeSession($stripeRequest);
            }

            if ($input['payment_method'] == NfcOrders::RAZOR_PAY) {

                $razorpay = app(RazorpayAPIController::class);

                $razorpayRequest = new Request([
                    'order_id' => $nfcOrder->id,
                    'email' => $nfcOrder->email,
                    'currency' => getSuperAdminSettingValue('default_currency'),
                ]);

                return $razorpay->createNfcRazorpayOnBoard($razorpayRequest);
            }

            if ($input['payment_method'] == NfcOrders::PAYSTACK) {

                $paystack = app(PaystackAPIController::class);

                $paystackRequest = new Request([
                    'order_id' => $nfcOrder->id,
                    'email' => $nfcOrder->email,
                    'currency' => getSuperAdminSettingValue('default_currency'),
                ]);

                return $paystack->redirectToGatewayNfcCard($paystackRequest);
            }

            if ($input['payment_method'] == NfcOrders::FLUTTERWAVE) {

                $flutterwave = app(FlutterwaveAPIController::class);

                $flutterwaveRequest = new Request([
                    'order_id' => $nfcOrder->id,
                    'email' => $nfcOrder->email,
                    'currency' => getSuperAdminSettingValue('default_currency'),
                ]);

                return $flutterwave->createNfcFlutterwaveOnBoard($flutterwaveRequest);
            }

            if ($input['payment_method'] == NfcOrders::PAYFAST) {

                $payfast = app(PayfastAPIController::class);

                $payfastRequest = new Request([
                    'order_id' => $nfcOrder->id,
                    'email' => $nfcOrder->email,
                    'currency' => getSuperAdminSettingValue('default_currency'),
                ]);

                return $payfast->payfastNfcOrder($payfastRequest);
            }

            if ($input['payment_method'] == NfcOrders::PAYPAL) {

                $paypal = app(PaypalAPIController::class);

                $paypalRequest = new Request([
                    'order_id' => $nfcOrder->id,
                    'email' => $nfcOrder->email,
                    'currency' => getSuperAdminSettingValue('default_currency'),
                ]);

                return $paypal->paypalNfcOnBoard($paypalRequest);
            }

            if ($input['payment_method'] == NfcOrders::MERCADO_PAGO) {

                $mercadopago = app(MercadoPagoAPIController::class);

                $mercadopagoRequest = new Request([
                    'order_id' => $nfcOrder->id,
                    'email' => $nfcOrder->email,
                    'currency' => getSuperAdminSettingValue('default_currency'),
                ]);

                return $mercadopago->nfcOnBoard($mercadopagoRequest);
            }

            if ($input['payment_method'] == NfcOrders::IYZICO) {

                $iyzico = app(IyzicoAPIController::class);

                $iyzicoRequest = new Request([
                    'order_id' => $nfcOrder->id,
                    'email' => $nfcOrder->email,
                    'currency' => getSuperAdminSettingValue('default_currency'),
                ]);

                return $iyzico->nfcOrder($iyzicoRequest);
            }

            if ($input['payment_method'] == NfcOrders::PHONEPE) {

                $phonepe = app(PhonepeAPIController::class);

                $phonepeRequest = new Request([
                    'order_id' => $nfcOrder->id,
                    'email' => $nfcOrder->email,
                    'currency' => getSuperAdminSettingValue('default_currency'),
                ]);

                return $phonepe->nfcOrder($phonepeRequest);
            }

            if ($input['payment_method'] == NfcOrders::MANUALLY) {

                // Base amount
                $baseAmount = $nfcOrder->nfcCard->price * $nfcOrder->quantity;

                // Tax config
                $isTaxEnabled = getSuperAdminSettingValue('nfc_tax_enabled') ?? false;
                $taxValue = getSuperAdminSettingValue('nfc_tax_value') ?? 0;

                $taxAmount = 0;
                $tax = null;

                if ($isTaxEnabled && $taxValue > 0) {
                    $taxAmount = ($baseAmount * $taxValue) / 100;
                    $tax = $taxValue;
                }

                $totalAmount = $baseAmount + $taxAmount;

                NfcOrderTransaction::create([
                    'nfc_order_id' => $nfcOrder->id,
                    'type' => NfcOrders::MANUALLY,
                    'transaction_id' => null,
                    'amount' => $totalAmount,
                    'user_id' => $nfcOrder->user_id,
                    'status' => NfcOrders::PENDING,
                    'tax' => $tax,
                ]);

                $vcardName = VCard::find($nfcOrder['vcard_id'])->name;
                $cardType = Nfc::find($nfcOrder['card_type'])->name;

                Mail::to(getSuperAdminSettingValue('email'))->send(new AdminNfcOrderMail($nfcOrder, $vcardName, $cardType));

                if (moduleExists('SlackIntegration')) {
                    $slackIntegration = SlackIntegration::first();

                    if ($slackIntegration && $slackIntegration->user_order_nfc_card_notification == 1 && !empty($slackIntegration->webhook_url)) {
                        $message = "🔔 New NFC Order Received !!!\n{$request->name} has ordered {$cardType} with Qty: {$request->quantity}.";
                        $slackIntegration->notify(new SlackNotification($message));
                    }
                }

                return $this->sendResponse([
                    'order_id' => $nfcOrder->id,
                    'base_amount' => round($baseAmount, 2),
                    'tax_amount' => round($taxAmount,2),
                    'total_amount' => round($totalAmount,2),
                    'status' => 'pending',
                ], 'Order placed successfully.');
            }
            return $this->sendError('Invalid payment method.');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), 500);
        }
    }
}
