<div class="modal fade" id="showPaymentLinkModal" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">{{ __('messages.vcard.payment_link_details') }}</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-5">
                        <div class="mb-3" io-image-input="true">
                            <label for="showPaymentLinkIcon"
                                class="form-label fs-6 fw-bolder text-gray-700">{{ __('messages.common.icon') }}:</label>
                            <div class="d-block">
                                <div class="image-picker">
                                    <div class="image previewImage" id="showPaymentLinkIcon"
                                        style="background-image: url('{{ asset('assets/images/default_service.png') }}')">
                                    </div>
                                    <div class="image-upload file-validation d-none"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-7">
                        <div class="mb-7">
                            <label class="form-label fs-6 fw-bolder text-gray-700">
                                {{ __('messages.vcard.label') }}:
                            </label>
                            <p id="showLabel" class="text-gray-600 fw-bold mb-0"></p>
                        </div>
                        <label class="form-label fs-6 fw-bolder text-gray-700">
                            {{ __('messages.vcard.display_type') }}:
                        </label>
                        <p id="showDisplayType" class="text-gray-600 fw-bold mb-0 text-capitalize"></p>
                    </div>
                    <div class="col-sm-12 mb-5" id="description-field">
                        <label class="form-label fs-6 fw-bolder text-gray-700">
                            {{ __('messages.common.description') . ':' }}
                        </label>
                        <p id="showDescription" class="text-gray-600 fw-bold mb-0"></p>
                    </div>
                    <div class="col-sm-12 mb-5 d-none" id="image-field">
                        <label class="form-label fs-6 fw-bolder text-gray-700">
                            {{ __('messages.vcard.image') }}:
                        </label>
                        <div class="mt-3">
                            <img id="showPaymentLinkContentImage" src="" alt="Payment Link Content"
                                class="img-fluid" style="max-width: 100px; max-height: 100px;" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
