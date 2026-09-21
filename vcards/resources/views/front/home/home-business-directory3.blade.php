@extends('front.layouts.app3')
@section('title')
{{ __('messages.business_directory') }}
@endsection
@section('content')
    <style>
        .pagination .page-link:hover {
            color: #ffffff !important;
        }
        .pagination-fixed {
            max-width: 100%;
            overflow-x: auto;
        }
        .pr-35 {
            padding-right: 35px !important;
        }
        .p-07-1 {
            padding: 0.75rem 1rem !important;
        }
        .form-control-height {
            height: 0 !important;
        }
        .py-07 {
            padding-top: 0.75rem !important;
            padding-bottom: 0.75rem !important;
        }
        .px-07 {
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
        }

        .business-directory-card .card-body .visit-btn-3 {
            font-size: 14px;
        }

        @media (min-width: 992px) {
            .custom-lg-xl-padding {
                padding-left: 5rem !important;
                padding-right: 5rem !important;
            }
        }

        @media (min-width: 1400px) {
            .custom-lg-xl-padding {
                padding-left: 8rem !important;
                padding-right: 8rem !important;
            }
        }
    </style>
    <div class="bg-gradient-to-br from-primary-600 to-accent-600">
        <section class="relative pt-12 pb-12 text-white overflow-hidden" @if (checkFrontLanguageSession() == 'ar' || checkFrontLanguageSession() == 'fa') dir="rtl" @endif>
            <div class="mx-auto px-4 relative z-10">
                <div class="max-w-4xl mx-auto text-center">
                    <h1
                        class="text-4xl md:text-5xl font-bold mb-1 bg-clip-text blog-title">
                        {{ __('messages.business_directory') }}
                    </h1>
                </div>
            </div>
        </section>

        <section class="pt-4 pb-16">
            <div class="mx-auto">
                <div class="relative">
                    @livewire('business-directory-list3')
                </div>
            </div>
        </section>
    </div>
@endsection
