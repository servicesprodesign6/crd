<?php

namespace App\Http\Requests;

use App\Models\WhatsappStoreProduct;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWhatsappProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules= WhatsappStoreProduct::$rules;
        $rules['images'] = 'array|min:1';
        $product = $this->route('wpStoreProduct');
        $rules['sort'] = [
            'nullable',
            'integer',
            Rule::unique('whatsapp_store_products')->where(function ($query) use ($product) {
                $productId = $product->id;
                $product = WhatsappStoreProduct::find($productId);
                if ($product) {
                    return $query->where('whatsapp_store_id', $product->whatsapp_store_id);
                }
                return $query->where('whatsapp_store_id', 0);
            })->ignore($product->id)
        ];

        return $rules;
    }
}
