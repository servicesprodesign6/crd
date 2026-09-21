<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateProductRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $rules = Product::$rules;

        // Add manual unique validation for sort field
        $rules['sort'] = [
            'nullable',
            'integer',
            Rule::unique('products')->where(function ($query) {
                return $query->where('vcard_id', $this->get('vcard_id'));
            })
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.string' => __('messages.vcard.product_name_string'),
            'currency_id.required_with' => __('messages.vcard.currency_id_required_with'),
            'sort.unique' => __('messages.flash.sort_taken'),
        ];
    }
}
