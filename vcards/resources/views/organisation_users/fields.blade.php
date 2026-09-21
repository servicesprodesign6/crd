<div class="row">
    <div class="col-lg-6">
        <div class="mb-5">
            {{ Form::label('first_name', __('messages.user.first_name') . ':', ['class' => 'form-label required']) }}
            {{ Form::text('first_name', isset($user) ? $user->first_name : null, ['class' => 'form-control', 'placeholder' => __('messages.form.first_name'), 'required', 'id' => 'userFirstName']) }}
        </div>
    </div>
    <div class="col-lg-6">
        <div class="mb-5">
            {{ Form::label('last_name', __('messages.user.last_name') . ':', ['class' => 'form-label required']) }}
            {{ Form::text('last_name', isset($user) ? $user->last_name : null, ['class' => 'form-control', 'placeholder' => __('messages.form.last_name'), 'required', 'id' => 'userLastName']) }}
        </div>
    </div>
    <div class="col-lg-6 mb-5">
        {{ Form::label('email', __('messages.user.email') . ':', ['class' => 'form-label required']) }}
        {{ Form::email('email', isset($user) ? $user->email : null, ['class' => 'form-control check-email', 'placeholder' => __('messages.form.mail'), 'required']) }}
        <input type="hidden" id="originalEmail" value="{{ isset($user) ? $user->email : '' }}">
        <span id="email-error-msg" class="text-danger fw-400 fs-small mt-2"></span>
    </div>
    <div class="col-lg-6">
        {{ Form::label('contact', __('messages.user.contact_no') . ':', ['class' => 'form-label']) }}
        {{ Form::tel('contact', isset($user) && $user->contact ? '+' . $user->region_code . $user->contact : null, ['class' => 'form-control', 'placeholder' => __('messages.form.contact'), 'onkeyup' => 'if (/\\D/g.test(this.value)) this.value = this.value.replace(/\\D/g,"")', 'id' => 'phoneNumber']) }}
        {{ Form::hidden('region_code', isset($user) ? $user->region_code : null, ['id' => 'prefix_code']) }}
        <p id="valid-msg" class="text-success d-none fw-400 fs-small mt-2">{{ __('messages.placeholder.valid_number') }}</p>
        <p id="error-msg" class="text-danger d-none fw-400 fs-small mt-2">{{ __('messages.placeholder.invalid_number') }}</p>
    </div>
    @if (! isset($user))
        <div class="col-lg-6 mb-5">
            <label class="form-label required">{{ __('messages.user.password') . ':' }}</label>
            <div class="mb-3 position-relative">
                <input class="form-control" id="password" type="password" name="password"
                    placeholder="{{ __('messages.form.password') }}" autocomplete="off" required aria-label="Password"
                    data-toggle="password" />
                <span class="position-absolute d-flex align-items-center top-0 bottom-0 end-0 me-4 input-icon input-password-hide cursor-pointer text-gray-600">
                    <i class="bi bi-eye-slash-fill"></i>
                </span>
            </div>
        </div>

        <div class="col-lg-6 mb-5">
            <label class="form-label required">{{ __('messages.user.confirm_password') . ':' }}</label>
            <div class="mb-3 position-relative">
                <input class="form-control" id="cPassword" type="password"
                    placeholder="{{ __('messages.form.c_password') }}" name="password_confirmation" autocomplete="off"
                    required aria-label="Password" data-toggle="password" />
                <span class="position-absolute d-flex align-items-center top-0 bottom-0 end-0 me-4 input-icon input-password-hide cursor-pointer text-gray-600">
                    <i class="bi bi-eye-slash-fill"></i>
                </span>
            </div>
        </div>
    @endif
    <div class="col-lg-6">
        <div class="mb-5">
            <div class="mb-3" io-image-input="true">
                <label for="exampleInputImage" class="form-label">{{ __('auth.app.profile') . ':' }}</label>
                <div class="d-block">
                    <div class="image-picker">
                        <div class="image previewImage" id="exampleInputImage"
                            style="background-image: url('{{ ! empty($user->profile_image ?? null) ? $user->profile_image : asset('web/media/avatars/user.png') }}')">
                        </div>
                        <span class="picker-edit rounded-circle text-gray-500 fs-small">
                            <label>
                                <i class="fa-solid fa-pen" id="profileImageIcon"></i>
                                <input type="file" id="profile_image" name="profile"
                                    class="image-upload file-validation d-none crop-image-input" accept="image/*"
                                    data-crop-width="100" data-crop-height="100" data-preview-id="userProfileImagePreview" />
                            </label>
                        </span>
                    </div>
                    <div class="form-text">{{ __('messages.allowed_file_types') }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="row">
            <div class="col-md-6 mb-5">
                <label class="form-label d-block">{{ __('messages.organization.vcard_permissions') . ':' }}</label>
                <div class="form-check mb-3">
                    {{ Form::checkbox('can_create_vcard', 1, isset($user) ? $user->canCreateVcards() : false, ['class' => 'form-check-input', 'id' => 'canCreateVcard']) }}
                    {{ Form::label('canCreateVcard', __('messages.organization.allow_create_vcard') . ':', ['class' => 'form-check-label']) }}
                    <div class="mt-2 {{ (isset($user) && $user->canCreateVcards()) ? '' : 'd-none' }}" id="noOfVcardInput">
                        {{ Form::number('no_of_vcards', isset($user) && $user->organisationUserPermission ? $user->organisationUserPermission->no_of_vcards : null, ['class' => 'form-control', 'placeholder' => __('messages.plan.no_of_vcards'), 'max' => $remainingLimits['remainingVcardLimit']]) }}
                        <small class="text-muted">{{ __('messages.vcard.available') . ':' }} {{ $remainingLimits['remainingVcardLimit'] }}</small>
                    </div>
                </div>
                <div class="form-check">
                    {{ Form::checkbox('can_edit_vcard', 1, isset($user) ? $user->canEditVcards() : false, ['class' => 'form-check-input', 'id' => 'canEditVcard']) }}
                    {{ Form::label('canEditVcard', __('messages.organization.allow_edit_vcard') . ':', ['class' => 'form-check-label']) }}
                </div>
            </div>
            @if (getPlanFeature(getCurrentSubscription()->plan)['whatsapp_store'])
                <div class="col-md-6 mb-5">
                    <label class="form-label d-block">{{ __('messages.organization.whatsapp_store_permissions') . ':' }}</label>
                    <div class="form-check mb-3">
                        {{ Form::checkbox('can_create_whatsapp_store', 1, isset($user) ? $user->canCreateWhatsappStore() : false, ['class' => 'form-check-input', 'id' => 'canCreateWhatsappStore']) }}
                        {{ Form::label('canCreateWhatsappStore', __('messages.organization.allow_create_whatsapp_store') . ':', ['class' => 'form-check-label']) }}
                        <div class="mt-2 {{ (isset($user) && $user->canCreateWhatsappStore()) ? '' : 'd-none' }}" id="noOfWhatsappStoreInput">
                            {{ Form::number('no_of_whatsapp_store', isset($user) && $user->organisationUserPermission ? $user->organisationUserPermission->no_of_whatsapp_store : null, ['class' => 'form-control', 'placeholder' => __('messages.organization.no_of_whatsapp_store'), 'max' => $remainingLimits['remainingWhatsappStoreLimit']]) }}
                            <small class="text-muted">{{ __('messages.vcard.available') . ':' }} {{ $remainingLimits['remainingWhatsappStoreLimit'] }}</small>
                        </div>
                    </div>
                    <div class="form-check">
                        {{ Form::checkbox('can_edit_whatsapp_store', 1, isset($user) ? $user->canEditWhatsappStore() : false, ['class' => 'form-check-input', 'id' => 'canEditWhatsappStore']) }}
                        {{ Form::label('canEditWhatsappStore', __('messages.organization.allow_edit_whatsapp_store') . ':', ['class' => 'form-check-label']) }}
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div>
        {{ Form::submit(__('messages.common.save'), ['class' => 'btn btn-primary me-3']) }}
        <a href="{{ route('organisation.users.index') }}" class="btn btn-secondary">{{ __('messages.common.discard') }}</a>
    </div>
</div>
