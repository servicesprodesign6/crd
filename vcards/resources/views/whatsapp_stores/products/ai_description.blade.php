<div class="modal fade" id="wpStoreAiDescriptionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-robot me-2"></i>
                    {{ __('messages.vcard.ai_description_generator') }}
                </h5>
                <button type="button" class="btn-close" id="wpStoreAiDescriptionCloseBtn" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="wpStoreAiDescriptionForm" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label required">
                            {{ __('messages.whatsapp_stores.product_images') . ':' }}
                        </label>
                        <input type="file" name="images[]" id="wpStoreAiDescriptionImages" class="form-control"
                            accept="image/*" multiple required>
                        <div class="form-text">
                            {{ __('messages.allowed_file_types') }} {{ __('messages.maximum_4_images') }}
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn text-white ai-btn" id="wpStoreAiGenerateDescriptionBtn">
                            <i class="fa-solid fa-wand-magic-sparkles me-1"></i>
                            {{ __('messages.vcard.generate_description') }}
                            <span class="spinner-border spinner-border-sm d-none" id="wpStoreAiDescriptionLoader"></span>
                        </button>
                        <button type="button" class="btn btn-secondary my-0 ms-2 me-0" id="wpStoreAiDescriptionDiscardBtn" data-bs-dismiss="modal">
                            {{ __('messages.common.discard') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
