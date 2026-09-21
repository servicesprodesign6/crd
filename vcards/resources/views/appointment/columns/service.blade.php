<div>
    @if (!empty($row->service))
        <span>{{ $row->service->name }}</span>
    @else
        <span>{{ __('messages.common.n/a') }}</span>
    @endif
</div>
