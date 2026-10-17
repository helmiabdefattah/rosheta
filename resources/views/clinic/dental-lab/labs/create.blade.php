@extends('clinic.layouts.app')

@section('content')
    @include('clinic.dental-lab.partials._tabs', ['active' => 'labs'])

    <h2 class="font-bold text-slate-800 mb-4">{{ __('app.dental.lab.add') }}</h2>

    <form method="POST" action="{{ route('practice.dental-lab.labs.store') }}">
        @csrf
        @include('clinic.dental-lab.labs._form')
    </form>
@endsection
