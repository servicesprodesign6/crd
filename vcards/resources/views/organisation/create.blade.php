@extends('layouts.app')
@section('title')
    {{ __('messages.organization.add_organization') }}
@endsection
@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-column">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-end mb-5">
                    <h1>{{ __('messages.organization.add_organization') }}</h1>
                    <a class="btn btn-outline-primary float-end" href="{{ route('organisation.index') }}">
                        {{ __('messages.common.back') }}
                    </a>
                </div>

                <div class="col-12">
                    @include('layouts.errors')
                </div>
                <div class="card">
                    <div class="card-body">
                        {!! Form::open(['route' => 'organisation.store', 'files' => 'true', 'id' => 'userCreateForm']) !!}
                        @include('organisation.fields')
                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
