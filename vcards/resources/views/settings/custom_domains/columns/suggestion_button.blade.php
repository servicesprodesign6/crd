<div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center ps-lg-3 mt-sm-3">
    <a type="button" class="btn btn-primary mb-2 mb-sm-0" target="_blank" href="{{ asset('custom_domain_guide/custom_domain_guide.pdf') }}">
        <i class="fas fa-download"></i>   {{ __('messages.custom_domain.cpanel_guide_download') }}
    </a>

    <a class="text-primary ms-sm-3" style="font-size:15px;" href="{{ route('setting.index', ['section' => 'custom_domain_guide']) }}">
        {{ __('messages.custom_domain.steps_before_approving_custom_domain') }}
    </a>
</div>
