<div class="modal fade" id="productAiDescriptionModal" aria-modal="true" role="dialog" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-robot me-2"></i>
                    {{ __('messages.vcard.ai_description_generator') }}
                </h5>
                <button type="button" class="btn-close" id="productAiDescriptionCloseBtn" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="productAiDescriptionForm">
                    @csrf
                    <div class="mb-3">
                        <label for="productAiDescriptionTextarea" class="form-label required">
                            {{ __('messages.vcard.enter_ypur_prompt') . ':' }}
                        </label>
                        <textarea id="productAiDescriptionTextarea" name="prompt" class="form-control" rows="6"
                            placeholder="{{ __('messages.vcard.enter_your_prompt_for_ai_vcard_description') }}"></textarea>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn text-white ai-btn" id="generateProductAiDescriptionBtn">
                            <i class="fa-solid fa-wand-magic-sparkles me-1"></i>
                            {{ __('messages.vcard.generate_description') }}
                            <span class="spinner-border spinner-border-sm d-none" id="productAiDescriptionLoader"></span>
                        </button>
                        <button type="button" class="btn btn-secondary my-0 ms-2 me-0" id="productAiDescriptionDiscardBtn" data-bs-dismiss="modal">
                            {{ __('messages.common.discard') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
