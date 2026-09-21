<?php

namespace App\Http\Requests;

use App\Models\WhatsappStoreProduct;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateWhatsappStoreProductRequest extends FormRequest
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

        $rules['sort'] = [
            'nullable',
            'integer',
            Rule::unique('whatsapp_store_products')->where(function ($query) {
                return $query->where('whatsapp_store_id', $this->get('whatsapp_store_id'));
            })
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'currency_id.exists' => __('messages.flash.currency_required'),
            'category_id.exists' => __('messages.flash.category_required'),
        ];
    }
}
