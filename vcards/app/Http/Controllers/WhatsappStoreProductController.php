<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\WpOrder;
use Laracasts\Flash\Flash;
use App\Models\WpOrderItem;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\WhatsappStore;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\WhatsappStoreProduct;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\WpProductBuyRequest;
use App\Mail\WhatsappStoreProductOrderSendUser;
use App\Http\Requests\UpdateWhatsappProductRequest;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Http\Requests\CreateWhatsappStoreProductRequest;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class WhatsappStoreProductController extends AppBaseController
{
    public function store(CreateWhatsappStoreProductRequest $request)
    {
        DB::beginTransaction();

        try {
            $input = $request->all();
            $access = WhatsappStore::findOrFail($input['whatsapp_store_id']);

            if (!$access->tenant_id == getLogInTenantId()) {
                return $this->sendError('Unauthorized.');
            }

            $product = WhatsappStoreProduct::create($input);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $product->newAddMedia($image)->toMediaCollection(
                        WhatsappStoreProduct::PRODUCT_IMAGES,
                        config('app.media_disc')
                    );
                }
            }

            DB::commit();

            return $this->sendSuccess(__('messages.flash.wp_product_create'));
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public function edit(WhatsappStoreProduct $wpStoreProduct)
    {
        $access = $wpStoreProduct->tenant_id == getLogInTenantId();
        if (!$access) {
            return $this->sendError('Unauthorized.');
        }
        $wpStoreProduct->load(['currency', 'category']);

        return $this->sendResponse($wpStoreProduct, 'Product retrieved successfully.');
    }


    public function update(WhatsappStoreProduct $wpStoreProduct, UpdateWhatsappProductRequest $request)
    {

        $access = $wpStoreProduct->tenant_id == getLogInTenantId();

        if (!$access) {
            return $this->sendError('Unauthorized.');
        }

        $input = $request->all();

        $wpStoreProduct->update($input);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $wpStoreProduct->newAddMedia($image)->toMediaCollection(
                    WhatsappStoreProduct::PRODUCT_IMAGES,
                    config('app.media_disc')
                );
            }
        }

        return $this->sendSuccess(__('messages.flash.wp_product_update'));
    }


    public function destroy($id)
    {
        $product = WhatsappStoreProduct::findOrFail($id);

        try {

            $isDelete = $product->ordersItems()->whereHas('wpOrder', function ($query) {
                $query->whereIn('status', [WpOrder::PENDING, WpOrder::DISPATCHED]);
            })->exists();

            if ($isDelete) {
                return $this->sendError('Product has orders.');
            }

            if ($product->tenant_id != getLogInTenantId()) {
                return $this->sendError('Unauthorized.');
            }

            $product->clearMediaCollection(WhatsappStoreProduct::PRODUCT_IMAGES);
            $product->delete();

            return $this->sendSuccess('Product deleted successfully.');
        } catch (\Exception $e) {

            throw new UnprocessableEntityHttpException($e->getMessage());
        }
    }

    public function destroyMedia($id)
    {
        $media = Media::findOrFail($id);

        if ($media->model_type != WhatsappStoreProduct::class) {
            return $this->sendError('Unauthorized.');
        }

        $media->delete();

        return $this->sendSuccess(__('messages.flash.product_image_delete'));
    }

    public function productBuy(WpProductBuyRequest $request)
    {

        if ($request->ajax()) {
            try {

                setLocalLang($request->language);

                DB::beginTransaction();
                $input = $request->except('products');

                $products = json_decode($request->input('products'), true);

                $alias = $request->url_alias;

                $whatsappStore = WhatsappStore::where('url_alias', $alias)->first();
                if (!$whatsappStore) {
                    abort(404);
                }

                $productNames = [];
                foreach ($products as $product) {
                    $storeProduct = WhatsappStoreProduct::find($product['id']);
                    if (!$storeProduct || $storeProduct->available_stock < $product['qty']) {
                        return $this->sendError( __('messages.flash.product_out_of_stock', ['name' => $storeProduct->name]));
                    }
                    $productNames[] = $storeProduct->name;
                }

                $orderID = Str::upper(Str::random(8));

                $input['order_id'] = $orderID;
                $input['discount'] = ($request->discount == 0) ? null : $request->discount;
                $input['discount_amount'] = ($request->discount_amount == 0) ? null : $request->discount_amount;

                $wpOrder = WpOrder::create($input);

                foreach ($products as $product) {
                    $wpOrderItem = WpOrderItem::create([
                        'wp_order_id' => $wpOrder->id,
                        'product_id' => $product['id'],
                        'price' => $product['price'],
                        'qty' => $product['qty'],
                        'total_price' => $product['total_price'],
                    ]);
                }
                DB::commit();
                $storeProduct = WhatsappStoreProduct::find($product['id']);
                if ($storeProduct) {
                    $storeProduct->available_stock -= $product['qty'];
                    $storeProduct->save();
                }
                $wpOrder->load(['products.product.currency']);

                $orderMailData = [
                    'user_name' => $storeProduct->whatsappStore->tenant->user->full_name,
                    'customer_name' => $input['name'],
                    'product_name' => implode(', ', $productNames),
                    'phone' => $input['phone'],
                    'address' => $input['address'],
                    'order_type' => $input['order_type'],
                    'order_date' => Carbon::now()->format('d M Y'),
                ];
                $tenant = $whatsappStore->tenant;
                $ownerUser = $tenant->user;
                $userId = $ownerUser->id;

                if (getUserSettingValue('product_order_send_mail_user', $userId)) {
                    if ($ownerUser->email) {
                        Mail::to($ownerUser->email)->send(new WhatsappStoreProductOrderSendUser($orderMailData,$whatsappStore->default_language,$whatsappStore->url_alias));
                    }
                }

                return $this->sendResponse($wpOrder, 'Order Created Successfully.');
            } catch (\Exception $e) {

                DB::rollBack();

                return $this->sendError($e->getMessage());
            }
        }
    }

    public function showOrder(WpOrder $wpOrder)
    {
        $access = $wpOrder->wpStore->tenant_id == getLogInTenantId();
        if (!$access) {
            Flash::error(__('Unauthorized.'));
            return redirect()->back();
        }
        $wpOrder->load(['products.product']);

        return $this->sendResponse($wpOrder, 'Order retrieved successfully.');
    }

    public function updateOrderStatus(Request $request, WpOrder $wpOrder)
    {
        $access = $wpOrder->wpStore->tenant_id == getLogInTenantId();
        if (!$access) {
            Flash::error(__('Unauthorized.'));
            return redirect()->back();
        }

        $status = $request->input('status');

        $wpOrder->update(['status' => $status]);
        if ($status == WpOrder::CANCELLED) {
            $wpOrderItem  = WpOrderItem::where('wp_order_id', $wpOrder->id)->first();
            $storeProduct = WhatsappStoreProduct::find($wpOrderItem->product_id);
            if ($storeProduct) {
                $storeProduct->available_stock += $wpOrderItem['qty'];
                $storeProduct->save();
            }
        }

        $wpOrder->load(['products.product.currency', 'wpStore:id,url_alias']);

        $baseUrl = config('app.url');


        return $this->sendResponse([$wpOrder, $baseUrl], 'Order status updated successfully.');
    }

    public function destroyOrder($id)
    {
        $whatsappStoreOrder = WpOrder::findOrFail($id);

        $whatsappStoreOrder->delete();

        return $this->sendSuccess(__('messages.flash.wp_order_delete'));
    }

    public function generateAiDescription(Request $request)
    {
        $request->validate([
            'images' => 'required|array|max:4',
            'images.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:4096',
            'product_name' => 'nullable|string|max:255',
        ]);

        $userId = getLogInUser()->organisation_id ?: getLogInUserId();
        $aiProvider = getUserSettingValue('ai_provider', $userId);
        $openAiEnabled = getUserSettingValue('open_ai_enable', $userId);
        $geminiEnabled = getUserSettingValue('gemini_ai_enable', $userId);
        $images = $request->file('images');
        $prompt = $this->getWpProductDescriptionPrompt($request->product_name);

        try {
            if (empty($aiProvider)) {
                return $this->sendError(__('messages.vcard.open_ai_not_enabled_in_settings'));
            }

            if ($aiProvider === 'open_ai' && !$openAiEnabled) {
                return $this->sendError(__('messages.vcard.open_ai_not_enabled_in_settings'));
            }

            if ($aiProvider === 'gemini_ai' && !$geminiEnabled) {
                return $this->sendError(__('messages.vcard.gemini_not_enabled_in_settings'));
            }

            if ($aiProvider === 'open_ai') {
                $result = $this->generateWpProductDescriptionWithOpenAi($images, $prompt, $userId);
            } else {
                $result = $this->generateWpProductDescriptionWithGemini($images, $prompt, $userId);
            }

            $description = trim(preg_replace('/```(html)?/i', '', $result['description']));

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

    private function getWpProductDescriptionPrompt(?string $productName): string
    {
        return 'Analyze the uploaded product image carefully and generate ONLY a clean professional HTML product description for an eCommerce website.

        Strict Instructions:

        - Do NOT generate product title.
        - Do NOT generate brand name unless explicitly visible in image text.
        - Do NOT generate category names.
        - Do NOT add introductory storytelling paragraphs.
        - Do NOT generate empty sections or placeholder content.
        - Do NOT use promotional luxury-style language.
        - Do NOT make assumptions about specifications, features, materials, or functionality unless clearly visible in the image.
        - Do NOT use words like "maybe", "possibly", "appears", "seems", or similar uncertain wording.

        Content Requirements:

        - Focus only on the visible product appearance.
        - Describe the design, color, texture, shape, and visible details.
        - Keep the content concise, clean, and natural.
        - Write in a professional eCommerce tone.
        - Keep description human-readable and SEO-friendly.
        - Generate short readable paragraphs and bullet points only when needed.

        HTML Rules:

        - Return ONLY valid HTML.
        - Use ONLY these tags:
        <p>, <ul>, <li>, <strong>, <br>

        Output Rules:

        - No markdown.
        - No code blocks.
        - No headings.
        - No titles.
        - No explanations.
        - No repeated content.
        - No fake specifications.
        - No conclusion paragraph.';
    }

    private function generateWpProductDescriptionWithOpenAi(array $images, string $prompt, int $userId): array
    {
        $userApiKey = getUserSettingValue('openai_api_key', $userId);
        $apiKey = !empty($userApiKey) ? $userApiKey : env('OPENAI_API_KEY');

        if (empty($apiKey)) {
            throw new \Exception(__('messages.vcard.openai_api_key_not_set'));
        }

        $model = getUserSettingValue('open_ai_model', $userId);

        if (empty($model)) {
            throw new \Exception(__('messages.vcard.open_ai_model_not_configured'));
        }

        $content = [
            [
                'type' => 'text',
                'text' => $prompt,
            ],
        ];

        foreach ($images as $image) {
            $content[] = [
                'type' => 'image_url',
                'image_url' => [
                    'url' => 'data:' . $image->getMimeType() . ';base64,' . base64_encode(file_get_contents($image->getRealPath())),
                ],
            ];
        }

        $response = Http::timeout(90)
            ->retry(2, 2000)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $content,
                    ],
                ],
                'max_tokens' => 500,
            ]);

        if ($response->failed()) {
            $errorData = $response->json();
            throw new \Exception($errorData['error']['message'] ?? __('messages.vcard.open_ai_api_error'));
        }

        return [
            'description' => trim($response->json('choices.0.message.content', '')),
            'model' => $model,
        ];
    }

    private function generateWpProductDescriptionWithGemini(array $images, string $prompt, int $userId): array
    {
        $userApiKey = getUserSettingValue('gemini_api_key', $userId);
        $apiKey = !empty($userApiKey) ? $userApiKey : env('GEMINI_API_KEY');

        if (empty($apiKey)) {
            throw new \Exception(__('messages.vcard.gemini_api_key_not_set'));
        }

        $model = getUserSettingValue('gemini_model', $userId);

        if (empty($model)) {
            throw new \Exception(__('messages.vcard.gemini_model_not_configured'));
        }

        $parts = [
            ['text' => $prompt],
        ];

        foreach ($images as $image) {
            $parts[] = [
                'inline_data' => [
                    'mime_type' => $image->getMimeType(),
                    'data' => base64_encode(file_get_contents($image->getRealPath())),
                ],
            ];
        }

        $response = Http::timeout(90)
            ->retry(2, 2000)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post('https://generativelanguage.googleapis.com/v1/models/' . $model . ':generateContent?key=' . $apiKey, [
                'contents' => [
                    [
                        'parts' => $parts,
                    ],
                ],
            ]);

        if ($response->failed()) {
            $errorData = $response->json();
            throw new \Exception($errorData['error']['message'] ?? __('messages.vcard.open_ai_api_error'));
        }

        return [
            'description' => trim($response->json('candidates.0.content.parts.0.text', '')),
            'model' => $model,
        ];
    }
}
