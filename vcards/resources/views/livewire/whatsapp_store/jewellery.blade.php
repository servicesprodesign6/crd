<div class="px-50 filter-section position-relative ">
    <div class="vector-all vector-product-1 position-absolute">
        <img src="{{ asset('assets/img/whatsapp_stores/jewellery/vector-item-1.png') }}" alt="images" class="w-100" />
    </div>
    <div class="vector-all vector-product-2 position-absolute">
        <img src="{{ asset('assets/img/whatsapp_stores/jewellery/vector-item-2.png') }}" alt="images" class="w-100" />
    </div>

    <div class="col-12 d-lg-none mb-4">
        <div class="mobile-header-wrapper">
            <div class="d-flex align-items-center gap-2 mb-3">
                <div class="mobile-search-container flex-grow-1">
                    <div class="position-relative">
                        <div class="search-icon-box">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </div>
                        <input type="text" wire:model.live="search" placeholder="{{ __('messages.whatsapp_stores_templates.search_items') }}" class="form-control mobile-search-input">
                    </div>
                </div>
                <button class="mobile-filter-btn" type="button" data-bs-toggle="offcanvas" data-bs-target="#filterOffcanvas">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="4" y1="21" x2="4" y2="14"></line>
                        <line x1="4" y1="10" x2="4" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12" y2="3"></line>
                        <line x1="20" y1="21" x2="20" y2="16"></line>
                        <line x1="20" y1="12" x2="20" y2="3"></line>
                        <line x1="1" y1="14" x2="7" y2="14"></line>
                        <line x1="9" y1="8" x2="15" y2="8"></line>
                        <line x1="17" y1="16" x2="23" y2="16"></line>
                    </svg>
                </button>
            </div>

            <div class="mobile-categories-nav">
                <div class="categories-track">
                    <button wire:click="$set('categoryFilter', [])" class="category-pill {{ empty($categoryFilter) ? 'active' : '' }}">
                        {{ __('messages.all') }}
                    </button>
                    @foreach($whatsappStore->categories as $category)
                        <button wire:click="$set('categoryFilter', [{{ $category->id }}])"
                                class="category-pill {{ in_array($category->id, $categoryFilter) ? 'active' : '' }}">
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-start bg-black-100 d-lg-none" tabindex="-1" id="filterOffcanvas" aria-labelledby="filterOffcanvasLabel" wire:ignore.self>
        <div class="offcanvas-header border-bottom border-secondary">
            <h5 class="offcanvas-title text-white" id="filterOffcanvasLabel">{{ __('messages.common.filter') }}</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <div class="items-tabs p-0">
                <div class="row mx-0 mb-22 min-max-value">
                    <div class="col-4 ps-0 px-1">
                        <input type="number" min="0" wire:model.defer="minPrice" class="form-control"
                            placeholder="{{ __('messages.whatsapp_stores_templates.min') }}"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')" />
                    </div>
                    <div class="col-4 px-1">
                        <input type="number" min="1" class="form-control" wire:model.defer="maxPrice"
                            placeholder="{{ __('messages.whatsapp_stores_templates.max') }}"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')" />
                    </div>
                    <div class="col-4 pe-0 px-1">
                        <button wire:click="applyPriceFilter" type="submit" class="apply-btn btn btn-primary w-100 h-100" data-bs-dismiss="offcanvas">
                            {{ __('messages.whatsapp_stores_templates.apply') }}
                        </button>
                    </div>
                </div>

                <div class="mt-4 mb-20 date-posted">
                    <div class="heading-text mb-20">
                        <h3 class="mb-0 fs-18 fw-medium text-white">
                            {{ __('messages.whatsapp_stores_templates.date_posted') }}</h3>
                    </div>
                    <div>
                        <div class="form-check mb-2" wire:key="mobile-date-3-days">
                            <input class="form-check-input" type="radio" name="mobile-dateFilter"
                                id="mobile-flexcheckboxDefault1" wire:model.live="dateFilter" value="3_days" />
                            <label class="form-check-label fs-16 fw-medium text-black lh-sm text-white"
                                for="mobile-flexcheckboxDefault1">
                                3 {{ __('messages.whatsapp_stores_templates.days_ago') }}
                            </label>
                        </div>
                        <div class="form-check mb-2" wire:key="mobile-date-1-week">
                            <input class="form-check-input" type="radio" name="mobile-dateFilter"
                                id="mobile-flexcheckboxDefault2" wire:model.live="dateFilter" value="1_week" />
                            <label class="form-check-label fs-16 fw-medium text-black lh-sm text-white"
                                for="mobile-flexcheckboxDefault2">
                                1 {{ __('messages.whatsapp_stores_templates.week_ago') }}
                            </label>
                        </div>
                        <div class="form-check mb-2" wire:key="mobile-date-1-month">
                            <input class="form-check-input" type="radio" name="mobile-dateFilter"
                                id="mobile-flexcheckboxDefault3" wire:model.live="dateFilter" value="1_month" />
                            <label class="form-check-label fs-16 fw-medium text-black lh-sm text-white"
                                for="mobile-flexcheckboxDefault3">
                                1 {{ __('messages.whatsapp_stores_templates.month_ago') }}
                            </label>
                        </div>
                        <div class="form-check mb-2" wire:key="mobile-date-6-months">
                            <input class="form-check-input" type="radio" name="mobile-dateFilter"
                                wire:model.live="dateFilter" value="6_months" id="mobile-flexcheckboxDefault4" />
                            <label class="form-check-label fs-16 fw-medium text-black lh-sm text-white"
                                for="mobile-flexcheckboxDefault4">
                                6 {{ __('messages.whatsapp_stores_templates.months_ago') }}
                            </label>
                        </div>
                        <div class="form-check mb-0" wire:key="mobile-date-1-year">
                            <input class="form-check-input" type="radio" name="mobile-dateFilter"
                                wire:model.live="dateFilter" value="1_year" id="mobile-flexcheckboxDefault5" />
                            <label class="form-check-label fs-16 fw-medium text-black lh-sm text-white"
                                for="mobile-flexcheckboxDefault5">
                                1 {{ __('messages.whatsapp_stores_templates.year_ago') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="mb-20">
                    <div class="heading-text mb-20">
                        <h3 class="mb-0 fs-18 fw-medium text-white">
                            {{ __('messages.whatsapp_stores_templates.search_price_range') }}</h3>
                    </div>
                    <div class="custom-select-container position-relative" wire:ignore>
                        <div class="custom-select">
                            <div
                                class="custom-select-box fs-14 fw-6 text-black d-flex align-items-center position-relative">
                                <div class="custom-arrow-select position-absolute d-flex align-items-center"
                                    style="right: 10px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                        viewBox="0 0 20 20" fill="none">
                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M4.41086 6.91098C4.25463 7.06725 4.16687 7.27918 4.16687 7.50015C4.16687 7.72112 4.25463 7.93304 4.41086 8.08931L9.41086 13.0893C9.56713 13.2455 9.77906 13.3333 10 13.3333C10.221 13.3333 10.4329 13.2455 10.5892 13.0893L15.5892 8.08931C15.741 7.93215 15.825 7.72164 15.8231 7.50315C15.8212 7.28465 15.7335 7.07564 15.579 6.92113C15.4245 6.76663 15.2155 6.67898 14.997 6.67709C14.7785 6.67519 14.568 6.75918 14.4109 6.91098L10 11.3218L5.58919 6.91098C5.43292 6.75476 5.221 6.66699 5.00003 6.66699C4.77906 6.66699 4.56713 6.75476 4.41086 6.91098Z"
                                            fill="currentColor" />
                                    </svg>
                                </div>
                                <span
                                    class="select-text">{{ __('messages.whatsapp_stores_templates.search_price_range') }}</span>
                            </div>
                            <div class="custom-select-options">
                                <div class="custom-select-option fs-14 fw-6 drop-item-select"
                                    wire:click.prevent="setPriceSortOrder('1')" data-value="1">
                                    {{ __('messages.whatsapp_stores_templates.low_to_high') }}</div>
                                <div class="custom-select-option fs-14 fw-6 drop-item-select"
                                    wire:click.prevent="setPriceSortOrder('2')" data-value="2">
                                    {{ __('messages.whatsapp_stores_templates.high_to_low') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="date-posted">
                    <div class="heading-text mb-20">
                        <h3 class="mb-0 fs-18 fw-medium text-white">
                            {{ __('messages.whatsapp_stores_templates.all_categories') }}</h3>
                    </div>
                    <div>
                        @foreach ($whatsappStore->categories as $category)
                            <div class="form-check mb-2" wire:key="mobile-category-{{ $category->id }}">
                                <input class="form-check-input" type="checkbox" wire:model.live="categoryFilter"
                                    value="{{ $category->id }}" name="flexcheckboxDefault"
                                    id="mobile-flexcheckboxCategory-{{ $category->id }}" />
                                <label class="form-check-label fs-16 fw-medium text-black lh-sm text-white"
                                    for="mobile-flexcheckboxCategory-{{ $category->id }}">
                                    {{ $category->name }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="mt-5">
                    <button type="button" wire:click="resetFilters" class="apply-btn btn btn-primary w-100 mb-3" data-bs-dismiss="offcanvas">
                        {{ __('messages.whatsapp_stores_templates.reset_filters') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3 mb-40 d-none d-lg-block">
            <div class="category-list">
                <div class="position-relative mb-22">
                    <div class="search-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"
                            fill="none">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M14.8752 8.37537C14.8752 10.0993 14.1904 11.7526 12.9714 12.9716C11.7525 14.1905 10.0992 14.8754 8.37524 14.8754C6.65134 14.8754 4.99804 14.1905 3.77905 12.9716C2.56006 11.7526 1.87524 10.0993 1.87524 8.37537C1.87524 6.65146 2.56006 4.99816 3.77905 3.77917C4.99804 2.56019 6.65134 1.87537 8.37524 1.87537C10.0992 1.87537 11.7525 2.56019 12.9714 3.77917C14.1904 4.99816 14.8752 6.65146 14.8752 8.37537ZM13.6565 8.37537C13.6565 9.77604 13.1001 11.1193 12.1097 12.1098C11.1192 13.1002 9.77592 13.6566 8.37524 13.6566C6.97457 13.6566 5.63126 13.1002 4.64084 12.1098C3.65041 11.1193 3.09399 9.77604 3.09399 8.37537C3.09399 6.97469 3.65041 5.63138 4.64084 4.64096C5.63126 3.65053 6.97457 3.09412 8.37524 3.09412C9.77592 3.09412 11.1192 3.65053 12.1097 4.64096C13.1001 5.63138 13.6565 6.97469 13.6565 8.37537Z"
                                fill="black" />
                            <path
                                d="M16.9307 16.0696L14.0869 13.2258C14.0312 13.1659 13.9639 13.1179 13.8891 13.0846C13.8144 13.0513 13.7337 13.0334 13.6519 13.032C13.57 13.0305 13.4888 13.0456 13.4129 13.0762C13.337 13.1069 13.2681 13.1525 13.2102 13.2103C13.1524 13.2682 13.1067 13.3371 13.0761 13.413C13.0454 13.4889 13.0304 13.5702 13.0318 13.652C13.0333 13.7338 13.0512 13.8145 13.0845 13.8892C13.1178 13.964 13.1658 14.0313 13.2257 14.0871L16.0694 16.9308C16.1252 16.9907 16.1925 17.0387 16.2673 17.072C16.342 17.1053 16.4227 17.1232 16.5045 17.1247C16.5863 17.1261 16.6676 17.1111 16.7435 17.0804C16.8194 17.0498 16.8883 17.0041 16.9462 16.9463C17.004 16.8884 17.0496 16.8195 17.0803 16.7436C17.1109 16.6677 17.126 16.5865 17.1245 16.5046C17.1231 16.4228 17.1052 16.3421 17.0719 16.2674C17.0386 16.1926 16.9906 16.1253 16.9307 16.0696Z"
                                fill="black" />
                        </svg>
                    </div>
                    <input type="search" placeholder="{{ __('messages.whatsapp_stores_templates.search_items') }}"
                        wire:model.live="search" class="form-control ps-45" />
                </div>

                <div class="row mx-0 mb-20 min-max-value position-relative pt-4">
                    <div class="col-4 ps-0 px-1">
                        <input type="number" min="0" wire:model.defer="minPrice" class="form-control"
                               placeholder="{{ __('messages.whatsapp_stores_templates.min') }}"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')" />
                    </div>
                    <div class="col-4 px-1">
                        <input type="number" min="1" wire:model.defer="maxPrice" class="form-control"
                               placeholder="{{ __('messages.whatsapp_stores_templates.max') }}"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')" />
                    </div>
                    <div class="col-4 pe-0 px-1">
                        <button wire:click="applyPriceFilter" type="submit" class="apply-btn btn btn-primary w-100 h-100">
                            {{ __('messages.whatsapp_stores_templates.apply') }}
                        </button>
                    </div>
                </div>

                <div class="custom-select-container position-relative mb-20">
                    <div class="custom-select">
                        <div class="custom-select-box fs-20 lh-1 fw-5 text-black d-flex align-items-center position-relative">
                            <div class="custom-arrow-select position-absolute d-flex align-items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M4.41098 6.91098C4.25476 7.06725 4.16699 7.27918 4.16699 7.50015C4.16699 7.72112 4.25476 7.93304 4.41098 8.08931L9.41098 13.0893C9.56725 13.2455 9.77918 13.3333 10.0001 13.3333C10.2211 13.3333 10.433 13.2455 10.5893 13.0893L15.5893 8.08931C15.7411 7.93215 15.8251 7.72164 15.8232 7.50315C15.8213 7.28465 15.7337 7.07564 15.5792 6.92113C15.4247 6.76663 15.2156 6.67898 14.9971 6.67709C14.7787 6.67519 14.5681 6.75918 14.411 6.91098L10.0001 11.3218L5.58931 6.91098C5.43304 6.75476 5.22112 6.66699 5.00015 6.66699C4.77918 6.66699 4.56725 6.75476 4.41098 6.91098Z"
                                        fill="#27262E" />
                                </svg>
                            </div>
                            <span class="select-text fs-20 fw-5 lh-1">
                                @if($priceSortOrder == '1')
                                    {{ __('messages.whatsapp_stores_templates.low_to_high') }}
                                @elseif($priceSortOrder == '2')
                                    {{ __('messages.whatsapp_stores_templates.high_to_low') }}
                                @else
                                    {{ __('messages.whatsapp_stores_templates.search_price_range') }}
                                @endif
                            </span>
                        </div>
                        <div class="custom-select-options">
                            <div class="custom-select-option fs-14 fw-6 text-black drop-item-select"
                                 wire:click.prevent="setPriceSortOrder('1')" data-value="1">
                                {{ __('messages.whatsapp_stores_templates.low_to_high') }}
                            </div>
                            <div class="custom-select-option fs-14 fw-6 text-black drop-item-select"
                                 wire:click.prevent="setPriceSortOrder('2')" data-value="2">
                                {{ __('messages.whatsapp_stores_templates.high_to_low') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-20 date-posted">
                    <div class="heading-text mb-20">
                        <h3 class="mb-0 fs-24 fw-medium text-black">{{ __('messages.whatsapp_stores_templates.all_categories') }}</h3>
                    </div>
                    <div>
                        @foreach ($whatsappStore->categories as $category)
                            <div class="form-check mb-2">
                                <label class="form-check-label fs-16 fw-medium lh-sm text-gray-100"
                                       for="flexcheckboxCategory{{ $category->id }}">
                                    {{ $category->name }}
                                </label>
                                <input class="form-check-input" type="checkbox" wire:model.live="categoryFilter"
                                       value="{{ $category->id }}" name="flexcheckboxDefault"
                                       id="flexcheckboxCategory{{ $category->id }}" />
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mb-20 date-posted">
                    <div class="heading-text mb-20">
                        <h3 class="mb-0 fs-18 fw-medium text-black">{{ __('messages.whatsapp_stores_templates.date_posted') }}</h3>
                    </div>
                    <div>
                        <div class="form-check mb-2">
                            <label class="form-check-label fs-16 fw-medium text-gray-100 lh-sm" for="flexcheckbox1">
                                3 {{ __('messages.whatsapp_stores_templates.days_ago') }}
                            </label>
                            <input class="form-check-input radio" type="radio" wire:model.live="dateFilter"
                                   value="3_days" name="flexcheckbox" id="flexcheckbox1" />
                        </div>
                        <div class="form-check mb-2">
                            <label class="form-check-label fs-16 fw-medium text-gray-100 lh-sm" for="flexcheckbox2">
                                1 {{ __('messages.whatsapp_stores_templates.week_ago') }}
                            </label>
                            <input class="form-check-input radio" type="radio" wire:model.live="dateFilter"
                                   value="1_week" name="flexcheckbox" id="flexcheckbox2" />
                        </div>
                        <div class="form-check mb-2">
                            <label class="form-check-label fs-16 fw-medium text-gray-100 lh-sm" for="flexcheckbox3">
                                1 {{ __('messages.whatsapp_stores_templates.month_ago') }}
                            </label>
                            <input class="form-check-input radio" type="radio" wire:model.live="dateFilter"
                                   value="1_month" name="flexcheckbox" id="flexcheckbox3" />
                        </div>
                        <div class="form-check mb-2">
                            <label class="form-check-label fs-16 fw-medium text-gray-100 lh-sm" for="flexcheckbox4">
                                6 {{ __('messages.whatsapp_stores_templates.months_ago') }}
                            </label>
                            <input class="form-check-input radio" type="radio" wire:model.live="dateFilter"
                                   value="6_months" name="flexcheckbox" id="flexcheckbox4" />
                        </div>
                        <div class="form-check mb-0">
                            <label class="form-check-label fs-16 fw-medium text-gray-100 lh-sm" for="flexcheckbox5">
                                1 {{ __('messages.whatsapp_stores_templates.year_ago') }}
                            </label>
                            <input class="form-check-input radio" type="radio" wire:model.live="dateFilter"
                                   value="1_year" name="flexcheckbox" id="flexcheckbox5" />
                        </div>
                    </div>
                </div>

                <div class="mt-5">
                    <button type="button" wire:click="resetFilters" class="apply-btn btn btn-primary w-100">
                        {{ __('messages.whatsapp_stores_templates.reset_filters') }}
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="row row-gap-12px product-all-section">
                @foreach ($products as $product)
                    <div class="col-lg-4 col-sm-6">
                        <div class="product-card h-100 d-flex flex-column">
                            <a href="{{ route('whatsapp.store.product.details', [$whatsappStore->url_alias, $product->id]) }}"
                               class="d-flex justify-content-between flex-column">
                                <div class="product-img w-100 h-100 d-flex justify-content-center align-items-center mx-auto">
                                    <img src="{{ $product->images_url[0] ?? '' }}" alt="images"
                                        class="h-100 w-100 object-fit-cover product-image" />
                                </div>
                            </a>
                            <div class="product-desc" style="flex-grow: 1;">
                                <div class="d-flex justify-content-between h-100 flex-column">
                                    <div>
                                        <input type="hidden" value="{{ $product->category->name }}" class="product-category">
                                        <input type="hidden" value="{{ $product->available_stock }}" class="available-stock">
                                        <input type="hidden" value="{{ $product->images_url[0] ?? '' }}" class="product-image">

                                        <a href="{{ route('whatsapp.store.product.details', [$whatsappStore->url_alias, $product->id]) }}">
                                            <h6 class="fs-20 text-black fw-normal mt-3 product-name">{{ $product->name }}</h6>
                                            <p class="fs-16 fw-normal text-gray-200 product-category">
                                                {{ $product->category->name }}
                                            </p>
                                        </a>
                                    </div>

                                    <div class="d-flex justify-content-between mt-auto @if ($product->available_stock == 0) flex-column @endif">
                                        <a href="{{ route('whatsapp.store.product.details', [$whatsappStore->url_alias, $product->id]) }}">
                                            <h4 class="fs-20 text-black fw-6 mb-0">
                                                <span class="currency_icon selling_price">{{ currencyFormat($product->selling_price, 2, $product->currency->currency_code) }}</span>
                                                @if ($product->net_price)
                                                    <del class="fs-14 fw-5 text-gray-200 ms-2">{{ currencyFormat($product->net_price, 2, $product->currency->currency_code) }}</del>
                                                @endif
                                            </h4>
                                        </a>

                                        @if ($product->available_stock > 0)
                                            <button class="cart-btn d-flex justify-content-center align-items-center addToCartBtn"
                                                    data-id="{{ $product->id }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                                        d="M5.7103 6.00002H21.2921L19.8081 15H7.19428L5.7103 6.00002Z"
                                                        stroke="white" stroke-width="1.48438" stroke-linecap="round"
                                                        stroke-linejoin="round" />
                                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                                        d="M9 20C9.55228 20 10 19.5523 10 19C10 18.4477 9.55228 18 9 18C8.44772 18 8 18.4477 8 19C8 19.5523 8.44772 20 9 20Z"
                                                        stroke="white" stroke-width="1.48438" />
                                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                                        d="M18 20C18.5523 20 19 19.5523 19 19C19 18.4477 18.5523 18 18 18C17.4477 18 17 18.4477 17 19C17 19.5523 17.4477 20 18 20Z"
                                                        stroke="white" stroke-width="1.48438" />
                                                    <path d="M7 6H3" stroke="white" stroke-width="1.48438" stroke-linecap="round" />
                                                </svg>
                                            </button>
                                        @else
                                            <span class="badge bg-danger text-white out-of-stock-text d-flex justify-content-center align-items-center mt-2" style="height: 35px; margin-left: 13%; border-radius: 0px;">
                                                {{ __('messages.whatsapp_stores.out_of_stock') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                <div class="mt-4 d-flex justify-content-center custom-pagination">
                    {{ $products->links() }}
                </div>
                @if ($products->count() == 0)
                    <div class="no-items-found d-flex justify-content-center align-items-center" style="height: 50vh;">
                        <h2 class="mb-0 text-break">
                            {{ __('messages.whatsapp_stores.no_items_found') }}
                        </h2>
                    </div>
                @endif

                {{-- <!-- Pagination -->
                <div class="mt-4 d-flex justify-content-center custom-pagination">
                    <nav aria-label="Page navigation example" class="d-flex justify-content-center align-items-center flex-wrap page-number row-gap-3 position-relative">
                        {{ $products->links() }}
                    </nav>
                </div> --}}
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:navigated', () => {
            initializeOffcanvasCleanup();
        });

        document.addEventListener('DOMContentLoaded', () => {
            initializeOffcanvasCleanup();
        });

        function initializeOffcanvasCleanup() {
            const offcanvasElement = document.getElementById('filterOffcanvas');
            if (offcanvasElement) {
                offcanvasElement.addEventListener('hidden.bs.offcanvas', function () {
                    document.body.style.overflow = 'auto';
                    document.body.style.paddingRight = '0px';
                    document.documentElement.style.overflow = 'auto';
                    const backdrops = document.querySelectorAll('.offcanvas-backdrop');
                    backdrops.forEach(backdrop => backdrop.remove());
                    document.body.classList.remove('offcanvas-open');
                    @this.call('$refresh');
                });
            }
        }
    </script>
</div>
