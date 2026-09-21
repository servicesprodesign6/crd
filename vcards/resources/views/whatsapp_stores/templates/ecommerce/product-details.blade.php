<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $product->name }} | {{ $whatsappStore->store_name }}</title>
    <link href="{{ asset('front/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="icon" href="{{ $whatsappStore->logo_url }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('assets/css/slider/css/slick.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/slider/css/slick-theme.min.css') }}">
    <link rel="stylesheet" href="{{ mix('assets/css/whatsappp_store/ecommerce.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/third-party.css') }}">
    <link rel="stylesheet" href="{{ asset('css/plugins.css') }}">
    <link rel="stylesheet" href="{{ mix('assets/css/whatsappp_store/custom.css') }}" />
    @if ($whatsappStore->font_family || $whatsappStore->font_size || $whatsappStore->custom_css)
        <style>
            @if ($whatsappStore->font_family)
                body {
                    font-family: {{ $whatsappStore->font_family }};
                }
            @endif

            @if ($whatsappStore->font_size)
                div > h4 {
                    font-size: {{ $whatsappStore->font_size }}px !important;
                }
            @endif

            @if ($whatsappStore->custom_css)
                {!! $whatsappStore->custom_css !!}
            @endif
        </style>
    @endif
</head>

<body>
    <div class="main-content mx-auto w-100 overflow-hidden d-flex flex-column justify-content-between {{ getLocalLanguage() == 'ar' || getLocalLanguage() == 'fa' ? 'rtl' : '' }}" @if (getLanguage($whatsappStore->default_language) == 'Arabic' || getLanguage($whatsappStore->default_language) == 'Persian') dir="rtl" @endif>
        <div>
            <nav class="navbar navbar-expand-lg px-50 position-relative">
                <div class="container-fluid p-0 gap-2 gap-lg-4 flex-nowrap">
                    <div class="d-flex align-items-center gap-3 overflow-hidden">
                        <a class="navbar-brand p-0 m-0"
                            href="{{ route('whatsapp.store.show', $whatsappStore->url_alias) }}">
                            <img src="{{ $whatsappStore->logo_url }}" alt="logo"
                                class="w-100 h-100 object-fit-cover" loading="lazy" />
                        </a>
                        <span class="fw-5 fs-20 text-truncate"><a
                                href="{{ route('whatsapp.store.show', $whatsappStore->url_alias) }}"
                                style="color: #212529 ">{{ $whatsappStore->store_name }}</a></span>
                    </div>

                    <div class="d-flex align-items-center gap-lg-4 gap-sm-3 gap-2">
                        <div class="language-dropdown position-relative">
                            <button class="dropdown-btn position-relative" id="dropdownMenuButton"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                @if (array_key_exists(checkLanguageSession($whatsappStore->url_alias), \App\Models\User::FLAG))
                                    <img class="flag" alt="flag"
                                        src="{{ asset(\App\Models\User::FLAG[getLanguageIsoCode($whatsappStore->default_language) ?? 'en']) }}"
                                        loading="lazy" />
                                @endif
                                {{ strtoupper(getLanguageIsoCode($whatsappStore->default_language) ?? 'en') }}
                            </button>
                            <svg class="dropdown-arrow" xmlns="http://www.w3.org/2000/svg" width="14" height="8"
                                viewBox="0 0 18 10" fill="none">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M0.615983 0.366227C0.381644 0.600637 0.25 0.918522 0.25 1.24998C0.25 1.58143 0.381644 1.89932 0.615983 2.13373L8.11598 9.63373C8.35039 9.86807 8.66828 9.99971 8.99973 9.99971C9.33119 9.99971 9.64907 9.86807 9.88348 9.63373L17.3835 2.13373C17.6112 1.89797 17.7372 1.58222 17.7343 1.25448C17.7315 0.92673 17.6 0.613214 17.3683 0.381454C17.1365 0.149694 16.823 0.0182329 16.4952 0.0153849C16.1675 0.0125369 15.8517 0.13853 15.616 0.366227L8.99973 6.98248L2.38348 0.366227C2.14907 0.131889 1.83119 0.000244141 1.49973 0.000244141C1.16828 0.000244141 0.850393 0.131889 0.615983 0.366227Z"
                                    fill="black" />
                            </svg>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                @foreach (getAllLanguageWithFullData() as $language)
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)" id="languageName"
                                            data-name="{{ $language->iso_code }}">

                                            @if (array_key_exists($language->iso_code, \App\Models\User::FLAG))
                                                <img class="flag" alt="flag"
                                                    src="{{ asset(\App\Models\User::FLAG[$language->iso_code]) }}"
                                                    loading="lazy" />
                                            @else
                                                @if (count($language->media) != 0)
                                                    <img src="{{ $language->image_url }}" class="me-1" loading="lazy"
                                                        loading="lazy" />
                                                @else
                                                    <i class="fa fa-flag fa-xl me-3 text-danger" aria-hidden="true"></i>
                                                @endif
                                            @endif
                                            {{ strtoupper($language->iso_code) }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <button id="addToCartViewBtn"
                            class="add-to-cart-btn d-flex align-items-center justify-content-center position-relative">
                            <div
                                class="position-absolute cart-count d-flex align-items-center justify-content-center product-count-badge">

                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40"
                                fill="none">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M27.0834 11.6668C27.0834 11.9984 26.9517 12.3163 26.7172 12.5507C26.4828 12.7851 26.1649 12.9168 25.8334 12.9168C25.5018 12.9168 25.1839 12.7851 24.9495 12.5507C24.7151 12.3163 24.5834 11.9984 24.5834 11.6668V9.16683C24.5834 7.95125 24.1005 6.78546 23.2409 5.92592C22.3814 5.06638 21.2156 4.5835 20 4.5835C18.7844 4.5835 17.6187 5.06638 16.7591 5.92592C15.8996 6.78546 15.4167 7.95125 15.4167 9.16683V11.6668C15.4167 11.9984 15.285 12.3163 15.0506 12.5507C14.8161 12.7851 14.4982 12.9168 14.1667 12.9168C13.8352 12.9168 13.5172 12.7851 13.2828 12.5507C13.0484 12.3163 12.9167 11.9984 12.9167 11.6668V9.16683C12.9167 7.28821 13.663 5.48654 14.9913 4.15816C16.3197 2.82977 18.1214 2.0835 20 2.0835C21.8786 2.0835 23.6803 2.82977 25.0087 4.15816C26.3371 5.48654 27.0834 7.28821 27.0834 9.16683V11.6668Z"
                                    fill="black" />
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M32.2367 13.917L33.57 33.917C33.6035 34.4292 33.5316 34.9428 33.3588 35.4262C33.186 35.9095 32.9159 36.3523 32.5653 36.7272C32.2146 37.102 31.7908 37.401 31.3201 37.6057C30.8493 37.8103 30.3416 37.9163 29.8283 37.917H10.1717C9.65823 37.9167 9.1503 37.8111 8.67933 37.6066C8.20836 37.4022 7.78437 37.1032 7.43362 36.7282C7.08287 36.3532 6.81282 35.9103 6.64019 35.4267C6.46756 34.9432 6.39603 34.4293 6.43001 33.917L7.76334 13.917C7.82676 12.9678 8.24855 12.0782 8.94327 11.4284C9.63799 10.7785 10.5537 10.417 11.505 10.417H28.495C29.4463 10.417 30.362 10.7785 31.0568 11.4284C31.7515 12.0782 32.1733 12.9678 32.2367 13.917ZM24.1433 17.797C23.7707 18.5803 23.1837 19.2418 22.4504 19.7051C21.717 20.1683 20.8674 20.4141 20 20.4141C19.1326 20.4141 18.283 20.1683 17.5497 19.7051C16.8163 19.2418 16.2293 18.5803 15.8567 17.797C15.7862 17.6487 15.6872 17.5158 15.5654 17.4057C15.4435 17.2957 15.3012 17.2108 15.1465 17.1557C14.9918 17.1007 14.8278 17.0767 14.6638 17.0851C14.4998 17.0934 14.3391 17.134 14.1908 17.2045C14.0426 17.275 13.9096 17.374 13.7996 17.4958C13.6896 17.6177 13.6046 17.76 13.5496 17.9147C13.4946 18.0694 13.4706 18.2334 13.4789 18.3974C13.4873 18.5613 13.5279 18.722 13.5983 18.8703C14.1724 20.0824 15.0788 21.1066 16.212 21.8238C17.3453 22.541 18.6589 22.9218 20 22.9218C21.3412 22.9218 22.6547 22.541 23.788 21.8238C24.9213 21.1066 25.8276 20.0824 26.4017 18.8703C26.4722 18.722 26.5127 18.5613 26.5211 18.3974C26.5295 18.2334 26.5055 18.0694 26.4504 17.9147C26.3954 17.76 26.3105 17.6177 26.2004 17.4958C26.0904 17.374 25.9575 17.275 25.8092 17.2045C25.6609 17.134 25.5002 17.0934 25.3362 17.0851C25.1722 17.0767 25.0082 17.1007 24.8536 17.1557C24.6989 17.2108 24.5565 17.2957 24.4347 17.4057C24.3128 17.5158 24.2138 17.6487 24.1433 17.797Z"
                                    fill="black" />
                            </svg>
                        </button>
                    </div>
                </div>
            </nav>
            <div class="item-details-section px-50 mb-30">
                <div class="item-details-card overflow-hidden position-relative">
                    <div class="container-lg position-relative card-main">
                        <div class="row">
                            <div class="col-lg-6 mb-lg-0 mb-40 px-0">
                                <div class="row flex-sm-row flex-column-reverse align-items-center">
                                    <div class="col-lg-3 col-sm-2 left-slider">
                                        <div class="slider-nav">
                                            @foreach ($product->images_url as $image)
                                                <div>
                                                    <div class="thumbnail-img">
                                                        <img src="{{ $image }}" alt="items"
                                                            class="w-100 h-100 object-fit-cover rounded"
                                                            loading="lazy" />
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="col-lg-9 col-sm-10 ps-sm-0 mb-4 mb-sm-0">
                                        <div class="slider-for">
                                            @foreach ($product->images_url as $image)
                                                <div>
                                                    <div class="details-img h-100 w-100">
                                                        <img src="{{ $image }}" alt="items"
                                                            class="w-100 h-100 object-fit-cover rounded"
                                                            loading="lazy" />
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 px-0">
                                <div class="ps-0 ps-lg-3 details d-flex justify-content-between flex-column h-100">
                                    <div>
                                        <div
                                            class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                            <h4 class="fs-32 fw-6 mb-0 product-name">{{ $product->name }}</h4>
                                            <a href="https://api.whatsapp.com/send?text={{ urlencode($product->name . ' - ' . url()->current()) }}"
                                                target="_blank" class="product-share-btn">
                                                <i class="fas fa-share-alt"></i>
                                                <span class="fs-16">{{ __('messages.vcard.share') }}</span>
                                            </a>
                                        </div>
                                        <input type="hidden" value="{{ $product->available_stock }}"
                                            class="available-stock">
                                        <input type="hidden" value="{{ $product->category->name }}"
                                            class="product-category">
                                        <input type="hidden" value="{{ $product->images_url[0] ?? '' }}"
                                            class="product-image">

                                        <p class="fs-30 fw-7 mb-30 lh-sm">
                                            <span class="currency_icon selling_price">{{ currencyFormat($product->selling_price, 2, $product->currency->currency_code) }}</span>
                                            @if ($product->net_price)
                                                <del class="fs-20 fw-6 text-gray-200">{{ currencyFormat($product->net_price, 2, $product->currency->currency_code) }}</del>
                                            @endif
                                        </p>
                                        <p class="fs-20 fw-6 text-gray-200 mb-10 lh-sm">
                                            {{ __('messages.whatsapp_stores_templates.description') }}</p>
                                        <p class="fw-5 fs-14 mb-40 lh-sm details-desc">
                                            {!! $product->description !!}
                                        </p>
                                    </div>
                                    @if ($product->available_stock != 0)
                                        <button
                                            class="btn btn-primary d-flex justify-content-center align-items-center w-100 fs-18 gap-2 addToCartBtn"
                                            data-id="{{ $product->id }}">
                                            <span>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40"
                                                    viewBox="0 0 40 40" fill="none">
                                                    <path
                                                        d="M31.4875 25.4531L32.6527 19.1277C32.6781 18.9947 32.6706 18.8574 32.6307 18.728C32.5908 18.5985 32.5198 18.4808 32.424 18.3851C32.3281 18.2894 32.2102 18.2187 32.0807 18.1791C31.9512 18.1395 31.8139 18.1322 31.6809 18.1579C31.1281 18.2744 30.5648 18.3332 29.9999 18.3334C27.9216 18.3333 25.9184 17.5568 24.3831 16.1561C22.8478 14.7554 21.8912 12.8317 21.7009 10.7622C21.6834 10.5548 21.5887 10.3614 21.4357 10.2203C21.2826 10.0792 21.0823 10.0006 20.8741 10H9.1405L9.01987 9.45719C8.89649 8.90209 8.58751 8.40565 8.14395 8.04982C7.70039 7.694 7.14876 7.50005 6.58011 7.5H4.21081C3.79565 7.5 3.41097 7.78359 3.34425 8.19336C3.32398 8.31294 3.33004 8.43551 3.362 8.55251C3.39396 8.66952 3.45106 8.77814 3.52932 8.87081C3.60757 8.96348 3.7051 9.03796 3.81509 9.08907C3.92509 9.14018 4.04491 9.16667 4.1662 9.16672H6.57948C6.76931 9.16586 6.95368 9.23015 7.1018 9.34887C7.24992 9.46758 7.35282 9.63353 7.39331 9.81898L11.9122 30.1549C11.2522 30.362 10.6728 30.7689 10.2541 31.3196C9.83546 31.8702 9.59816 32.5372 9.57502 33.2286C9.55189 33.9199 9.74408 34.6013 10.125 35.1787C10.506 35.7561 11.0568 36.2009 11.7015 36.4516C12.3462 36.7023 13.0529 36.7465 13.7238 36.5782C14.3947 36.4099 14.9968 36.0374 15.4468 35.5121C15.8968 34.9867 16.1725 34.3346 16.2359 33.6458C16.2992 32.957 16.147 32.2655 15.8003 31.667H26.2828C25.9164 32.3022 25.7699 33.0407 25.8659 33.7678C25.962 34.4948 26.2953 35.1699 26.8141 35.6882C27.333 36.2065 28.0083 36.5392 28.7355 36.6345C29.4627 36.7299 30.201 36.5826 30.8359 36.2156C31.4708 35.8486 31.9669 35.2823 32.2472 34.6045C32.5275 33.9268 32.5763 33.1756 32.386 32.4673C32.1958 31.759 31.7772 31.1333 31.1951 30.6872C30.6129 30.2411 29.8999 29.9995 29.1665 30H13.5851L13.0296 27.5H29.029C29.6134 27.5 30.1794 27.2952 30.6285 26.9213C31.0777 26.5473 31.3816 26.0279 31.4875 25.4531ZM12.9165 34.5834C12.6693 34.5834 12.4276 34.51 12.2221 34.3727C12.0165 34.2353 11.8563 34.0401 11.7617 33.8117C11.6671 33.5833 11.6423 33.332 11.6905 33.0895C11.7388 32.847 11.8578 32.6243 12.0326 32.4495C12.2074 32.2747 12.4302 32.1556 12.6727 32.1074C12.9151 32.0591 13.1665 32.0839 13.3949 32.1785C13.6233 32.2731 13.8185 32.4333 13.9559 32.6389C14.0932 32.8445 14.1665 33.0861 14.1665 33.3334C14.1665 33.6649 14.0348 33.9828 13.8004 34.2172C13.566 34.4517 13.248 34.5834 12.9165 34.5834ZM30.4165 33.3334C30.4165 33.5806 30.3432 33.8223 30.2059 34.0278C30.0685 34.2334 29.8733 34.3936 29.6449 34.4882C29.4165 34.5828 29.1651 34.6076 28.9227 34.5593C28.6802 34.5111 28.4574 34.3921 28.2826 34.2172C28.1078 34.0424 27.9888 33.8197 27.9405 33.5772C27.8923 33.3347 27.9171 33.0834 28.0117 32.855C28.1063 32.6266 28.2665 32.4314 28.4721 32.294C28.6776 32.1567 28.9193 32.0834 29.1665 32.0834C29.498 32.0834 29.816 32.2151 30.0504 32.4495C30.2848 32.6839 30.4165 33.0018 30.4165 33.3334Z"
                                                        fill="currentcolor" />
                                                    <path
                                                        d="M29.9997 3.3335C28.6811 3.3335 27.3922 3.72449 26.2959 4.45703C25.1995 5.18957 24.3451 6.23075 23.8405 7.44892C23.3359 8.66709 23.2039 10.0075 23.4611 11.3007C23.7183 12.5939 24.3533 13.7818 25.2856 14.7142C26.218 15.6465 27.4059 16.2814 28.6991 16.5387C29.9923 16.7959 31.3327 16.6639 32.5509 16.1593C33.769 15.6547 34.8102 14.8002 35.5428 13.7039C36.2753 12.6076 36.6663 11.3187 36.6663 10.0001C36.6663 8.23204 35.9639 6.53637 34.7137 5.28613C33.4634 4.0359 31.7677 3.33352 29.9997 3.3335ZM32.9163 10.8335H30.833V12.9169C30.8341 13.027 30.8133 13.1362 30.7719 13.2382C30.7305 13.3403 30.6693 13.4331 30.5918 13.5113C30.5143 13.5896 30.4221 13.6517 30.3205 13.6941C30.2188 13.7365 30.1098 13.7583 29.9997 13.7583C29.8896 13.7583 29.7805 13.7365 29.6789 13.6941C29.5773 13.6517 29.4851 13.5896 29.4076 13.5113C29.3301 13.4331 29.2689 13.3403 29.2275 13.2382C29.1861 13.1362 29.1653 13.027 29.1664 12.9169V10.8335H27.083C26.9729 10.8346 26.8637 10.8138 26.7616 10.7724C26.6596 10.731 26.5668 10.6698 26.4885 10.5923C26.4103 10.5148 26.3482 10.4226 26.3058 10.321C26.2634 10.2193 26.2416 10.1103 26.2416 10.0002C26.2416 9.89006 26.2634 9.78103 26.3058 9.6794C26.3482 9.57777 26.4103 9.48554 26.4885 9.40806C26.5668 9.33057 26.6596 9.26936 26.7616 9.22796C26.8637 9.18656 26.9729 9.16579 27.083 9.16686H29.1664V7.0835C29.1653 6.97338 29.1861 6.86415 29.2275 6.76211C29.2689 6.66008 29.3301 6.56725 29.4076 6.48901C29.4851 6.41077 29.5773 6.34866 29.6789 6.30628C29.7805 6.26389 29.8896 6.24207 29.9997 6.24207C30.1098 6.24207 30.2188 6.26389 30.3205 6.30628C30.4221 6.34866 30.5143 6.41077 30.5918 6.48901C30.6693 6.56725 30.7305 6.66008 30.7719 6.76211C30.8133 6.86415 30.8341 6.97338 30.833 7.0835V9.16686H32.9164C33.136 9.16898 33.3459 9.25771 33.5004 9.41376C33.655 9.56981 33.7417 9.78055 33.7417 10.0002C33.7417 10.2198 33.655 10.4305 33.5004 10.5866C33.3459 10.7426 33.136 10.8314 32.9164 10.8335H32.9163Z"
                                                        fill="currentcolor" />
                                                </svg>
                                            </span>
                                            {{ __('messages.whatsapp_stores_templates.add_to_cart') }}
                                        </button>
                                    @else
                                        <button
                                            class="btn btn-danger text-danger d-flex justify-content-center align-items-center w-50 fs-18 gap-2 fw-5 out-of-stock-text">
                                            {{ __('messages.whatsapp_stores.out_of_stock') }}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="recommended-product-section px-50 position-relative">
                <div class="section-heading">
                    <h2 class="position-relative mb-0">
                        {{ __('messages.whatsapp_stores_templates.recommended_products') }}</h2>
                </div>
                <div class="product-slider">
                    @foreach ($whatsappStore->products()->where('id', '!=', $product->id)->orderByRaw('sort IS NULL, sort ASC')->orderByDesc('created_at')->get() as $product)
                        <div>
                            <a href="{{ route('whatsapp.store.product.details', [$whatsappStore->url_alias, $product->id]) }}"
                                class="d-block h-100" style="color: #212529;">
                                <div class="product-card">

                                    <div class="product-img mx-auto overflow-hidden w-100">
                                        <img src="{{ $product->images_url[0] ?? '' }}" alt="item"
                                            class="w-100 h-100 object-fit-cover rounded product-image" loading="lazy" />
                                    </div>

                                    <div class="product-details" style="flex-grow: 1;">
                                        <div class="d-flex justify-content-between h-100 flex-column">
                                            <div>
                                                <h5 class="fs-20 fw-6 mb-1 product-name">{{ $product->name }}</h5>
                                                <input type="hidden" value="{{ $product->available_stock }}"
                                                        class="available-stock">
                                                <input type="hidden" class="product-category" value="{{ $product->category->name }}" />
                                            </div>
                                            <div class="d-flex flex-column gap-2 justify-content-between">
                                                <p class="fs-30 fw-6 lh-sm mb-0">
                                                        <span class="currency_icon selling_price">{{ currencyFormat($product->selling_price, 2, $product->currency->currency_code) }}</span>
                                                    @if ($product->net_price)
                                                        <del class="fs-20 fw-5 text-gray-200 text-nowrap">{{ currencyFormat($product->net_price, 2, $product->currency->currency_code) }}</del>
                                                    @endif
                                                </p>

                                                @if ($product->available_stock > 0)
                                                <button data-id="{{ $product->id }}"
                                                    class="@if($product->available_stock == 0) disabled @endif btn btn-primary d-flex justify-content-center align-items-center addToCartBtn">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                        viewBox="0 0 30 30" fill="none">
                                                        <path
                                                            d="M23.6158 19.0898L24.4896 14.3458C24.5087 14.246 24.503 14.1431 24.4731 14.046C24.4432 13.9489 24.39 13.8606 24.3181 13.7888C24.2462 13.7171 24.1578 13.664 24.0606 13.6343C23.9635 13.6046 23.8605 13.5991 23.7608 13.6184C23.3462 13.7058 22.9237 13.7499 22.5 13.75C20.9414 13.75 19.4389 13.1676 18.2875 12.1171C17.136 11.0666 16.4185 9.62377 16.2758 8.07164C16.2626 7.91607 16.1917 7.77107 16.0769 7.66525C15.9621 7.55943 15.8118 7.50047 15.6557 7.5H6.8555L6.76503 7.09289C6.67249 6.67657 6.44076 6.30424 6.10809 6.03737C5.77541 5.7705 5.36169 5.62504 4.9352 5.625H3.15823C2.84686 5.625 2.55835 5.8377 2.50831 6.14502C2.49311 6.23471 2.49765 6.32663 2.52162 6.41438C2.54559 6.50214 2.58842 6.5836 2.64711 6.65311C2.7058 6.72261 2.77894 6.77847 2.86144 6.8168C2.94394 6.85513 3.03381 6.87501 3.12477 6.87504H4.93473C5.0771 6.87439 5.21538 6.92261 5.32647 7.01165C5.43756 7.10069 5.51474 7.22515 5.54511 7.36424L8.93428 22.6162C8.43929 22.7715 8.00475 23.0767 7.69073 23.4897C7.37672 23.9026 7.19874 24.4029 7.18139 24.9214C7.16404 25.4399 7.30818 25.951 7.59389 26.384C7.8796 26.8171 8.29276 27.1506 8.77627 27.3387C9.25978 27.5267 9.78976 27.5599 10.293 27.4337C10.7962 27.3075 11.2477 27.028 11.5852 26.634C11.9227 26.2401 12.1295 25.751 12.177 25.2343C12.2245 24.7177 12.1103 24.1991 11.8503 23.7502H19.7123C19.4374 24.2267 19.3275 24.7805 19.3996 25.3258C19.4716 25.8711 19.7216 26.3774 20.1107 26.7662C20.4998 27.1549 21.0064 27.4044 21.5517 27.4759C22.0971 27.5474 22.6508 27.437 23.127 27.1617C23.6032 26.8864 23.9753 26.4617 24.1855 25.9534C24.3957 25.4451 24.4323 24.8817 24.2896 24.3505C24.147 23.8192 23.833 23.35 23.3964 23.0154C22.9598 22.6808 22.425 22.4996 21.875 22.5H10.189L9.77229 20.625H21.7719C22.2102 20.625 22.6347 20.4714 22.9715 20.191C23.3084 19.9105 23.5363 19.5209 23.6158 19.0898ZM9.68751 25.9375C9.50209 25.9375 9.32083 25.8825 9.16666 25.7795C9.01249 25.6765 8.89233 25.5301 8.82137 25.3588C8.75041 25.1875 8.73185 24.999 8.76802 24.8171C8.80419 24.6353 8.89348 24.4682 9.02459 24.3371C9.15571 24.206 9.32275 24.1167 9.50461 24.0805C9.68647 24.0444 9.87497 24.0629 10.0463 24.1339C10.2176 24.2048 10.364 24.325 10.467 24.4792C10.57 24.6333 10.625 24.8146 10.625 25C10.625 25.2487 10.5262 25.4871 10.3504 25.6629C10.1746 25.8387 9.93615 25.9375 9.68751 25.9375ZM22.8125 25C22.8125 25.1854 22.7575 25.3667 22.6545 25.5209C22.5515 25.675 22.4051 25.7952 22.2338 25.8662C22.0625 25.9371 21.874 25.9557 21.6921 25.9195C21.5103 25.8833 21.3432 25.794 21.2121 25.6629C21.081 25.5318 20.9917 25.3648 20.9555 25.1829C20.9193 25.0011 20.9379 24.8126 21.0089 24.6413C21.0798 24.4699 21.2 24.3235 21.3542 24.2205C21.5083 24.1175 21.6896 24.0625 21.875 24.0625C22.1236 24.0625 22.3621 24.1613 22.5379 24.3371C22.7137 24.5129 22.8125 24.7514 22.8125 25Z"
                                                            fill="currentColor" />
                                                        <path
                                                            d="M22.5 2.5C21.5111 2.5 20.5444 2.79324 19.7221 3.34265C18.8999 3.89205 18.259 4.67294 17.8806 5.58657C17.5022 6.5002 17.4031 7.50553 17.5961 8.47543C17.789 9.44533 18.2652 10.3362 18.9645 11.0355C19.6637 11.7348 20.5546 12.211 21.5245 12.4039C22.4944 12.5968 23.4998 12.4978 24.4134 12.1194C25.327 11.7409 26.1079 11.1001 26.6573 10.2778C27.2067 9.45558 27.5 8.48888 27.5 7.49998C27.4999 6.17391 26.9732 4.90215 26.0355 3.96448C25.0978 3.0268 23.8261 2.50002 22.5 2.5ZM24.6875 8.125H23.125V9.68752C23.1258 9.7701 23.1102 9.85203 23.0792 9.92856C23.0481 10.0051 23.0022 10.0747 22.9441 10.1334C22.886 10.1921 22.8168 10.2386 22.7406 10.2704C22.6644 10.3022 22.5826 10.3186 22.5 10.3186C22.4174 10.3186 22.3357 10.3022 22.2594 10.2704C22.1832 10.2386 22.114 10.1921 22.0559 10.1334C21.9978 10.0747 21.9519 10.0051 21.9208 9.92856C21.8898 9.85203 21.8742 9.7701 21.875 9.68752V8.125H20.3125C20.2299 8.1258 20.148 8.11022 20.0715 8.07917C19.9949 8.04812 19.9253 8.00221 19.8666 7.9441C19.808 7.88598 19.7614 7.81682 19.7296 7.74059C19.6978 7.66437 19.6814 7.5826 19.6814 7.50001C19.6814 7.41742 19.6978 7.33565 19.7296 7.25943C19.7614 7.1832 19.808 7.11403 19.8666 7.05592C19.9253 6.9978 19.9949 6.9519 20.0715 6.92085C20.148 6.8898 20.2299 6.87422 20.3125 6.87502H21.875V5.3125C21.8742 5.22992 21.8898 5.14799 21.9208 5.07146C21.9519 4.99493 21.9978 4.92532 22.0559 4.86664C22.114 4.80796 22.1832 4.76138 22.2594 4.72959C22.3357 4.6978 22.4174 4.68143 22.5 4.68143C22.5826 4.68143 22.6644 4.6978 22.7406 4.72959C22.8168 4.76138 22.886 4.80796 22.9441 4.86664C23.0022 4.92532 23.0481 4.99493 23.0792 5.07146C23.1102 5.14799 23.1258 5.22992 23.125 5.3125V6.87502H24.6875C24.8522 6.87661 25.0097 6.94316 25.1256 7.0602C25.2415 7.17723 25.3065 7.33529 25.3065 7.50001C25.3065 7.66473 25.2415 7.82279 25.1256 7.93982C25.0097 8.05686 24.8522 8.12341 24.6875 8.125H24.6875Z"
                                                            fill="currentColor" />
                                                    </svg>
                                                </button>
                                                @else
                                                    <button style="margin-left: 5%; width: 75% !important;"
                                                        class="btn btn-danger text-danger d-flex justify-content-center align-items-center w-50 fs-18 gap-2 fw-5 out-of-stock-text mx-auto">
                                                        {{ __('messages.whatsapp_stores.out_of_stock') }}
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        @include('whatsapp_stores.templates.order_modal')
        @include('whatsapp_stores.templates.cart_modal')
        <footer class="position-relative">
            <div class="container pt-3">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <img src="{{ $whatsappStore->logo_url }}" alt="logo" style="width: 50px !important; height: 50px !important;">
                        </div>
                        <div>
                            © Copyright {{ now()->year }} {{ env('APP_NAME') }}. All Rights Reserved.
                        </div>
                    </div>

                    <div class="col-lg-4 col-md-6 mb-2 pt-3 pt-sm-0">
                        <h5 class="fw-bold text-white mb-3">
                            <i class="fas fa-headset me-2 text-white"></i>{{ __('messages.whatsapp_stores.support_services') }}
                        </h5>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <a href="{{ route('whatsapp.store.show-terms-conditions', $whatsappStore->url_alias) }}"
                                target="_blank"
                                class="text-decoration-none text-white footer-link-hover">
                                    <i class="fas fa-file-contract me-2"></i>{{ __('messages.vcard.term_condition') }}
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="{{ route('whatsapp.store.show-privacy-policy', $whatsappStore->url_alias) }}"
                                target="_blank"
                                class="text-decoration-none text-white footer-link-hover">
                                    <i class="fas fa-shield-alt me-2"></i>{{ __('messages.vcard.privacy_policy') }}
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="{{ route('whatsapp.store.show-refund-cancellation', $whatsappStore->url_alias) }}"
                                target="_blank"
                                class="text-decoration-none text-white footer-link-hover">
                                    <i class="fas fa-undo-alt me-2"></i>{{ __('messages.vcard.refund_cancellation_policy') }}
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="{{ route('whatsapp.store.show-shipping-delivery', $whatsappStore->url_alias) }}"
                                target="_blank"
                                class="text-decoration-none text-white footer-link-hover">
                                    <i class="fas fa-shipping-fast me-2"></i>{{ __('messages.vcard.shipping_delivery_policy') }}
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="col-md-4">
                        <div class="fw-5 fs-16">
                            <h5 class="fw-bold text-white mb-3">
                                <i class="fas fa-comments me-2 text-white"></i>{{ __('messages.whatsapp_stores.talk_to_us') }}
                            </h5>
                            <div class="mb-2">
                                <i class="fas fa-map-marker-alt"></i> {{ $whatsappStore->address }}
                            </div>
                            <div>
                                <i class="fa-solid fa-phone"></i> +{{ $whatsappStore->region_code }} {{ $whatsappStore->whatsapp_no }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</body>
<script>
    @if ($whatsappStore->custom_js)
        {!! $whatsappStore->custom_js !!}
    @endif
</script>
<script>
    let vcardAlias = "{{ $whatsappStore->url_alias }}";
    let languageChange = "{{ url('whatsapp-stores/language') }}";
    let lang = "{{ checkLanguageSession($whatsappStore->url_alias) }}";
    let userlanguage = "{{ getLanguage($whatsappStore->default_language) }}"
    let isRtl = "{{ getLocalLanguage() == 'ar' || getLocalLanguage() == 'fa' ? 'true' : 'false' }}" === "true";
</script>
<script>
    let templateName = "ecommerce";
</script>
<script src="{{ asset('messages.js?$mixID') }}"></script>
<script src="{{ asset('assets/js/intl-tel-input/build/intlTelInput.js') }}"></script>
<script src="{{ asset('assets/js/vcard11/jquery.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('front/js/bootstrap.bundle.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/front-third-party-vcard11.js') }}"></script>
<script src="{{ mix('assets/js/custom/helpers.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/whatsapp_store_template.js') }}"></script>
<script src="{{ asset('assets/js/slider/js/slick.min.js') }}" type="text/javascript"></script>
<script>
    $(document).ready(function() {
        $(".product-slider").slick({
            infinite: true,
            slidesToShow: 4,
            rtl: isRtl,
            slidesToScroll: 1,
            autoplay: true,
            prevArrow: '<button class="slide-arrow prev-arrow"><svg xmlns="http://www.w3.org/2000/svg" width="7" height="12" viewBox="0 0 7 12" fill="none"><path d="M2.61048 5.99881L2.52357 5.91829L2.61048 6.01208L6.74799 10.4776C6.74801 10.4776 6.74802 10.4777 6.74804 10.4777C6.89859 10.6459 6.98199 10.8714 6.98011 11.1056C6.97822 11.3398 6.89118 11.5637 6.73792 11.7291C6.58468 11.8945 6.37755 11.9882 6.16119 11.9902C5.94487 11.9922 5.7363 11.9025 5.58044 11.7401C5.58042 11.74 5.58039 11.74 5.58037 11.74L0.851898 6.63663C0.696935 6.46933 0.609765 6.24231 0.609765 6.00545C0.609765 5.76859 0.696935 5.54156 0.851899 5.37426L5.58049 0.270777C5.73548 0.103552 5.94549 0.00976553 6.1643 0.00976555C6.3831 0.00976557 6.59311 0.103552 6.7481 0.270777L6.7481 0.270775C6.90306 0.438075 6.99023 0.665102 6.99023 0.901961C6.99023 1.13882 6.90306 1.36585 6.7481 1.53315L2.61048 5.99881Z" stroke="#141414" stroke-width="0.0195312"/></svg></button>',
            nextArrow: '<button class="slide-arrow next-arrow"><svg xmlns="http://www.w3.org/2000/svg" width="7" height="12" viewBox="0 0 7 12" fill="none"><path d="M4.38952 6.00119L4.47643 6.08171L4.38952 5.98792L0.252014 1.52238C0.251996 1.52236 0.251977 1.52234 0.251959 1.52232C0.101415 1.35406 0.0180061 1.12857 0.0198916 0.894392C0.0217773 0.660185 0.108825 0.43628 0.262083 0.270871C0.415319 0.105486 0.622448 0.0118285 0.838806 0.0098001C1.05513 0.00776977 1.2637 0.097502 1.41956 0.259938C1.41958 0.25996 1.41961 0.259983 1.41963 0.260006L6.1481 5.36337C6.30307 5.53067 6.39024 5.75769 6.39024 5.99455C6.39024 6.23141 6.30307 6.45844 6.1481 6.62574L1.41951 11.7292C1.26452 11.8964 1.05451 11.9902 0.835705 11.9902C0.616899 11.9902 0.406885 11.8964 0.251898 11.7292L0.2519 11.7292C0.0969359 11.5619 0.00976574 11.3349 0.00976578 11.098C0.00976582 10.8612 0.096936 10.6342 0.2519 10.4669L4.38952 6.00119Z" stroke="#2650D7" stroke-width="0.0195312"/></svg></button>',

            responsive: [{
                    breakpoint: 1199,
                    settings: {
                        slidesToShow: 3,
                    },
                },
                {
                    breakpoint: 860,
                    settings: {
                        slidesToShow: 2,
                    },
                },
                {
                    breakpoint: 575,
                    settings: {
                        slidesToShow: 1,
                        dots: false,
                        arrows: false,
                    },
                },
            ],
        });
    });
</script>
<script>
    $(document).ready(function() {
        $(".slider-for").slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            arrows: false,
            fade: true,
            dots: false,
            rtl: isRtl,
            asNavFor: ".slider-nav",
        });
        $(".slider-nav").slick({
            slidesToShow: 4,
            slidesToScroll: 1,
            asNavFor: ".slider-for",
            vertical: true,
            autoplay: true,
            infinite: true,
            dots: false,
            arrows: false,
            focusOnSelect: true,
            responsive: [{
                    breakpoint: 1129,
                    settings: {
                        slidesToShow: 3,
                    },
                },
                {
                    breakpoint: 991,
                    settings: {
                        slidesToShow: 5,
                    },
                },
                {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: 4,
                    },
                },
                {
                    breakpoint: 575,
                    settings: {
                        slidesToShow: 4,
                        vertical: false,
                        dots: true,
                    },
                },
                {
                    breakpoint: 460,
                    settings: {
                        slidesToShow: 3,
                        vertical: false,
                        dots: true,
                    },
                },
            ],
        });
    });
</script>
<script>
    let defaultCountryCodeValue = "{{ getSuperAdminSettingValue('default_country_code') }}"
</script>
<script>
$(document).ready(function() {
    function addAdvancedCursorZoom(selector, zoomLevel = 2) {
        $(selector).each(function() {
            const $img = $(this);
            const $container = $img.parent();
            let isZoomed = false;

            $container.addClass('zoom-container');
            $img.addClass('zoomable-image');

            $container.on('mouseenter', function(e) {
                isZoomed = true;
                $img.css({
                    'transform': `scale(${zoomLevel})`,
                    'transition': 'transform 0.3s ease'
                });
                updateTransformOrigin(e);
            });

            $container.on('mouseleave', function() {
                isZoomed = false;
                $img.css({
                    'transform': 'scale(1)',
                    'transform-origin': 'center center',
                    'transition': 'transform 0.3s ease, transform-origin 0.1s ease'
                });
            });

            $container.on('mousemove', function(e) {
                if (isZoomed) {
                    updateTransformOrigin(e);
                }
            });

            function updateTransformOrigin(e) {
                const rect = $container[0].getBoundingClientRect();
                const x = ((e.clientX - rect.left) / rect.width) * 100;
                const y = ((e.clientY - rect.top) / rect.height) * 100;

                $img.css({
                    'transform-origin': `${x}% ${y}%`,
                    'transition': 'transform 0.3s ease, transform-origin 0.1s ease'
                });
            }
        });
    }

    addAdvancedCursorZoom('.details-img img', 2.8);
    addAdvancedCursorZoom('.product-img img', 2.2);
    addAdvancedCursorZoom('.thumbnail-img img', 2);
});
</script>
</html>
