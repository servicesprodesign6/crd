<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Nfc;
use App\Models\User;
use App\Models\Vcard;
use App\Models\Product;
use App\Models\Currency;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\ProductOrderSendCustomer;
use App\Models\AppointmentTransaction;
use Illuminate\Support\Facades\Session;
use WandesCardoso\MercadoPago\DTO\Item;
use WandesCardoso\MercadoPago\DTO\Payer;
use App\Repositories\AppointmentRepository;
use WandesCardoso\MercadoPago\DTO\BackUrls;
use App\Mail\SubscriptionPaymentSuccessMail;
use App\Repositories\SubscriptionRepository;
use WandesCardoso\MercadoPago\DTO\Preference;
use WandesCardoso\MercadoPago\Facades\MercadoPago;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class MercadoPagoController extends AppBaseController
{
    /**
     * @var SubscriptionRepository
     */
    private $subscriptionRepository;

    public function __construct(SubscriptionRepository $subscriptionRepository)
    {
        $this->subscriptionRepository = $subscriptionRepository;
    }
    public function onBoard(Request $request)
    {
        config(['mercadopago.access_token' => getSelectedPaymentGateway('mp_access_token')]);
        config(['mercadopago.public_key' => getSelectedPaymentGateway('mp_public_key')]);
        try {
            $data = $this->subscriptionRepository->manageSubscription($request->all());
            $subscription = $data['subscription'];
            $plan = $data['plan'];
            if (isset($subscription->plan->currency->currency_code) && ! in_array(
                strtoupper($subscription->plan->currency->currency_code),
                getMercadoPagoSupportedCurrencies()
            )) {
                return $this->sendError(__('messages.placeholder.this_currency_is_not_supported_mercadopago'));
            }

            $payer = new Payer(
                Auth::user()->first_name,
                Auth::user()->last_name,
                Auth::user()->email,
            );


            $item = Item::make()
                ->setTitle($plan->name)
                ->setQuantity(1)
                ->setId(Auth::user()->id)
                ->setCategoryId($subscription->id)
                ->setUnitPrice((int)round($data['amountToPay'], 2));

            $preference = Preference::make()
                ->setPayer($payer)
                ->addItem($item)
                ->setBackUrls(new BackUrls(
                    route('mercadopago.success'),
                ))
                ->setAutoReturn('approved')
                ->setExternalReference($plan->id);

            $response = MercadoPago::preference()->create($preference);
            $response['body']->items[0]->currency_id = strtoupper($subscription->plan->currency->currency_code);

            return $this->sendResponse($response, 'Order created successfully');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return $this->sendError($e->getMessage());
        }
    }

    public function paymentSuccess(Request $request)
    {
        $input = $request->all();

        config(['mercadopago.access_token' => getSelectedPaymentGateway('mp_access_token')]);
        config(['mercadopago.public_key' => getSelectedPaymentGateway('mp_public_key')]);
        $marcaPagoOrder = mercadoPago()->payment()->find($input['payment_id']);
        if ($marcaPagoOrder['httpCode'] != 200) {
            Flash::error(__('messages.placeholder.payment_cancel'));
            return view('sadmin.plans.payment.paymentcancel');
        }

        try {
            DB::beginTransaction();
            $amount = $marcaPagoOrder['body']->transaction_amount;
            $subscriptionId = $marcaPagoOrder['body']->additional_info->items[0]->category_id;

            $userId = $marcaPagoOrder['body']->additional_info->items[0]->id;
            $user = User::findOrFail($userId);
            $subscription = Subscription::findOrFail($subscriptionId);

            $subscription->update([
                'payment_type' => Subscription::MERCADO_PAGO,
                'status' => Subscription::ACTIVE
            ]);

            Subscription::whereTenantId($subscription->tenant_id)
                ->where('id', '!=', $subscriptionId)
                ->where('status', '!=', Subscription::REJECT)
                ->update([
                    'status' => Subscription::INACTIVE,
                ]);


            $transaction = Transaction::create([
                'tenant_id' => $subscription->tenant_id,
                'transaction_id' => $input['payment_id'],
                'type' => Transaction::MERCADO_PAGO,
                'amount' => $amount,
                'status' => Subscription::ACTIVE,
                'meta' => json_encode($input),
            ]);

            $planName = $subscription->plan->name;

            $subscription->update(['transaction_id' => $transaction->id]);

            $affiliateAmount = getSuperAdminSettingValue('affiliation_amount');
            $affiliateAmountType = getSuperAdminSettingValue('affiliation_amount_type');
            if ($affiliateAmountType == 1) {
                AffiliateUser::whereUserId($user->id)->where('amount', 0)->withoutGlobalScopes()->update(['amount' => $affiliateAmount, 'is_verified' => 1]);
            } else if ($affiliateAmountType == 2) {
                $amount = $amount * $affiliateAmount / 100;
                AffiliateUser::whereUserId($user->id)->where('amount', 0)->withoutGlobalScopes()->update(['amount' => $amount, 'is_verified' => 1]);
            }

            $userEmail = $user->email;
            $firstName = $user->first_name;
            $lastName =  $user->last_name;
            $emailData = [
                'subscriptionID' => $subscriptionId,
                'amountToPay' => $amount,
                'planName' => $planName,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ];

            manageVcards($user);
            Mail::to($userEmail)->send(new SubscriptionPaymentSuccessMail($emailData));
            DB::commit();

            return view('sadmin.plans.payment.paymentSuccess');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            Flash::error($e->getMessage());
            return view('sadmin.plans.payment.paymentcancel');
        }
    }

    public function nfcOnBoard($orderId, $email, $nfc, $amount, $currency)
    {
        try {
            $payer = new Payer(
                $nfc['name'],
                $nfc['phone'],
                $email,
            );
            $item = Item::make()
                ->setTitle($nfc->nfcCard->name ??  $nfc['name'])
                ->setQuantity(1)
                ->setId(Auth::user()->id)
                ->setCategoryId($orderId)
                ->setUnitPrice((int)round($amount,2));

            $preference = Preference::make()
                ->setPayer($payer)
                ->addItem($item)
                ->setBackUrls(new BackUrls(
                    route('nfc.mercadopago.success'),
                ))
                ->setAutoReturn('approved');
            $response = MercadoPago::preference()->create($preference);
            $response['body']->items[0]->currency_id = strtoupper($currency);

            return $response;
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    public function nfcPaymentSuccess(Request $request)
    {
        $data = $request->all();
        config(['mercadopago.access_token' => getSelectedPaymentGateway('mp_access_token')]);
        config(['mercadopago.public_key' => getSelectedPaymentGateway('mp_public_key')]);
        $marcaPagoOrder = mercadoPago()->payment()->find($data['payment_id']);

        if ($marcaPagoOrder['httpCode'] != 200) {
            Log::error($marcaPagoOrder);
            return view('sadmin.plans.payment.nfcPaymentCancel');
        }

        try {
            DB::beginTransaction();
            $nfcOrderId = $marcaPagoOrder['body']->additional_info->items[0]->category_id;
            $userId = $marcaPagoOrder['body']->additional_info->items[0]->id;
            $nfcOrder = NfcOrders::find($nfcOrderId);
            $amount = $marcaPagoOrder['body']->transaction_amount;

            $nfcOrderTransaction = NfcOrderTransaction::create([
                'nfc_order_id' => $nfcOrderId,
                'type' => NfcOrders::MERCADO_PAGO,
                'transaction_id' => $data['payment_id'],
                'amount' => $amount,
                'user_id' => $userId,
                'status' => NfcOrders::SUCCESS,
            ]);

            $vcardName = Vcard::find($nfcOrder['vcard_id'])->name;
            $cardType = Nfc::find($nfcOrder['card_type'])->name;

            Mail::to(getSuperAdminSettingValue('email'))->send(new AdminNfcOrderMail($nfcOrder, $vcardName, $cardType));

            Flash::success(__('messages.nfc.order_placed_success'));

            DB::commit();
            return redirect(route('user.orders'));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            Flash::error($e->getMessage());
            return redirect(route('user.orders'));
        }
    }

        // Appointment on board
    public function appointmentOnBoard($userId, $vcard, $input)
    {
        $user = User::whereId($userId)->first();
        if ($user && $user->organisation_id) {
            $userId = $user->organisation_id;
        }

        config(['mercadopago.access_token' => getUserSettingValue('mp_access_token', $userId)]);
        config(['mercadopago.public_key' => getUserSettingValue('mp_public_key', $userId)]);
        try {
            $payer = new Payer(
                $input['name'],
                $input['phone'],
                $input['email'],
            );
            $item = Item::make()
                ->setTitle($input['vcard_name'])
                ->setQuantity(1)
                ->setId($vcard->id)
                ->setDescription($input['to_time'])
                ->setCategoryId($input['from_time'])
                ->setUnitPrice((int)round($input['amount'],2));

            $additionalInfo = [
                'name'      => $input['name'],
                'email'     => $input['email'],
                'phone'     => $input['phone'],
                'date'      => $input['date'],
                'from_time' => $input['from_time'],
                'to_time'   => $input['to_time'],
            ];

            $preference = Preference::make()
                ->setPayer($payer)
                ->addItem($item)
                ->setBackUrls(new BackUrls(
                    route('appointment.mercadopago.success'),
                ))

                ->setAdditionalInfo(
                    json_encode($additionalInfo)
                )
                ->setAutoReturn('approved')
                ->setExternalReference($input['date']);
            $response = MercadoPago::preference()->create($preference);
            $response['body']->items[0]->currency_id = strtoupper($input['currency_code']);
            session()->put('user_id', $userId);
            return $response;
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }

    // Appointment payment success
    public function userAppointmentPaymentSuccess(Request $request)
    {
        $userId = session()->get('user_id');
        $user = User::whereId($userId)->first();
        if ($user && $user->organisation_id) {
            $userId = $user->organisation_id;
        }

        if (!$userId) {
            throw new UnprocessableEntityHttpException(__('messages.placeholder.unable_to_process_payment'));
        }

        $input = $request->all();
        config(['mercadopago.access_token' => getUserSettingValue('mp_access_token', $userId)]);
        config(['mercadopago.public_key' => getUserSettingValue('mp_public_key', $userId)]);
        $marcaPagoOrder = mercadoPago()->payment()->find($input['payment_id']);
        $preference = mercadoPago()->preference()->find($input['preference_id']);

        if ($marcaPagoOrder['httpCode'] != 200 && $preference['httpCode'] != 200) {
            Log::error($marcaPagoOrder);
            throw new UnprocessableEntityHttpException(__('messages.placeholder.unable_to_process_payment'));
        }

        try {
            DB::beginTransaction();
            $amount = $marcaPagoOrder['body']->transaction_amount;
            $vcardId = $marcaPagoOrder['body']->additional_info->items[0]->id;
            $vcard = Vcard::with('tenant.user')->where('id', $vcardId)->first();
            $currencyId = Currency::whereCurrencyCode($marcaPagoOrder['body']->currency_id)->first()->id;
            $transactionId = $input['payment_id'];
            $tenantId = $vcard->tenant->id;
            $userId = $vcard->tenant->user->id;

            $input = json_decode($preference['body']->additional_info, true);


            $transactionDetails = [
                'vcard_id' => $vcard->id,
                'transaction_id' => $transactionId,
                'currency_id' => $currencyId,
                'amount' => $amount,
                'tenant_id' => $tenantId,
                'type' => Appointment::MERCADOPAGO,
                'status' => Transaction::SUCCESS,
                'meta' => json_encode($input),
            ];

            $appointmentTran = AppointmentTransaction::create($transactionDetails);

            $appointmentInput = [
                'name' => $input['name'],
                'email' => $input['email'],
                'date' => $input['date'],
                'phone' => $input['phone'],
                'from_time' => $input['from_time'],
                'to_time' => $input['to_time'],
                'vcard_id' => $vcardId,
                'appointment_tran_id' => $appointmentTran->id,
                'toName' => $vcard->fullName > 1 ? $vcard->fullName : $vcard->tenant->user->fullName,
                'vcard_name' => $vcard->name,
            ];

            /** @var AppointmentRepository $appointmentRepo */
            $appointmentRepo = App::make(AppointmentRepository::class);
            $vcardEmail = is_null($vcard->email) ? $vcard->tenant->user->email : $vcard->email;
            $appointmentRepo->appointmentStoreOrEmail($appointmentInput, $vcardEmail);

            Flash::success(__('messages.placeholder.payment_done'));
            App::setLocale(Session::get('languageChange_' . $vcard->url_alias));
            session()->forget('user_id');
            DB::commit();

            return redirect(route('vcard.show', [$vcard->url_alias, __('messages.placeholder.appointment_created')]));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    // Product Onboarding
    public function productOnBoard($userId, $product, $input, $currency)
    {
        try {
            $user = User::whereId($userId)->first();
            if ($user && $user->organisation_id) {
                $userId = $user->organisation_id;
            }
            $payer = new Payer(
                $input['name'],
                $input['phone'],
                $input['email'],
            );
            $item = Item::make()
                ->setTitle($product->name)
                ->setQuantity(1)
                ->setId($userId)
                ->setCategoryId($product->id)
                ->setUnitPrice((int)round($product->price,2));

            $additionalInfo = [
                'product_id' => $product->id,
                'name' => $input['name'],
                'email' => $input['email'],
                'phone' => $input['phone'],
                'address' => $input['address'],
                'payment_method' => $input['payment_method'],
            ];

            $preference = Preference::make()
                ->setPayer($payer)
                ->addItem($item)
                ->setBackUrls(new BackUrls(
                    route('product.mercadopago.success'),
                ))

                ->setAdditionalInfo(
                    json_encode($additionalInfo)
                )
                ->setAutoReturn('approved');
            $response = MercadoPago::preference()->create($preference);
            $response['body']->items[0]->currency_id = strtoupper($currency);
            session()->put('user_id', $userId);
            return $response;
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
    }


    // User Buy Product Success
    public function userProductBuySuccess(Request $request)
    {

        $userId = session()->get('user_id');
        $user = User::whereId($userId)->first();
        if ($user && $user->organisation_id) {
            $userId = $user->organisation_id;
        }
        if (!$userId) {
            throw new UnprocessableEntityHttpException(__('messages.placeholder.unable_to_process_payment'));
        }
        $input = $request->all();

        config(['mercadopago.access_token' => getUserSettingValue('mp_access_token', $userId)]);
        config(['mercadopago.public_key' => getUserSettingValue('mp_public_key', $userId)]);

        $marcaPagoOrder = mercadoPago()->payment()->find($input['payment_id']);
        $preference = mercadoPago()->preference()->find($input['preference_id']);
        if ($marcaPagoOrder['httpCode'] != 200 && $preference['httpCode'] != 200) {
            throw new UnprocessableEntityHttpException(__('messages.payment.payment_cancel'));
        }

        try {
            DB::beginTransaction();
            $amount = $marcaPagoOrder['body']->transaction_amount;
            $productId = $marcaPagoOrder['body']->additional_info->items[0]->category_id;
            $userId = $marcaPagoOrder['body']->additional_info->items[0]->id;
            $product =  Product::whereId($productId)->first();
            $currencyId = Currency::whereCurrencyCode($marcaPagoOrder['body']->currency_id)->first()->id;
            $transactionId = $input['payment_id'];

            $input = json_decode($preference['body']->additional_info, true);

            ProductTransaction::create([
                'product_id' => $input['product_id'],
                'name' => $input['name'],
                'email' => $input['email'],
                'phone' => $input['phone'],
                'address' => $input['address'],
                'currency_id' => $currencyId,
                'meta' => '',
                'type' =>  $input['payment_method'],
                'transaction_id' => $transactionId,
                'amount' => $amount,
            ]);

            $orderMailData = [
                'user_name' => $product->vcard->user->full_name,
                'customer_name' => $input['name'],
                'product_name' => $product->name,
                'product_price' => $product->price,
                'phone' => $input['phone'],
                'address' => $input['address'],
                'payment_type' => __('messages.paypal'),
                'order_date' => Carbon::now()->format('d M Y'),
            ];

            if (getUserSettingValue('product_order_send_mail_customer', $userId)) {
                Mail::to($input['email'])->send(new ProductOrderSendCustomer($orderMailData));
            }

            if (getUserSettingValue('product_order_send_mail_user', $userId)) {
                Mail::to($product->vcard->user->email)->send(new ProductOrderSendUser($orderMailData));
            }

            $vcard = $product->vcard;
            App::setLocale(Session::get('languageChange_' . $vcard->url_alias));
            session()->forget('user_id');
            DB::commit();

            return redirect(route('showProducts', [$vcard->id, $vcard->url_alias, __('messages.placeholder.product_purchase')]));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

}

