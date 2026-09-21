@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            <img src="{{ asset(getAppLogo()) }}" class="logo" style="height:auto!important;width:auto!important;object-fit:cover"
                alt="{{ getAppName() }}">
        @endcomponent
    @endslot

    {{-- Body --}}
    @if (!empty($content))
        {!! $content !!}
    @else
        <h2>{{ __('messages.mail.hello') }} <b>{{ $productTransaction->name }}</b></h2>
        <p> {{ __('messages.nfc.your_order_status_changed') }}</p>
        <p><b>{{ __('messages.vcard.product_name').':' }} </b>{{ $productTransaction->product->name }}</p>
        <p><b>{{ __('messages.subscription.amount').':' }} </b>{{ $productTransaction->currency->currency_icon }} {{ number_format($productTransaction->amount, 2) }}</p>
        <p><b>{{ __('messages.nfc.order_status').': ' }}</b>{{ __('messages.nfc.'.App\Models\ProductTransaction::STATUS_ARR[$status]) }}</p>
        <p><b>{{ __('messages.payment_type').': ' }}</b>{{ App\Models\Product::PAYMENT_METHOD[$productTransaction->type] }}</p>
        <p><b>{{ __('messages.user.address').': ' }}</b>{{ $productTransaction->address }}</p>
        <p>{{ __('messages.mail.thanks_regard') }}</p>
        <p>{{ getAppName() }}</p>
    @endif

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            <h6>© {{ date('Y') }} {{ getAppName() }}.</h6>
        @endcomponent
    @endslot
@endcomponent
