<div class="modal fade" id="addPaymentLinkModal" tabindex="-1" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">{{ __('messages.vcard.new_payment_link') }}</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {!! Form::open(['id' => 'addPaymentLinkForm', 'files' => 'true', 'data-default-icon' => asset('assets/images/default_service.png')]) !!}
                @csrf
                <div class="row">
                    <div class="col-sm-3 mb-5">
                        <div class="mb-3" io-image-input="true">
                            <label for="paymentLinkIconPreview"
                                class="form-label required">{{ __('messages.common.icon') }}:</label>
                            <div class="d-block">
                                <div class="image-picker">
                                    <div class="image previewImage" id="paymentLinkIconPreview"
                                        style="background-image: url('{{ asset('assets/images/default_service.png') }}')">
                                    </div>
                                    <span class="picker-edit rounded-circle text-gray-500 fs-small"
                                        data-bs-toggle="tooltip" data-placement="top"
                                        data-bs-original-title="{{ __('messages.tooltip.image') }}">
                                        <label>
                                            <i class="fa-solid fa-pen" id="profileImageIcon"></i>
                                            <input type="file" id="paymentLinkImg" name="icon"
                                                class="image-upload file-validation d-none crop-image-input" accept="image/*"
                                                data-preview-id="paymentLinkIconPreview" />
                                        </label>
                                    </span>
                                </div>
                                <div class="form-text">{{ __('messages.allowed_file_types') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-9 mb-5">
                        {{ Form::hidden('vcard_id', $vcard->id) }}
                        <div class="mb-7">
                            {{ Form::label('label', __('messages.vcard.label') . ':', ['class' => 'form-label required fs-6 fw-bolder text-gray-700 mb-3']) }}
                            {{ Form::text('label', null, ['class' => 'form-control', 'required', 'placeholder' => __('messages.vcard.label')]) }}
                        </div>
                        {{ Form::label('display_type', __('messages.vcard.display_type') . ':', ['class' => 'form-label required fs-6 fw-bolder text-gray-700 mb-3']) }}
                        {{ Form::select('display_type', App\Models\VcardPaymentLink::paymentLinks(), null, ['class' => 'form-select', 'data-control' => 'select2', 'id' => 'payment_link_display_type_create', 'required']) }}
                    </div>
                    <div class="col-sm-12 mb-5" id="content-container"
                            data-upi-tooltip="*UPI Link Format: upi://pay?pa=VPA@bank&am=Amount&cu=INR .
                            Parameters:
                            pa = Payee UPI ID (e.g. 9876543210@ybl),
                            am = Amount (e.g. 100 or 1.50),
                            cu = Currency (INR).
                            *Example: upi://pay?pa=9876543210@ybl&am=100&cu=INR .
                            *Tip: Remove 'am=Amount' if you want the customer to enter the amount manually.">
                        {{ Form::label('description', __('messages.common.description') . ':', ['class' => 'form-label required fs-6 fw-bolder text-gray-700 mb-3']) }}
                        {{ Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => __('messages.common.description'), 'rows' => '5', 'required']) }}
                    </div>
                    <div class="col-sm-12 mb-3 d-none" id="upi-note-container-create">
                        <div class="small mb-0">
                            <strong>{{ __('messages.vcard.note') }}</strong><br>
                            {{ __('messages.vcard.upi_link_format') }}
                            upi://pay?pa=VPA@bank&am={{ __('messages.subscription.amount') }}&cu={{ __('messages.plan.currency') }}<br>

                            <span>{{ __('messages.vcard.pay_button_mobile_only') }}</span><br><br>

                            <strong>{{ __('messages.vcard.example') }}</strong><br>
                            upi://pay?pa=9876543210@ybl&am=100&cu=INR<br><br>
                        </div>
                    </div>
                    <div class="modal-footer pt-0">
                        {{ Form::button(__('messages.common.save'), ['class' => 'btn btn-primary m-0', 'id' => 'paymentLinkSave', 'type' => 'submit']) }}
                        {{ Form::button(__('messages.common.discard'), ['class' => 'btn btn-secondary my-0 ms-5 me-0', 'data-bs-dismiss' => 'modal']) }}
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
