<div>
    @php
        $enquiry_btn = false;
        $enquiry_btn = App\Models\Vcard::where('id', $this->vcardId)->value('show_product_enquiry_btn');
    @endphp

    <div class="d-flex align-items-center">
        <div class="form-check form-switch m-0 pe-5 d-flex align-items-center">
            <label class="form-check-label" for="vcard-product-enquiry-button" style="margin-right: 50px">{{__('messages.vcard.display_product_enquiry_button')}}</label>
            <input data-id="{{ $this->vcardId }}" id="vcard-product-enquiry-button" name="show_product_enquiry_btn" class="form-check-input" type="checkbox" role="switch" {{ $enquiry_btn ? 'checked' : '' }}>
        </div>

        <a type="button" class="btn btn-primary ms-auto" id="addProductBtn">{{ __('messages.vcard.add_product') }}</a>
    </div>
</div>
