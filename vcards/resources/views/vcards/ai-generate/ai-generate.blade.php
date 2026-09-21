<div class="modal fade" id="aiVcardModal" tabindex="-1" aria-modal="true" aria-labelledby="aiVcardModal" role="dialog">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa-solid fa-robot me-2 mb-1"></i>
                    {{ __('messages.vcard.ai_vcard') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="aiCardForm" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">
                            {{ __('messages.vcard.import_company_card') . ':' }}
                        </label>
                        <span data-bs-toggle="tooltip" data-placement="top"
                            data-bs-original-title="{{ __('messages.vcard.upload_business_card_tip') }}">
                            <i class="fas fa-question-circle ml-1 general-question-mark"></i>
                        </span>
                        <input type="file" name="card_image" class="form-control" accept="image/*" required>
                        <div class="mt-3">
                            <a href="{{ asset('images/sample-business-card.png') }}"
                            class="text-primary text-decoration-none"
                            download>
                                <i class="fa-solid fa-download me-1"></i>
                                {{ __('messages.vcard.download_sample_file') }}
                            </a>
                        </div>
                        <small class="text-muted d-block mt-1">
                            {{ __('messages.vcard.upload_business_card_info') }}
                        </small>
                    </div>

                    <div class="text-end">
                        <button class="btn text-white ai-btn" id="aiGenerateBtn">
                            <i class="fa-solid fa-wand-magic-sparkles me-1"></i>
                            {{ __('messages.vcard.generate_vcard') }}
                            <span class="spinner-border spinner-border-sm d-none" id="aiLoader"></span>
                        </button>
                        {{ Form::button(__('messages.common.discard'),['class' => 'btn btn-secondary my-0 ms-2 me-0','data-bs-dismiss' => 'modal', 'id' => 'aiDiscardBtn']) }}
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
