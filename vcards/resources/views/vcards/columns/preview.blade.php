<?php
$userId = Auth::id();
$user = getLogInUser();
if ($user && $user->organisation_id) {
    $userId = $user->organisation_id;
}
$customDomain = App\Models\CustomDomain::where('user_id', $userId)
                ->where('is_approved', App\Models\CustomDomain::APPROVED)
                ->where('is_active', 1)
                ->latest()
                ->first();
$isCustomDomainUse = $customDomain ? $customDomain->is_use_vcard : false;
$vcardUrl = $isCustomDomainUse ? "https://{$customDomain->domain}/{$row->url_alias}" : route('vcard.show', ['alias' => $row->url_alias]);
?>
@if ($row->status == 1)
    <a href="{{ $vcardUrl }}" id="vcardUrl{{ $row->id }}" target="_blank"
        class="text-decoration-none fs-6 preview-url text-primary">{{ $vcardUrl }}</a>
    <button class="btn px-2 text-primary fs-2 user-edit-btn copy-clipboard" data-id="{{ $row->id }}"
        title="{{ 'copy' }}">
        <i class="fa-regular fa-copy fs-2"></i>
    </button>
@else
    <span id="vcardUrl{{ $row->id }}" target="_blank">{{ $vcardUrl }}</span>
@endif
