@php
    $statusArr = App\Models\ProductTransaction::STATUS_ARR;
    if ($row->order_status == App\Models\ProductTransaction::PENDING) {
        unset($statusArr[App\Models\ProductTransaction::DELIVERED]);
    } elseif ($row->order_status == App\Models\ProductTransaction::DISPATCHED) {
        unset($statusArr[App\Models\ProductTransaction::PENDING]);
        unset($statusArr[App\Models\ProductTransaction::CANCELLED]);
    }

    if ($row->status == App\Models\Product::APPROVED) {
        unset($statusArr[App\Models\ProductTransaction::CANCELLED]);
    } else {
        unset($statusArr[App\Models\ProductTransaction::DISPATCHED]);
        unset($statusArr[App\Models\ProductTransaction::DELIVERED]);
    }
@endphp

@if ($row->order_status == App\Models\ProductTransaction::PENDING || $row->order_status == App\Models\ProductTransaction::DISPATCHED)
    <div wire:key="order-status-{{ $row->id }}-{{ $row->order_status }}">
        {{ Form::select('order_status', getTranslatedData($statusArr)->toArray(), $row->order_status, ['class' => 'form-control form-select product-transaction-order-status', 'data-id' => $row->id]) }}
    </div>
@elseif($row->order_status == App\Models\ProductTransaction::DELIVERED)
    <span class="badge bg-light-success">{{ __('messages.delivered') }}</span>
@elseif($row->order_status == App\Models\ProductTransaction::CANCELLED)
    <span class="badge bg-light-danger">{{ __('messages.cancelled') }}</span>
@endif
