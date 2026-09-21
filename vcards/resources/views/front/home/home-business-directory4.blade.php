@extends('front.layouts.app4')
@section('title')
{{ __('messages.business_directory') }}
@endsection
@section('content')
    <style>
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
    <div class="min-h-screen bg-gradient-to-br from-blue-200 via-indigo-100 to-purple-100">
        <section class="pt-28 pb-8 text-center" @if (checkFrontLanguageSession() == 'ar' || checkFrontLanguageSession() == 'fa') dir="rtl" @endif>
            <div class="max-w-4xl mx-auto px-4">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-800">
                    {{ __('messages.business_directory') }}
                </h1>
            </div>
        </section>
        <section>
            <div class="mx-auto">
                @livewire('business-directory-list4')
            </div>
        </section>
    </div>
@endsection
