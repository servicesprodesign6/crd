@extends('layouts.app')
@section('title')
    {{ __('messages.organization.organizations') }}
@endsection
@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-column table-striped">
            @include('flash::message')
            <livewire:organisation-table lazy />
        </div>
    </div>
    @include('users.changePassword')
@endsection
