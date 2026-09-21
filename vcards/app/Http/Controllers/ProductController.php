<?php

namespace App\Http\Controllers;

use Exception;
use Carbon\Carbon;
use App\Models\Product;
use App\Models\Currency;
use Laracasts\Flash\Flash;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Mail\ProductOrderSendUser;
use App\Models\ProductTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\ProductOrderSendCustomer;
use Illuminate\Support\Facades\Session;
use WandesCardoso\MercadoPago\DTO\Item;
use App\Http\Requests\ProductBuyRequest;
use App\Repositories\NfcOrderRepository;
use WandesCardoso\MercadoPago\DTO\Payer;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use WandesCardoso\MercadoPago\DTO\BackUrls;
use App\Repositories\VcardProductRepository;
use WandesCardoso\MercadoPago\DTO\Preference;
use App\Http\Controllers\MercadoPagoController;
use WandesCardoso\MercadoPago\Facades\MercadoPago;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductController extends AppBaseController
{
    /**
     * @var VcardProductRepository
     */
    private $vcardProductRepo;

    public function __construct(VcardProductRepository $vcardProductRepo)
    {
        $this->vcardProductRepo = $vcardProductRepo;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function store(CreateProductRequest $request): JsonResponse
    {
        $input = $request->all();

        $service = $this->vcardProductRepo->store($input);

        return $this->sendResponse($service, __('messages.flash.create_product'));
    }

    public function edit($id): JsonResponse
    {
        $product = Product::with('currency')->where('id', $id)->first();
        if ($product->currency) {
            $product['formatted_amount'] = getCurrencyAmount($product->price, $product->currency->currency_icon);
        }

        return $this->sendResponse($product, 'Product successfully retrieved.');
    }

    public function destroy($id): JsonResponse
    {
        $product = Product::where('id', $id)->first();
        $product->clearMediaCollection(Product::PRODUCT_PATH);
        $product->delete();

        return $this->sendSuccess('Product deleted successfully.');
    }

    public function update(UpdateProductRequest $request, $id): JsonResponse
    {
        $input = $request->all();

        $service = $this->vcardProductRepo->update($input, $id);

        return $this->sendResponse($service, __('messages.flash.update_product'));
    }

    public function generateAiDescription(Request $request): JsonResponse
    {
        $request->validate([
            'prompt' => 'required|string',
            'name' => 'nullable|string|max:255',
            'price' => 'nullable',
            'product_url' => 'nullable|string|max:255',
        ]);

        $userId = getLogInUser()->organisation_id ?: getLogInUserId();
        $aiProvider = getUserSettingValue('ai_provider', $userId);
        $openAiEnabled = getUserSettingValue('open_ai_enable', $userId);
        $geminiEnabled = getUserSettingValue('gemini_ai_enable', $userId);
        $prompt = $this->buildProductDescriptionPrompt($request);

        try {
            if (empty($aiProvider)) {
                if ($openAiEnabled) {
                    $aiProvider = 'open_ai';
                } elseif ($geminiEnabled) {
                    $aiProvider = 'gemini_ai';
                } else {
                    return $this->sendError(__('messages.vcard.ai_not_enabled_in_settings'));
                }
            }

            if ($aiProvider === 'open_ai' && !$openAiEnabled) {
                return $this->sendError(__('messages.vcard.open_ai_not_enabled_in_settings'));
            }

            if ($aiProvider === 'gemini_ai' && !$geminiEnabled) {
                return $this->sendError(__('messages.vcard.gemini_not_enabled_in_settings'));
            }

            $description = $aiProvider === 'open_ai'
                ? $this->generateProductDescriptionWithOpenAi($prompt, $userId)
                : $this->generateProductDescriptionWithGemini($prompt, $userId);

            $description = trim(preg_replace('/```(html)?/i', '', $description));
            $description = trim(strip_tags($description));
            $description = preg_replace('/\s+/', ' ', $description);

            if (empty($description)) {
                return response()->json([
                    'success' => false,
                    'message' => __('messages.vcard.generated_description_is_empty'),
                ], 500);
            }

            return response()->json([
                'success' => true,
                'description' => $description,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function buy(ProductBuyRequest $request)
    {
        $input = $request->all();

        $product = Product::with('currency', 'vcard.user')->whereId($input['product_id'])->first();
        $currency = isset($product->currency_id) ? $product->currency->currency_code : Currency::whereId(getUserSettingValue('currency_id', $product->vcard->user->id))->first()->currency_code;
        $userId = $product->vcard->tenant->user->id;
        try {
            App::setLocale(Session::get('languageChange_' . $product->vcard->url_alias));
            DB::beginTransaction();

            session()->put('product_vcard_data', [
                'vcard_id' => $product->vcard->id,
                'default_language' => $product->vcard->default_language,
                'url_alias' => $product->vcard->url_alias,
            ]);

            if ($input['payment_method'] == Product::STRIPE) {
                /** @var VcardProductRepository $repo */
                $repo = App::make(VcardProductRepository::class);

                $result = $repo->productBuySession($input, $product);
                DB::commit();

                return $this->sendResponse([
                    'payment_method' => $input['payment_method'],
                    $result,
                ], __('messages.placeholder.stripe_created'));
            }
            // PhonePe
            if ($input['payment_method'] == Product::PHONEPE) {

                if ($currency != "INR") {
                    return $this->sendError(__('messages.placeholder.this_currency_is_not_supported_phonepe'));
                }

                /** @var UserPhonepeController $phonepe */
                $phonepe = App::make(UserPhonepeController::class);
                $result = $phonepe->productBuy($input, $product);
                if (isset($result->original['status']) && $result->original['status'] != 200) {
                    return $this->sendError($result->original['message']);
                }
                DB::commit();

                return $this->sendResponse([
                    'payment_method' => $input['payment_method'],
                    $result,
                ], __('messages.placeholder.phonepe_created'));
            }

            // Paystack
            if ($input['payment_method'] == Product::PAYSTACK) {

                if (isset($currency) && !in_array(strtoupper($currency), getPayStackSupportedCurrencies())) {
                    return $this->sendError(__('messages.placeholder.this_currency_is_not_supported_paystack'));
                }

                /** @var UserPaystackController $paystack */
                $paystack = App::make(UserPaystackController::class);
                $result = $paystack->productBuy($input, $product);
                $targetUrl = $result->getTargetUrl();
                DB::commit();
                return $this->sendResponse(['payment_method' => $input['payment_method'], $targetUrl], __('messages.placeholder.paystack_created'));
            }

            // Flutterwave
            if ($input['payment_method'] == Product::FLUTTERWAVE) {
                $supportedCurrency = ['GBP', 'CAD', 'XAF', 'CLP', 'COP', 'EGP', 'EUR', 'GHS', 'GNF', 'KES', 'MWK', 'MAD', 'NGN', 'RWF', 'SLL', 'STD', 'ZAR', 'TZS', 'UGX', 'USD', 'XOF', 'ZMW'];
                if (isset($currency) && !in_array(strtoupper($currency), $supportedCurrency)) {
                    return $this->sendError(__('messages.placeholder.this_currency_is_not_supported_flutterwave'));
                }

                /** @var UserFlutterwaveController $flutterwave */
                $flutterwave = App::make(UserFlutterwaveController::class);
                $targetUrl = $flutterwave->productBuy($input, $product);
                DB::commit();
                return $this->sendResponse(['payment_method' => $input['payment_method'], $targetUrl], __('messages.placeholder.flutterwave_created'));
            }

            // Razor Pay
            if ($input['payment_method'] == Product::RAZORPAY) {

                $repo = App::make(VcardProductRepository::class);

                $result = $repo->userCreateRazorPaySession($input, $product, $currency);
                $result['payment_method'] = $input['payment_method'];
                $userId = $product->vcard->user->id;
                $product = Product::find($input['product_id']);
                Session::put('productId', $product);
                Session::put('product_vcard_data', [
                    'vcard_id' => $product->vcard->id,
                    'default_language' => $product->vcard->default_language,
                    'url_alias' => $product->vcard->url_alias,
                ]);
                DB::commit();

                return $this->sendResponse([
                    $result
                ], __('messages.nfc.razorpay_session_success'));
            }

            if ($input['payment_method'] == Product::MERCADOPAGO) {

                config(['mercadopago.access_token' => getUserSettingValue('mp_access_token', $userId)]);
                config(['mercadopago.public_key' => getUserSettingValue('mp_public_key', $userId)]);

                $response = App::make(MercadoPagoController::class)->productOnBoard($userId, $product, $input, $currency);

                DB::commit();

                return $this->sendResponse([
                    'payment_method' => $input['payment_method'],
                    $response['body'],
                ], __('messages.placeholder.mercadopago_created'));
            }
            //Payfast
            if ($input['payment_method'] == Product::PAYFAST) {
                $vcard = $product->vcard;

                if ($currency != "ZAR") {
                    return $this->sendError(__('messages.placeholder.currency_supported_payfast'));
                }

                $payFast = App::make(PayfastController::class);
                $payfastUrl = $payFast->productBuy($input, $product, $userId);

                if (!$payfastUrl) {
                    return $this->sendError(__('messages.placeholder.payment_not_complete'));
                }

                DB::commit();

                return $this->sendResponse([
                    'payment_method' => $input['payment_method'],
                    $payfastUrl,
                ], __('messages.placeholder.payfast_created'));
            }

            //Iyzico
            if ($input['payment_method'] == Product::IYZICO) {
                $vcard = $product->vcard;

                $supportedCurrency = ['USD', 'EUR', 'GBP', 'RUB', 'CHF', 'NOK'];

                if (isset($currency) && !in_array(strtoupper($currency), $supportedCurrency)) {
                    return $this->sendError(__('messages.placeholder.currency_supported_iyzico'));
                }

                $iyzico = App::make(IyzicoController::class);
                $iyzico->setUserId($userId);

                $iyzicoUrl = $iyzico->productBuy($input, $product);

                if (!filter_var($iyzicoUrl, FILTER_VALIDATE_URL)) {
                    return $this->sendError($iyzicoUrl);
                }

                DB::commit();

                return $this->sendResponse([
                    'payment_method' => $input['payment_method'],
                    $iyzicoUrl,
                ], __('messages.placeholder.iyzico_created'));
            }

            //manually
            if ($input['payment_method'] == Product::MANUALLY) {

                $product = Product::find($input['product_id']);

                ProductTransaction::create([
                    'name' => $input['name'],
                    'email' => $input['email'],
                    'phone' => $input['phone'],
                    'address' => $input['address'],
                    'type' => $input['payment_method'],
                    'product_id' => $input['product_id'],
                    'currency_id' => $product->currency_id,
                    'amount' => $product->price,
                    'status' => 1,
                ]);

                $orderMailData = [
                    'user_name' => $product->vcard->user->full_name,
                    'customer_name' => $input['name'],
                    'product_name' => $product->name,
                    'product_price' => $product->price,
                    'phone' => $input['phone'],
                    'address' => $input['address'],
                    'payment_type' => __('messages.manually'),
                    'order_date' => Carbon::now()->format('d M Y'),
                ];

                if (getUserSettingValue('product_order_send_mail_customer', $product->vcard->user->id)) {
                    Mail::to($input['email'])->send(new ProductOrderSendCustomer($orderMailData, $product->vcard->default_language, $product->vcard->url_alias));
                }

                if (getUserSettingValue('product_order_send_mail_user', $product->vcard->user->id)) {
                    Mail::to($product->vcard->user->email)->send(new ProductOrderSendUser($orderMailData, $product->vcard->default_language, $product->vcard->url_alias));
                }

                $result['payment_method'] = $input['payment_method'];
                DB::commit();

                return $this->sendResponse([
                    $result
                ], __('messages.flash.product_purchase_success'));
            }

            //PayPal
            if ($input['payment_method'] == Product::PAYPAL) {
                if (isset($currency) && !in_array(strtoupper($currency), getPayPalSupportedCurrencies())) {

                    return $this->sendError(__('messages.placeholder.this_currency_is_not_supported'));
                }

                /** @var PaypalController $payPalCont */
                $payPalCont = App::make(PaypalController::class);

                $result = $payPalCont->buyProductOnboard($input, $product);

                DB::commit();

                return $this->sendResponse([
                    'payment_method' => $input['payment_method'],
                    $result,
                ], __('messages.placeholder.paypal_created'));
            }

            // Sslcommerz
            if ($input['payment_method'] == Product::SSLCOMMERZ) {
                if ($currency != "BDT") {
                    return $this->sendError(__('messages.placeholder.currency_not_supported_sslcommerz'));
                }

                $sslcommerz = App::make(SslcommerzController::class);
                $result = $sslcommerz->productBuy($input, $product);

                DB::commit();

                return $this->sendResponse([
                    'payment_method' => $input['payment_method'],
                    $result,
                ], __('messages.placeholder.sslcommerz_created'));
            }

            // Cashfree
            if ($input['payment_method'] == Product::CASHFREE) {
                $vcard = $product->vcard;

                if ($currency != "INR") {
                    return $this->sendError(__('messages.placeholder.currency_supported_cashfree'));
                }

                $cashfree = App::make(CashfreeController::class);
                $result = $cashfree->productBuy($input, $product, $userId);

                if (!$result || $result['success'] === false) {
                    return $this->sendError($result['message'] ?? __('messages.placeholder.payment_not_complete'));
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => __('messages.placeholder.cashfree_created'),
                    'data' => [
                        'payment_method' => Product::CASHFREE,
                        'checkoutPageUrl' => route('appointment.cashfree.checkout', ['order_id' => $result['order_id'], 'paymentSessionId' => $result['payment_session_id'], 'returnUrl' => $result['return_url'], 'mode' => $result['mode']]),
                    ],
                ]);
            }
        }catch (Exception $e) {
            DB::rollBack();

            return $this->sendError($e->getMessage());
        }
    }

    private function getProductDescriptionSystemPrompt(): string
    {
        return 'You are a helpful assistant that writes concise, accurate, and natural product descriptions for a digital catalog.

        Return plain text only.
        Do not use HTML, markdown, headings, bullet points, quotation marks, emojis, or asterisks.
        Use only the information provided in the prompt.
        Do not invent product specifications, materials, dimensions, or features that were not provided.
        Keep the output suitable for direct use in a textarea field.
        Write 2 to 4 short sentences in a professional tone.';
    }

    private function buildProductDescriptionPrompt(Request $request): string
    {
        $context = [
            'Product name: ' . trim((string) $request->input('name')),
            'Price: ' . trim((string) $request->input('price')),
            'Product URL: ' . trim((string) $request->input('product_url')),
            'User prompt: ' . trim((string) $request->input('prompt')),
        ];

        $context = array_values(array_filter($context, function ($value) {
            return !preg_match('/:\s*$/', $value);
        }));

        return implode("\n", $context);
    }

    private function generateProductDescriptionWithOpenAi(string $prompt, int $userId): string
    {
        $userApiKey = getUserSettingValue('openai_api_key', $userId);
        $apiKey = !empty($userApiKey) ? $userApiKey : env('OPENAI_API_KEY');

        if (empty($apiKey)) {
            throw new Exception(__('messages.vcard.openai_api_key_not_set'));
        }

        $model = getUserSettingValue('open_ai_model', $userId);

        if (empty($model)) {
            throw new Exception(__('messages.vcard.open_ai_model_not_configured'));
        }

        $response = Http::timeout(30)
            ->retry(2, 2000)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => $this->getProductDescriptionSystemPrompt(),
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ]);

        if ($response->failed()) {
            $errorData = $response->json();
            throw new Exception($errorData['error']['message'] ?? __('messages.vcard.open_ai_api_error'));
        }

        return trim($response->json('choices.0.message.content', ''));
    }

    private function generateProductDescriptionWithGemini(string $prompt, int $userId): string
    {
        $userApiKey = getUserSettingValue('gemini_api_key', $userId);
        $apiKey = !empty($userApiKey) ? $userApiKey : env('GEMINI_API_KEY');

        if (empty($apiKey)) {
            throw new Exception(__('messages.vcard.gemini_api_key_not_set'));
        }

        $model = getUserSettingValue('gemini_model', $userId);

        if (empty($model)) {
            throw new Exception(__('messages.vcard.gemini_model_not_configured'));
        }

        $response = Http::timeout(30)
            ->retry(2, 2000)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post('https://generativelanguage.googleapis.com/v1/models/' . $model . ':generateContent?key=' . $apiKey, [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => $this->getProductDescriptionSystemPrompt() . "\n\n" . $prompt,
                            ],
                        ],
                    ],
                ],
            ]);

        if ($response->failed()) {
            $errorData = $response->json();
            throw new Exception($errorData['error']['message'] ?? __('messages.vcard.open_ai_api_error'));
        }

        return trim($response->json('candidates.0.content.parts.0.text', ''));
    }

    public function updateProductStatus($id, $status)
    {
        $product = ProductTransaction::find($id);

        if (!$product) {
            return redirect()->back()->with('error', 'Product not found.');
        }
        $updateData = [
            'status' => $status,
        ];

        if ($status == Product::REJECT) {
            $updateData['order_status'] = ProductTransaction::CANCELLED;
        }

        $product->update($updateData);

        return redirect()->back()->with('success',  __('messages.flash.product_status_change'));
    }

    public function destroyMedia($id)
    {
        $media = Media::findOrFail($id);
        if ($media->model_type != Product::class) {
            return $this->sendError('Unauthorized.');
        }
        $media->delete();
        return $this->sendSuccess(__('messages.flash.product_image_deleted'));
    }
}
