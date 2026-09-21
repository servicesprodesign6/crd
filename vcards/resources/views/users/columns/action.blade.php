<div>
    <div class="justify-content-center d-flex">
        @if (is_null($row->email_verified_at))
            <a href="javascript:void(0)" title="{{ __('messages.resend_verification_email') }}"
                class="btn px-1 text-warning fs-3 user-resend-verification-email" data-id="{{ $row->id }}">
                <i class="fa-solid fa-envelope"></i>
            </a>
        @endif
        <a title="{{ __('messages.user.change_password') }}" class="btn px-1 text-primary fs-3 user-change-password"
            data-id="{{ $row->id }}">
            <i class="fa-solid fa-key" style="color: #fcc030"></i>
        </a>
        <a href="{{ route('users.edit', $row->id) }}" title="{{ __('messages.common.edit') }}"
            class="btn px-1 text-primary fs-3 user-edit-btn" data-id="{{ $row->id }}">
            <i class="fa-solid fa-pen-to-square"></i>
        </a>
        <a href="javascript:void(0)" data-id="{{ $row->id }}" title="{{ __('messages.common.delete') }}"
            class="btn px-1 text-danger fs-3 user-delete-btn">
            <i class="fa-solid fa-trash-can"></i>
        </a>
    </div>
</div>
