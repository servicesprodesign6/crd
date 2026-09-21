<div class="justify-content-center d-flex">
    <a href="{{ route('wp.product.order.pdf', $row->id) }}" title="{{ __('messages.common.download') }}"
       class="btn px-1 text-primary fs-3">
        <i class="fa-solid fa-download"></i>
    </a>
    <a title="{{ __('messages.common.view') }}" class="btn px-1 text-info wp-product-transaction-order-view-btn fs-3" type="button" data-id="{{ $row->id }}">
        <i class="fa-solid fa-eye"></i>
    </a>

</div>
