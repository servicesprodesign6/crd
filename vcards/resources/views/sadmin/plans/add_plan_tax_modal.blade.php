<div class="modal fade common-modal-card" id="addPlanTaxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">{{ __('messages.nfc.add_tax') }}</h3>
                <button type="button" class="modal-close bg-transparent p-0 border-0" data-bs-dismiss="modal" aria-label="Close">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <g id="Menu / Close_MD"> <path id="Vector" d="M18 18L12 12M12 12L6 6M12 12L18 6M12 12L6 18" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path> </g> </g></svg>
                    </button>
            </div>
            {{ Form::open(['id'=>'addPlanTaxForm']) }}
            <div class="modal-body pt-0">
                <div class="alert alert-danger fs-4 text-white d-flex align-items-center  d-none" role="alert" id="PlanValidationErrorsBox">
                    <i class="fa-solid fa-face-frown me-5"></i>
                </div>
                <div class="mb-3">
                    {{ Form::label('plan_tax_name',__('messages.common.tax_name').':', ['class' => 'form-label required']) }}
                    {{ Form::text('plan_tax_name', null, ['class' => 'form-control', 'required','placeholder' => __('messages.common.tax_name'),'id' => 'planTaxName']) }}
                </div>
                <div class="mb-3">
                    {{ Form::label('plan_tax_value',__('messages.nfc.tax').':', ['class' => 'form-label required']) }}
                    {{ Form::number('plan_tax_value', null, ['class' => 'form-control', 'required','placeholder' => __('messages.nfc.tax'),'id' => 'planTax']) }}
                </div>
            <div>
                <h6>{{ __('messages.nfc.note_tax_will_be_percentage') }}</h6>
            </div>

            <div class="form-check form-switch mt-3">
                <input class="form-check-input" type="checkbox" id="planTaxStatus" name="plan_tax_enabled" value="1">
                <label class="form-check-label" for="planTaxStatus">{{ __('messages.nfc.enable_tax') }}</label>
            </div>

            </div>
            <div class="modal-footer justify-content-start pt-0">
                {{ Form::button(__('messages.common.save'), ['type'=>'submit','class' => 'btn btn-primary m-0','id'=>'btnSave']) }}
                <button type="button" class="btn discard-btn my-0 ms-3 me-0"
                        data-bs-dismiss="modal">{{ __('messages.common.discard') }}</button>
            </div>
            {{ Form::close() }}
        </div>
    </div>
</div>
