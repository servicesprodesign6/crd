<div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0">

            <div class="modal-body p-0 border-0">
                <div class="row overflow-auto">
                    <div class="col-12 col-lg-8 mb-4 mb-lg-0">
                        <div class="table-details overflow-auto h-100">
                            <table class="w-100">
                                <thead>
                                    <tr>
                                        <th class="fs-18 fw-5 text-white">{{ __('messages.whatsapp_stores.products') }}</th>
                                        <th class="fs-18 fw-5 text-white text-center text-nowrap"> {{ __('messages.whatsapp_stores_templates.quantity') }}</th>
                                        <th class="fs-18 fw-5 text-white text-center text-nowrap">  {{ __('messages.whatsapp_stores_templates.total_price') }}</th>
                                        <th class="fs-18 fw-5 text-white text-end"></th>
                                    </tr>
                                </thead>
                                <tbody id="cartItemsCloth">

                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-12 col-lg-4">
                        <div class="table-details d-flex flex-column h-100 justify-content-between">
                            <h2 class="fs-20 fw-5 text-white mb-0">{{ __('messages.whatsapp_stores_templates.order_summary') }}</h2>
                            <div id="totalDetails" class="flex-grow-1">

                            </div>
                            <div class="mb-15">
                                <p class="fs-18 text-black text-end fw-5 mb-0 py-1 total pe-3">{{ __('messages.nfc.total') }} : <span id="subTotal">0</span> </p>
                                @if (!empty($whatsappStore->discount))
                                    <input type="hidden" id="storeDiscount" value="{{ $whatsappStore->discount }}">
                                    <p class="fs-18 text-black text-end fw-5 mb-0 pe-3 total">- {{ __('messages.whatsapp_stores.discount') }} : <span id="discountPercentage">0</span> </p>
                                @endif

                                <div id="deliveryChargeSection" class="d-none">
                                    <input type="hidden" id="deliveryChargeAmount" value="{{ $whatsappStore->delivery_charge }}">
                                    <p class="fs-18 text-black text-end fw-5 mb-0 pe-3 total">+ {{ __('messages.whatsapp_stores.delivery_charge') }} : <span id="deliveryChargeText">0</span> </p>
                                </div>

                                <p class="fs-20 text-black text-end fw-5 mb-0 py-3 pe-3 total" >{{ __('messages.whatsapp_stores.grand_total') }} : <span id="grandTotal">0</span> </p>

                                @if ($whatsappStore->take_away || $whatsappStore->delivery)
                                    <h3 class="fs-18 fw-5 text-black mb-3 px-2">{{ __('messages.whatsapp_stores.fulfillment_method') }}</h3>
                                    <div class="d-flex flex-column flex-md-row gap-3 mb-4 px-2">
                                        @if ($whatsappStore->take_away)
                                            <div class="flex-grow-1">
                                                <input class="btn-check delivery-type" type="radio" name="order_type" id="takeAway" value="1" checked>
                                                <label class="w-100 d-flex align-items-center justify-content-center gap-2 py-3 rounded-3 fulfillment-label fs-16 fw-5" for="takeAway">
                                                    <i class="fas fa-shopping-bag"></i> {{ __('messages.whatsapp_stores.take_away') }}
                                                </label>
                                            </div>
                                        @endif
                                        @if ($whatsappStore->delivery)
                                            <div class="flex-grow-1">
                                                <input class="btn-check delivery-type" type="radio" name="order_type" id="delivery" value="2" {{ !$whatsappStore->take_away ? 'checked' : '' }}>
                                                <label class="w-100 d-flex align-items-center justify-content-center gap-2 py-3 rounded-3 fulfillment-label fs-16 fw-5" for="delivery">
                                                    <i class="fas fa-truck"></i> {{ __('messages.whatsapp_stores.delivery') }}
                                                </label>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <button class="btn btn-primary w-100 fs-18 fw-5 order-btn" data-bs-toggle="modal" data-bs-target="#orderNowModal">
                                    {{ __('messages.whatsapp_stores_templates.order_now') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
