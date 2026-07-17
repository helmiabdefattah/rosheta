@extends('clinic.layouts.app')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">🧩 {{ __('app.setup.title') }}</h1>
        <p class="text-slate-500 text-sm">{{ __('app.setup.subtitle') }}</p>
    </div>
    <a href="{{ route('practice.doctor.dashboard') }}"
       class="text-sm font-medium px-4 py-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700">
        ← {{ __('app.manager.back_to_dashboard') }}
    </a>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <a href="{{ route('practice.doctor.setup.medical-plans') }}"
       class="block bg-white rounded-2xl shadow-sm hover:shadow-md transition p-6 border border-slate-100">
        <div class="flex items-start gap-4">
            <div class="text-4xl">💊</div>
            <div>
                <h2 class="text-lg font-bold text-slate-900">{{ __('app.plan.title_plural') }}</h2>
                <p class="text-sm text-slate-500 mt-1">{{ __('app.plan.desc') }}</p>
                <p class="text-xs text-slate-400 mt-2">{{ __('app.plan.count', ['count' => $plansCount]) }}</p>
            </div>
        </div>
    </a>
    <a href="{{ route('practice.doctor.setup.examination-fields') }}"
       class="block bg-white rounded-2xl shadow-sm hover:shadow-md transition p-6 border border-slate-100">
        <div class="flex items-start gap-4">
            <div class="text-4xl">🧾</div>
            <div>
                <h2 class="text-lg font-bold text-slate-900">{{ __('app.field.title_plural') }}</h2>
                <p class="text-sm text-slate-500 mt-1">{{ __('app.field.desc') }}</p>
                <p class="text-xs text-slate-400 mt-2">{{ __('app.field.count', ['count' => $fieldsCount]) }}</p>
            </div>
        </div>
    </a>
</div>
@endsection
