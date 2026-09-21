@extends('layouts.app')
@section('title')
    {{ 'Storage' }}
@endsection
@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column table-striped">
        @include('flash::message')
    </div>
    <div class="row g-4">
        <!-- Storage Overview -->
        <div class="col-sm-6">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title border-bottom pb-3">{{ __('messages.storage_overview') }}</h5>
                    <div class="mt-10">
                        <canvas id="storagePieChart" data-chart-data="{{ json_encode($chartData['data']) }}" data-chart-labels="{{ json_encode($chartData['labels']) }}" style="height: 300px"></canvas>
                    </div>
                    <div class="mt-10">
                        <!-- Legend -->
                        <div class="d-flex flex-wrap justify-content-center gap-4">
                            <div style="min-width:175px;">
                                <span class="px-5 py-1 rounded-2 bg-primary"></span>
                                <span class="mx-3">{{ __('messages.used_storage') }}</span>
                            </div>
                            <div style="min-width:175px;">
                                <span style="background-color: #C1C6FF;" class="px-5 py-1 rounded-2 blur-bg "></span>
                                <span class="mx-3">{{ __('messages.unused_storage') }}</span>
                            </div>
                        </div>
                        <!-- Storage Usage Progress Bar -->
                        <div class="mt-10 text-end">
                            {{ intval($userLimit) }} {{ __('messages.mb') }} / {{ $storageLimit }} {{ __('messages.mb') }}
                        </div>
                        <div class="progress mt-7">
                            <div class="progress-bar" role="progressbar" style="width: {{ ($userLimit / $storageLimit) * 100 }}%;" aria-valuenow="{{ $userLimit }}" aria-valuemin="0" aria-valuemax="{{ $storageLimit }}"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Storage Used Details -->
        <div class="col-12 col-sm-6">
            <div class="card h-100">
                <div class="card-body">
                    <!-- Storage Used Details -->
                    <h5 class="card-title border-bottom pb-3">{{ __('messages.storage_used') }}</h5>
                    <!-- Product Storage -->
                    <h5 class="card-title mt-5 mb-2">{{ __('messages.vcards') }}</h5>
                    <dl class="storage-list mb-0">
                        <div class="storage-row">
                            <dt>{{ __('messages.vcard.products') }}</dt>
                            <dd>
                                <span class="storage-size">{{ number_format($productStorageMB, 2) }} {{ __('messages.mb') }}</span>
                                <span class="storage-pct">{{ number_format(($productStorageMB / $storageLimit) * 100, 2) }}%</span>
                            </dd>
                        </div>
                        <div class="storage-row">
                            <dt>{{ __('messages.vcard.services') }}</dt>
                            <dd>
                                <span class="storage-size">{{ number_format($serviceStorageMB, 2) }} {{ __('messages.mb') }}</span>
                                <span class="storage-pct">{{ number_format(($serviceStorageMB / $storageLimit) * 100, 2) }}%</span>
                            </dd>
                        </div>
                        <div class="storage-row">
                            <dt>{{ __('messages.vcard.testimonials') }}</dt>
                            <dd>
                                <span class="storage-size">{{ number_format($testimonialStorageMB, 2) }} {{ __('messages.mb') }}</span>
                                <span class="storage-pct">{{ number_format(($testimonialStorageMB / $storageLimit) * 100, 2) }}%</span>
                            </dd>
                        </div>
                        <div class="storage-row">
                            <dt>{{ __('messages.social_icon') }}</dt>
                            <dd>
                                <span class="storage-size">{{ number_format($socialStorageMB, 2) }} {{ __('messages.mb') }}</span>
                                <span class="storage-pct">{{ number_format(($socialStorageMB / $storageLimit) * 100, 2) }}%</span>
                            </dd>
                        </div>
                        <div class="storage-row">
                            <dt>{{ __('messages.vcard.blogs') }}</dt>
                            <dd>
                                <span class="storage-size">{{ number_format($blogStorageMB, 2) }} {{ __('messages.mb') }}</span>
                                <span class="storage-pct">{{ number_format(($blogStorageMB / $storageLimit) * 100, 2) }}%</span>
                            </dd>
                        </div>
                        <div class="storage-row">
                            <dt>{{ __('messages.vcard.gallery') }}</dt>
                            <dd>
                                <span class="storage-size">{{ number_format($galleryStorageMB, 2) }} {{ __('messages.mb') }}</span>
                                <span class="storage-pct">{{ number_format(($galleryStorageMB / $storageLimit) * 100, 2) }}%</span>
                            </dd>
                        </div>
                        <div class="storage-row">
                            <dt>{{ __('messages.profile_and_cover') }}</dt>
                            <dd>
                                <span class="storage-size">{{ number_format($profileStorageMB, 2) }} {{ __('messages.mb') }}</span>
                                <span class="storage-pct">{{ number_format(($profileStorageMB / $storageLimit) * 100, 2) }}%</span>
                            </dd>
                        </div>
                    </dl>

                    <h5 class="card-title mt-4 mb-2">{{ __('messages.common.whatsapp_store') }}</h5>
                    <dl class="storage-list mb-0">
                        <div class="storage-row">
                            <dt>{{ __('messages.whatsapp_stores.products') }}</dt>
                            <dd>
                                <span class="storage-size">{{ number_format($productWhatsappStoreStorageMB, 2) }} {{ __('messages.mb') }}</span>
                                <span class="storage-pct">{{ number_format(($productWhatsappStoreStorageMB / $storageLimit) * 100, 2) }}%</span>
                            </dd>
                        </div>
                        <div class="storage-row">
                            <dt>{{ __('messages.product_category') }}</dt>
                            <dd>
                                <span class="storage-size">{{ number_format($productCategoriesWhatsappStoreStorageMB, 2) }} {{ __('messages.mb') }}</span>
                                <span class="storage-pct">{{ number_format(($productCategoriesWhatsappStoreStorageMB / $storageLimit) * 100, 2) }}%</span>
                            </dd>
                        </div>
                        <div class="storage-row">
                            <dt>{{ __('messages.profile_and_cover') }}</dt>
                            <dd>
                                <span class="storage-size">{{ number_format($profileWhatsappStoreStorageMB, 2) }} {{ __('messages.mb') }}</span>
                                <span class="storage-pct">{{ number_format(($profileWhatsappStoreStorageMB / $storageLimit) * 100, 2) }}%</span>
                            </dd>
                        </div>
                    </dl>

                    <!-- User Settings Details -->
                    <h5 class="card-title mt-4 mb-2">{{ __('messages.user.setting') }}</h5>
                    <dl class="storage-list mb-0">
                        <div class="storage-row">
                            <dt>{{ __('messages.pwa.pwa') }}</dt>
                            <dd>
                                <span class="storage-size">{{ number_format($pwaStorageMB, 2) }} {{ __('messages.mb') }}</span>
                                <span class="storage-pct">{{ number_format(($pwaStorageMB / $storageLimit) * 100, 2) }}%</span>
                            </dd>
                        </div>
                        <div class="storage-row">
                            <dt>{{ __('messages.user.avatar') }}</dt>
                            <dd>
                                <span class="storage-size">{{ number_format($avatarStorageMB, 2) }} {{ __('messages.mb') }}</span>
                                <span class="storage-pct">{{ number_format(($avatarStorageMB / $storageLimit) * 100, 2) }}%</span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .storage-row {
        display: flex;
        align-items: baseline;
        flex-wrap: wrap;
        gap: 0.25rem 0.5rem;
        padding: 0.45rem 0;
    }

    .storage-row dt {
        flex: 1 1 0;
        min-width: 0;
        font-weight: normal;
        word-break: break-word;
        color: #6c757d;
    }

    .storage-row dd {
        flex: 0 0 auto;
        margin: 0;
        display: flex;
        gap: 4rem;
        white-space: nowrap;
        color: #6c757d;

        @media (max-width: 768px) {
            gap: 1rem;
        }
    }

    .storage-size, .storage-pct {
        min-width: 5ch;
        text-align: right;
    }
</style>
@endsection
