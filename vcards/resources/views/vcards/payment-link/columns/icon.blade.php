<div class="d-flex align-items-center">
    <a href="javascript:void(0)">
        <div class="image image-circle image-mini me-3">
            @if ($row->getFirstMediaUrl(\App\Models\VcardPaymentLink::IMAGE_COLLECTION))
                <img src="{{ $row->getFirstMediaUrl(\App\Models\VcardPaymentLink::IMAGE_COLLECTION) }}"
                    alt="payment link image" class="user-img">
            @else
                <img src="{{ asset('assets/images/default_service.png') }}" alt="default image" class="user-img">
            @endif
        </div>
    </a>
</div>
