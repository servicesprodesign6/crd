<div class="min-h-screen bg-gradient-to-br from-blue-200 via-indigo-100 to-purple-100">
    <section class="pt-10 pb-16 text-center relative">
        <div class="{{ $filterType === 'vcard' || $filterType === 'whatsapp_store' ? 'max-w-7xl' : 'max-w-7xl' }} mx-auto px-4 sm:px-6 md:px-8">
            <div
                class="backdrop-blur-xl bg-white/40 border border-white/50 shadow-xl rounded-2xl flex flex-col md:flex-row md:flex-wrap lg:flex-nowrap gap-3 md:gap-4
                       items-stretch md:items-center p-4 p-3">

                <input wire:model.live="searchName" type="text" placeholder="{{ __('messages.search') }}..."
                    class="w-full {{ $filterType === 'vcard' || $filterType === 'whatsapp_store' ? 'md:w-[calc(50%-0.5rem)] lg:flex-1' : 'md:flex-1' }} px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-400 outline-none bg-white">
                <input wire:model.live="searchLocation" type="text"
                    placeholder="{{ __('messages.search_by_location') }}..."
                    class="w-full {{ $filterType === 'vcard' || $filterType === 'whatsapp_store' ? 'md:w-[calc(50%-0.5rem)] lg:flex-1' : 'md:flex-1' }} px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-400 outline-none bg-white">

                <select wire:model.live="sortType"
                    class="w-full {{ $filterType === 'vcard' || $filterType === 'whatsapp_store' ? 'md:w-[calc(32.9%-0.5rem)] lg:flex-1' : 'md:w-auto lg:flex-none' }} px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-400 outline-none bg-white appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2024%2024%22%20stroke%3D%22%236B7280%22%3E%3Cpath%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%222%22%20d%3D%22m19%209-7%207-7-7%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.75rem_center] bg-[size:1.25rem_1.25rem] bg-no-repeat pr-10 cursor-pointer">
                    <option value="recent">{{ __('messages.recent') }}</option>
                    <option value="popular">{{ __('messages.popular') }}</option>
                    <option value="trending">{{ __('messages.trending') }}</option>
                </select>

                <select wire:model.live="filterType"
                    class="w-full {{ $filterType === 'vcard' || $filterType === 'whatsapp_store' ? 'md:w-[calc(32.9%-0.5rem)] lg:flex-1' : 'md:w-auto lg:flex-none' }} px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-400 outline-none bg-white appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2024%2024%22%20stroke%3D%22%236B7280%22%3E%3Cpath%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%222%22%20d%3D%22m19%209-7%207-7-7%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.75rem_center] bg-[size:1.25rem_1.25rem] bg-no-repeat pr-10 cursor-pointer">
                    <option value="">{{ __('messages.all_type') }}</option>
                    <option value="vcard">{{ __('messages.vcard.vcard') }}</option>
                    <option value="whatsapp_store">{{ __('messages.common.whatsapp_store') }}</option>
                </select>

                @if ($filterType === 'vcard')
                    <select wire:model.live="filterTemplate"
                        class="w-full {{ $filterType === 'vcard' || $filterType === 'whatsapp_store' ? 'md:w-[calc(32.9%-0.5rem)] lg:flex-1' : 'md:w-auto' }} px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-400 outline-none bg-white appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2024%2024%22%20stroke%3D%22%236B7280%22%3E%3Cpath%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%222%22%20d%3D%22m19%209-7%207-7-7%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.75rem_center] bg-[size:1.25rem_1.25rem] bg-no-repeat pr-10 cursor-pointer">
                        <option value="">{{ __('messages.vcard.select_template') }}</option>

                        @foreach ($this->groupedTemplates as $id => $template)
                            <option value="{{ $id }}">{{ $template['label'] }}</option>
                        @endforeach

                    </select>
                @endif

                @if ($filterType === 'whatsapp_store')
                    <select wire:model.live="filterWpTemplate"
                        class="w-full {{ $filterType === 'vcard' || $filterType === 'whatsapp_store' ? 'md:w-[calc(32.9%-0.5rem)] lg:flex-1' : 'md:w-auto' }} px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-400 outline-none bg-white appearance-none bg-[url('data:image/svg+xml;charset=utf-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20fill%3D%22none%22%20viewBox%3D%220%200%2024%2024%22%20stroke%3D%22%236B7280%22%3E%3Cpath%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%20stroke-width%3D%222%22%20d%3D%22m19%209-7%207-7-7%22%2F%3E%3C%2Fsvg%3E')] bg-[position:right_0.75rem_center] bg-[size:1.25rem_1.25rem] bg-no-repeat pr-10 cursor-pointer">

                        <option value="">{{ __('messages.vcard.select_template') }}</option>

                        @foreach ($wpTemplateNames as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach

                    </select>
                @endif

                <button
                    class="w-full {{ $filterType === 'vcard' || $filterType === 'whatsapp_store' ? 'md:w-full lg:w-auto lg:px-8' : 'md:w-auto lg:px-8' }} inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-[linear-gradient(45deg,#0f5edf_45%,#a4e0ff_100%)]
                           text-white text-sm font-semibold shadow-md hover:shadow-lg transition-all duration-300">
                    {{ __('messages.search') }}
                </button>
            </div>
        </div>
    </section>

    <section class="pb-20">
        <div class="mx-auto px-4 sm:px-6 md:px-10 lg:px-20 custom-lg-xl-padding">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 md:gap-8">
                @forelse($businesses as $business)
                    <div
                        class="bg-white rounded-2xl shadow-lg hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 ease-in-out overflow-hidden group">
                        <div class="h-48 sm:h-56 overflow-hidden relative">
                            <a href="{{ $business['type'] == 'vcard' ? $business['url'] : route('whatsapp.store.show', $business['url_alias']) }}"
                                target="_blank">
                                <img src="{{ $business['image_url'] }}"
                                    class="w-full h-full object-cover group-hover:scale-110 transition duration-500"
                                    loading="lazy" alt="{{ $business['name'] }}">
                            </a>
                            <div class="absolute top-3 right-3">
                                @if ($business['type'] == 'vcard')
                                    <span
                                        class="bg-blue-100 text-blue-700 text-xs font-semibold px-3 py-1 rounded-full">
                                        <i class="fas fa-id-card mr-1"></i> {{ __('messages.vcard.vcard') }}
                                    </span>
                                @else
                                    <span
                                        class="bg-green-100 text-green-700 text-xs font-semibold px-3 py-1 rounded-full">
                                        <i class="fab fa-whatsapp mr-1"></i> {{ __('messages.common.whatsapp_store') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="py-4 px-3 text-center">
                            <a href="{{ $business['type'] == 'vcard' ? $business['url'] : route('whatsapp.store.show', $business['url_alias']) }}"
                                target="_blank">
                                <h3 class="text-lg font-semibold text-gray-800 hover:text-blue-600 truncate">
                                    {{ $business['name'] }}
                                </h3>
                            </a>
                            <p class="text-gray-500 text-sm mt-2 truncate min-h-5">
                                @if ($business['type'] === 'vcard')
                                    {{ \Illuminate\Support\Str::limit(strip_tags($business['description']), 120) }}
                                @else
                                    <i class="fa-solid fa-location-dot mr-1 text-blue-500"></i>
                                    {{ \Illuminate\Support\Str::limit(strip_tags($business['address']), 120) }}
                                @endif
                            </p>
                            <div class="mt-5 flex items-center justify-between gap-1 flex-nowrap">
                                <span class="text-sm text-gray-400 whitespace-nowrap">
                                    <i class="bx bx-calendar text-blue-500"></i>
                                    {{ $business['created_at']->format('M d, Y') }}
                                </span>

                                <a href="{{ $business['type'] == 'vcard' ? $business['url'] : route('whatsapp.store.show', $business['url_alias']) }}"
                                    target="_blank"
                                    class="whitespace-nowrap inline-flex items-center justify-center gap-2 bg-[linear-gradient(43deg,#0f5edf_50%,#a4e0ff_100%)] text-white text-sm font-semibold
                                        px-4 py-2 rounded-full shadow-md hover:shadow-lg transition-all duration-300 shrink-0">
                                    {{ __('messages.vcard.visit_store') }}
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full flex flex-col items-center justify-center py-16 sm:py-20">
                        <div
                            class="w-20 h-20 flex items-center justify-center rounded-full bg-gradient-to-br from-[#0f5edf]/20 to-[#a4e0ff]/20 mb-6">
                            <i class="fa-solid fa-store text-3xl text-[#0f5edf]"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">
                            {{ __('messages.no_business_directory_found') }}
                        </h3>
                    </div>
                @endforelse
            </div>
            <div class="mt-14 flex justify-center">
                {{ $businesses->links() }}
            </div>
        </div>
    </section>
</div>
