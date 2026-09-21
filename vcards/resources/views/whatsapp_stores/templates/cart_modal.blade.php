      <!-- Modal -->
      <div class="modal fade" @if (getLanguage($whatsappStore->default_language) == 'Arabic' || getLanguage($whatsappStore->default_language) == 'Persian') dir="rtl" @endif id="cartModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content" @if (getLanguage($whatsappStore->default_language) == 'Arabic' || getLanguage($whatsappStore->default_language) == 'Persian') dir="rtl" @endif>
                  <div class="modal-header px-0 pt-0">
                      <input type="hidden" value="{{ $whatsappStore->id }}" id="whatsappStoreId">
                      <h5 class="modal-title fs-20 fw-6" id="exampleModalLabel">
                          {{ __('messages.whatsapp_stores_templates.cart_items') }}
                      </h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body pb-0">
                      <div class="overflow-auto">
                          <table class="table table-borderless mb-20">
                              <thead>
                                  <tr class="{{ app()->getLocale() == 'ar' || app()->getLocale() == 'fa' ? 'rtl' : '' }}">
                                      <th class="fw-6 fs-16 pt-0">{{ __('messages.whatsapp_stores.products') }}</th>
                                      <th class="fw-6 fs-16 pt-0">{{ __('messages.common.price') }}</th>
                                      <th class="fw-6 fs-16 pt-0 text-center">
                                          {{ __('messages.whatsapp_stores_templates.quantity') }}</th>
                                      <th class="fw-6 fs-16 pt-0 pe-0 text-end">
                                          {{ __('messages.whatsapp_stores_templates.total_price') }}</th>
                                      <th></th>
                                  </tr>
                              </thead>
                              <tbody id="cartItems">

                              </tbody>
                              <tfoot>
                                  <tr class="{{ app()->getLocale() == 'ar' || app()->getLocale() == 'fa' ? 'rtl' : '' }}">
                                      <td colspan="4" class="fs-16 text-end fw-5 pe-0" id="grandTotalLine">
                                          {{ __('messages.nfc.total') }} :
                                          <span id="subTotal">0</span>
                                      </td>
                                      <td></td>
                                  </tr>
                                  @if (!empty($whatsappStore->discount))
                                      <tr class="{{ app()->getLocale() == 'ar' || app()->getLocale() == 'fa' ? 'rtl' : '' }}">
                                          <td colspan="4" class="fs-16 text-end fw-5 pe-0">
                                              <input type="hidden" id="storeDiscount" value="{{ $whatsappStore->discount }}">
                                              - {{ __('messages.whatsapp_stores.discount') }} :
                                              <span id="discountPercentage">0</span>
                                          </td>
                                          <td></td>
                                      </tr>
                                  @endif

                                    <tr id="deliveryChargeSection"
                                        class="d-none {{ app()->getLocale() == 'ar' || app()->getLocale() == 'fa' ? 'rtl' : '' }}">
                                        <td colspan="4" class="fs-16 text-end fw-5 pe-0">
                                            <input type="hidden" id="deliveryChargeAmount"
                                                value="{{ $whatsappStore->delivery_charge }}">
                                            + {{ __('messages.whatsapp_stores.delivery_charge') }} : <span id="deliveryChargeText">0</span>
                                        </td>
                                        <td></td>
                                    </tr>

                                  <tr class="{{ app()->getLocale() == 'ar' || app()->getLocale() == 'fa' ? 'rtl' : '' }}">
                                      <td colspan="4" class="fs-18 fw-6 text-end pe-0 mt-2">
                                          {{ __('messages.whatsapp_stores.grand_total') }}: <span id="grandTotal">0</span>
                                      </td>
                                      <td></td>
                                  </tr>
                              </tfoot>
                          </table>
                      </div>

                        @if ($whatsappStore->take_away || $whatsappStore->delivery)
                            <h5 class="fs-16 fw-6 mb-3 mt-3">{{ __('messages.whatsapp_stores.fulfillment_method') }}</h5>
                            <div class="d-flex flex-column flex-md-row gap-3 mb-4">
                                @if ($whatsappStore->take_away)
                                    <div class="flex-grow-1">
                                        <input class="btn-check delivery-type" type="radio" name="order_type" id="takeAway" value="1"
                                            checked>
                                        <label
                                            class="w-100 d-flex align-items-center justify-content-center gap-2 py-3 fulfillment-label fs-14 fw-5"
                                            for="takeAway">
                                            <i class="fas fa-shopping-bag"></i> {{ __('messages.whatsapp_stores.take_away') }}
                                        </label>
                                    </div>
                                @endif
                                @if ($whatsappStore->delivery)
                                    <div class="flex-grow-1">
                                        <input class="btn-check delivery-type" type="radio" name="order_type" id="delivery" value="2" {{ !$whatsappStore->take_away ? 'checked' : '' }}>
                                        <label
                                            class="w-100 d-flex align-items-center justify-content-center gap-2 py-3 fulfillment-label fs-14 fw-5"
                                            for="delivery">
                                            <i class="fas fa-truck"></i> {{ __('messages.whatsapp_stores.delivery') }}
                                        </label>
                                    </div>
                                @endif
                            </div>
                        @endif

                      <button type="button" data-bs-toggle="modal" data-bs-target="#orderNowModal"
                          class="btn btn-primary m-0 w-100 order-btn">
                          {{ __('messages.whatsapp_stores_templates.order_now') }}
                      </button>
                  </div>
              </div>
          </div>
      </div>
