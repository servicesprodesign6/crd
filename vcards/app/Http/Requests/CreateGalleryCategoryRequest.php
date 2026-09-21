<?php

namespace App\Http\Requests;

use App\Models\GalleryCategory;
use Illuminate\Foundation\Http\FormRequest;

class CreateGalleryCategoryRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = GalleryCategory::$rules;

        $rules['name'] = 'required|string|max:255|unique:gallery_categories,name,NULL,id,vcard_id,' . $this->vcard_id;

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'name' => __('messages.whatsapp_stores.category'),
        ];
    }
}
