<div>
    @php
        $slider_view = false;
        $slider_view = App\Models\Vcard::where('id', $this->vcardId)->value('services_slider_view');

        $enquiry_btn = false;
        $enquiry_btn = App\Models\Vcard::where('id', $this->vcardId)->value('show_service_enquiry_btn');
    @endphp
    <div class="d-flex align-items-center flex-column flex-sm-row add-btn-whatsapp ps-4">
        <div class="d-flex align-items-center mb-2 mb-sm-0">
            <div class="form-check form-switch m-0  d-flex align-items-center">
                <label class="form-check-label" for="vcard-services-enquiry-button" style="margin-right: 50px">{{__('messages.vcard.display_service_enquiry_button')}}</label>
                <input data-id="{{ $this->vcardId }}" id="vcard-services-enquiry-button" name="show_service_enquiry_btn" class="form-check-input" type="checkbox" role="switch" {{ $enquiry_btn ? 'checked' : '' }}>
            </div>
            <div class="form-check form-switch m-0 pe-5 d-flex align-items-center">
                <label class="form-check-label" for="vcard-services-slider-view" style="margin-right: 50px">{{__('messages.vcard.view_one_by_one')}}</label>
                <input data-id="{{ $this->vcardId }}" id="vcard-services-slider-view" name="services_slider_view" class="form-check-input" type="checkbox" role="switch" {{ $slider_view ? 'checked' : '' }}>
            </div>
        </div>
        <a type="button" class="btn btn-primary ms-auto" id="addServiceBtn">{{__('messages.vcard.add_service')}}</a>
    </div>
</div>
