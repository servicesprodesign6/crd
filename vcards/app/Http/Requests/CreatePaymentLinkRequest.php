<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\VcardPaymentLink;

class CreatePaymentLinkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = VcardPaymentLink::$rules;

        $rules['icon'] = 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048';

        if ($this->display_type == 4) {
            $rules['image'] = 'required|image';
        } else {
            $rules['description'] = 'required|string|max:1000';
        }

        return $rules;
    }
}
