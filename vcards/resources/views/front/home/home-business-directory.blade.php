@extends(homePageLayout())
@section('title')
    {{ __('messages.business_directory') }}
@endsection
<link rel="stylesheet" href="{{ mix('assets/css/business_directory/business_directory.css') }}">
@section('content')
    <!-- start hero section -->
    <section class="hero-section pt-100 pb-60"
        @if (checkFrontLanguageSession() == 'ar' || checkFrontLanguageSession() == 'fa') dir="rtl" @endif>
        <div class="container pt-60 mt-5">
            <div class="row">
                <div class="col-12 text-center">
                    <h2 class="fs-40 text-white"> {{ __('messages.business_directory') }} </h2>
                </div>
            </div>
        </div>
    </section>
    <!-- end hero section -->
    <!--start Business Directory-section -->
    <div @if (checkFrontLanguageSession() == 'ar' || checkFrontLanguageSession() == 'fa') dir="rtl" @endif>
        @livewire('business-directory-list')
    </div>
    <!-- end Business Directory-section -->
@endsection
