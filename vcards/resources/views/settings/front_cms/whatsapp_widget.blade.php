@extends('layouts.app')
@section('title')
    {{__('messages.front_cms.whatsapp_widget')}}
@endsection
@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-column">
            @include('flash::message')
            @include('layouts.errors')
            <div class="card">
                <div class="card-body d-md-flex">
                    @include('settings.front_cms.front_cms_menu')
                    <div class="flex-grow-1 ms-md-5 mt-5 mt-md-0">
                        {!! Form::open(['route' => 'setting.whatsapp.widget.update', 'id' => 'whatsappWidgetForm']) !!}
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="border mb-5 p-6 rounded-3 bg-light">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <div class="p-3 bg-body rounded-3 me-4 shadow-sm d-flex align-items-center justify-content-center border"
                                                style="width: 50px; height: 50px;">
                                                <i class="fab fa-whatsapp text-success fs-1"></i>
                                            </div>
                                            <div>
                                                <h5 class="mb-0 fw-bolder text-gray-900">
                                                    {{ __('messages.front_cms.whatsapp_widget') }}
                                                </h5>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch ps-0">
                                            <input class="form-check-input ms-0" type="checkbox" name="whatsapp_widget_show"
                                                id="whatsappWidgetShow" {{ isset($setting['whatsapp_widget_show']) && $setting['whatsapp_widget_show'] ? 'checked' : '' }}
                                                style="width: 45px; height: 22px; cursor: pointer;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12" id="whatsappWidgetMessageContainer"
                                style="{{ isset($setting['whatsapp_widget_show']) && $setting['whatsapp_widget_show'] ? '' : 'display:none;' }}">
                                <div class="mb-5">
                                    <label class="form-label fw-bolder fs-5 required mb-1 text-gray-900">{{ __('messages.common.message') }}</label>
                                    <div class="text-gray-600 fs-7 mb-4">{{ __('messages.front_cms.wp_msg_description') }}</div>
                                    <div id="whatsappWidgetEditor" style="height: 200px" class="bg-body rounded border">
                                        {!! $setting['whatsapp_widget_message'] ?? '' !!}
                                    </div>
                                    {{ Form::hidden('whatsapp_widget_message', $setting['whatsapp_widget_message'] ?? null, ['id' => 'whatsappWidgetData']) }}
                                </div>
                            </div>
                            <div class="d-flex">
                                {{ Form::submit(__('messages.common.save'), ['class' => 'btn btn-primary me-3']) }}
                            </div>
                        </div>
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            if (!$('#whatsappWidgetEditor').length) {
                return false;
            }
            let quill = new Quill('#whatsappWidgetEditor', {
                modules: {
                    toolbar: [
                        [{ header: [1, 2, 3, 4, 5, 6, false] }],
                        ["bold", "italic", "underline", "strike"],
                        ["blockquote", "code-block"],
                        [{ list: "ordered" }, { list: "bullet" }],
                        [{ script: "sub" }, { script: "super" }],
                        [{ indent: "-1" }, { indent: "+1" }],
                        [{ direction: "rtl" }],
                        [{ color: [] }, { background: [] }],
                        [{ font: [] }],
                        [{ align: [] }],
                    ],
                },
                theme: 'snow',
                placeholder: "{{ __('messages.front.enter_your_message') }}..."
            });

            $('#whatsappWidgetShow').change(function () {
                if ($(this).is(':checked')) {
                    $('#whatsappWidgetMessageContainer').show();
                } else {
                    $('#whatsappWidgetMessageContainer').hide();
                }
            });

            $('#whatsappWidgetForm').submit(function (e) {
                if ($('#whatsappWidgetShow').is(':checked')) {
                    if (quill.getText().trim().length === 0) {
                        displayErrorMessage(Lang.get('js.message_required'));
                        e.preventDefault();
                        return false;
                    }
                }
                $('#whatsappWidgetData').val(JSON.stringify(quill.root.innerHTML));
            });
        });
    </script>
@endpush