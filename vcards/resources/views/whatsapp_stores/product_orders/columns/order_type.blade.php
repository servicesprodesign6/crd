@if ($row->order_type == \App\Models\WpOrder::TAKE_AWAY)
    <span class="badge bg-light-info" style="width: 100px">{{ __('messages.whatsapp_stores.take_away') }}</span>
@elseif ($row->order_type == \App\Models\WpOrder::DELIVERY)
    <span class="badge bg-light-success" style="width: 100px">{{ __('messages.whatsapp_stores.delivery') }}</span>
@else
    <span class="badge bg-light-primary" style="width: 100px">{{ __('messages.common.n/a') }}</span>
@endif