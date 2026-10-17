@extends('clinic.layouts.app')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-slate-900">➕ {{ __('app.expenses.new') }}</h1>
    <a href="{{ route('practice.doctor.expenses.index') }}" class="btn btn-secondary btn-sm">← {{ __('app.expenses.title') }}</a>
</div>

@include('clinic.expenses.partials.form', [
    'action' => route('practice.doctor.expenses.store'),
    'method' => 'POST',
])
@endsection
