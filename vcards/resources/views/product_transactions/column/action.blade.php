<div class="justify-content-center d-flex">
    <a href="{{ route('product-order.pdf', $row->id) }}" title="{{ __('messages.common.download') }}"
       class="btn px-1 text-primary fs-3">
        <i class="fa-solid fa-download"></i>
    </a>
    <a title="{{ __('messages.common.view') }}" href="{{ route('product-orders.show', $row->id) }}" class="btn px-1 text-info fs-3">
        <i class="fa-solid fa-eye"></i>
    </a>
</div>
