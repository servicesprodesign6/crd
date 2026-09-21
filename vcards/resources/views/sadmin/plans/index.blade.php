@extends('layouts.app')
@section('title')
    {{__('messages.plans')}}
@endsection
@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-column table-striped">
            @include('flash::message')
            <livewire:plan-table lazy/>
        </div>
    </div>
    @include('sadmin.plans.add_plan_tax_modal')
@endsection
