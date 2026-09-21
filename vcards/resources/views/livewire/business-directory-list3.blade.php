<div class="min-vh-100">
    <div class="mb-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="{{ $filterType === 'vcard' || $filterType === 'whatsapp_store' ? 'col-xl-11' : 'col-xl-11' }} col-12">
                    <div class="bg-white bg-opacity-50 border border-light shadow-lg rounded-2xl p-07-1">
                        <div class="row g-3 align-items-center">
                            <div class="col-12 {{ $filterType === 'vcard' || $filterType === 'whatsapp_store' ? 'col-md-6 col-lg' : 'col-md' }}">
                                <input wire:model.live="searchName" type="text"
                                    class="form-control form-control-height form-control-lg rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-400 outline-none bg-white"
                                    placeholder="{{ __('messages.search') }}...">
                            </div>
                            <div class="col-12 {{ $filterType === 'vcard' || $filterType === 'whatsapp_store' ? 'col-md-6 col-lg' : 'col-md' }}">
                                <input wire:model.live="searchLocation" type="text"
                                    class="form-control form-control-height form-control-lg rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-400 outline-none bg-white"
                                    placeholder="{{ __('messages.search_by_location') }}...">
                            </div>

                            <div class="col-12 {{ $filterType === 'vcard' || $filterType === 'whatsapp_store' ? 'col-md-4 col-lg' : 'col-md-auto' }}">
                                <select wire:model.live="sortType"
                                    class="form-select form-select-lg rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-400 outline-none bg-white pr-35">
                                    <option value="recent">{{ __('messages.recent') }}</option>
                                    <option value="popular">{{ __('messages.popular') }}</option>
                                    <option value="trending">{{ __('messages.trending') }}</option>
                                </select>
                            </div>

                            <div class="col-12 {{ $filterType === 'vcard' || $filterType === 'whatsapp_store' ? 'col-md-4 col-lg' : 'col-md-auto' }}">
                                <select wire:model.live="filterType"
                                    class="form-select form-select-lg rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-400 outline-none bg-white pr-35">
                                    <option value="">{{ __('messages.all_type') }}</option>
                                    <option value="vcard">{{ __('messages.vcard.vcard') }}</option>
                                    <option value="whatsapp_store">{{ __('messages.common.whatsapp_store') }}</option>
                                </select>
                            </div>

                            @if ($filterType === 'vcard')
                                <div class="col-12 {{ $filterType === 'vcard' || $filterType === 'whatsapp_store' ? 'col-md-4 col-lg' : 'col-md-auto' }}">
                                    <select wire:model.live="filterTemplate"
                                        class="form-select form-select-lg rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-400 outline-none bg-white pr-35">
                                        <option value="">{{ __('messages.vcard.select_template') }}</option>

                                        @foreach ($this->groupedTemplates as $id => $template)
                                            <option value="{{ $id }}">{{ $template['label'] }}</option>
                                        @endforeach

                                    </select>
                                </div>
                            @endif

                            @if ($filterType === 'whatsapp_store')
                                <div class="col-12 {{ $filterType === 'vcard' || $filterType === 'whatsapp_store' ? 'col-md-4 col-lg' : 'col-md-auto' }}">
                                    <select wire:model.live="filterWpTemplate"
                                        class="form-select form-select-lg rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-400 outline-none bg-white pr-35">
                                        <option value="">{{ __('messages.vcard.select_template') }}</option>

                                        @foreach ($wpTemplateNames as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach

                                    </select>
                                </div>
                            @endif

                            <div class="col-12 {{ $filterType === 'vcard' || $filterType === 'whatsapp_store' ? 'col-md-12 col-lg-auto' : 'col-md-auto' }}">
                                <button
                                    class="w-100 w-md-auto inline-flex items-center justify-center px-6 py-07 bg-white text-primary-600 font-medium rounded-lg text-white bg-gradient-to-r from-primary-500 to-accent-500 hover:from-primary-600 hover:to-accent-600 shadow-lg transition-all duration-300 hover:shadow-primary-900/30 hover:scale-105 font-medium">
                                    {{ __('messages.search') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mx-auto px-4 px-sm-4 px-md-5 custom-lg-xl-padding mb-5" @if (checkFrontLanguageSession() == 'ar' || checkFrontLanguageSession() == 'fa') dir="rtl" @endif>
        <div class="row g-4">
            @forelse($businesses as $business)
                <div class="col-12 col-sm-6 col-md-6 col-lg-4 col-xl-3 mb-4">
                    <div class="card h-100 shadow-sm border-0 rounded-xl business-directory-card">
                        <a href="{{ $business['type'] == 'vcard' ? $business['url'] : route('whatsapp.store.show', $business['url_alias']) }}"
                            target="_blank" class="relative">
                            <img src="{{ $business['image_url'] }}" class="h-[230px] w-100 object-fit-cover" loading="lazy" alt="{{ $business['name'] }}">

                            <div class="position-absolute top-0 end-0 m-3">
                                @if ($business['type'] == 'vcard')
                                    <span
                                        class="bg-primary-100 text-primary-700 text-xs font-medium px-3 py-1 rounded-full">
                                        <i class="fas fa-id-card me-1"></i>
                                        {{ __('messages.vcard.vcard') }}
                                    </span>
                                @else
                                    <span
                                        class="bg-green-100 text-green-700 text-xs font-medium px-3 py-1 rounded-full">
                                        <i class="fab fa-whatsapp me-1"></i>
                                        {{ __('messages.common.whatsapp_store') }}
                                    </span>
                                @endif
                            </div>
                        </a>

                        <div class="card-body d-flex flex-column text-center px-07">
                            <a href="{{ $business['type'] == 'vcard' ? $business['url'] : route('whatsapp.store.show', $business['url_alias']) }}"
                                target="_blank" class="text-decoration-none">
                                <h5 class="text-lg font-semibold truncate">
                                    {{ $business['name'] }}
                                </h5>
                            </a>
                            <p class="text-gray-500 text-sm mt-2 truncate">
                                @if ($business['type'] === 'vcard')
                                    {{ \Illuminate\Support\Str::limit(strip_tags($business['description']), 120) }}
                                @else
                                    <i class="fa-solid fa-location-dot mr-1 text-blue-500"></i>
                                    {{ \Illuminate\Support\Str::limit(strip_tags($business['address']), 120) }}
                                @endif
                            </p>
                            <p class="card-text text-muted small mt-2">
                                @if ($business['type'] === 'vcard')
                                    {{ \Illuminate\Support\Str::limit(strip_tags($business['description']), 100) }}
                                @else
                                    <i class="fa-solid fa-location-dot me-1 text-primary"></i>
                                    {{ \Illuminate\Support\Str::limit(strip_tags($business['address']), 100) }}
                                @endif
                            </p>

                            <div class="mt-auto pt-3 d-flex flex-row align-items-center gap-2 justify-content-between flex-nowrap w-100 overflow-hidden" style="min-width: 0;">
                                <small class="text-sm text-muted d-flex align-items-center text-nowrap flex-shrink-0">
                                    <i class="bx bx-calendar me-1 text-blue-500"></i>
                                    {{ $business['created_at']->format('M d, Y') }}
                                </small>

                                <a href="{{ $business['type'] == 'vcard' ? $business['url'] : route('whatsapp.store.show', $business['url_alias']) }}"
                                    target="_blank"
                                    class="flex-shrink-0 inline-flex visit-btn-3 items-center bg-gradient-to-r from-primary-500 to-accent-500 text-white text-sm font-semibold
                                          px-3 py-2 rounded-full shadow-md hover:shadow-lg transition-all duration-300">
                                    <span class="text-nowrap">{{ __('messages.vcard.visit_store') }}</span>
                                    <span class="transition-transform duration-300 group-hover:translate-x-1 ms-1">
                                        <i class="fa-solid fa-arrow-right"></i>
                                    </span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-5 bg-white rounded-xl text-center items-center justify-center shadow-sm">
                        <div class="mb-4">
                            <div class="bg-primary bg-opacity-10 rounded-circle d-inline-flex align-items-center justify-content-center"
                                style="width:80px;height:80px;">
                                <i class="fa-solid fa-store fs-3 text-white"></i>
                            </div>
                        </div>
                        <h5 class="text-xl font-semibold text-gray-800 mb-2">
                            {{ __('messages.no_business_directory_found') }}
                        </h5>
                    </div>
                </div>
            @endforelse
        </div>
        <div class="mt-4 d-flex justify-content-center">
            <div class="pagination-fixed">
                {{ $businesses->links() }}
            </div>
        </div>
    </div>
</div>
