@extends('clinic.layouts.app')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">📊 {{ __('app.manager.title') }}</h1>
        <p class="text-slate-500 text-sm">@if ($clinic) {{ $clinic->name }} @endif</p>
    </div>
    <a href="{{ route('practice.doctor.dashboard') }}"
       class="text-sm font-medium px-4 py-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700">
        ← {{ __('app.manager.back_to_dashboard') }}
    </a>
</div>

@php
    $cards = [
        ['route' => 'practice.doctor.manager.collections',          'icon' => '💵', 'title' => __('app.manager.collections'),          'desc' => __('app.manager.collections_desc'),          'color' => 'emerald'],
        ['route' => 'practice.doctor.manager.patients',             'icon' => '🧾', 'title' => __('app.manager.patients_report'),      'desc' => __('app.manager.patients_desc'),             'color' => 'indigo'],
        ['route' => 'practice.doctor.manager.insurance-collections','icon' => '➕', 'title' => __('app.manager.insurance_collections'), 'desc' => __('app.manager.insurance_collections_desc'),'color' => 'cyan'],
        ['route' => 'practice.doctor.manager.insurance-report',     'icon' => '🛡', 'title' => __('app.manager.insurance_report'),      'desc' => __('app.manager.insurance_report_desc'),     'color' => 'violet'],
    ];
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    @foreach ($cards as $card)
        <a href="{{ route($card['route']) }}"
           class="block bg-white rounded-2xl shadow-sm hover:shadow-md transition p-6 border border-slate-100">
            <div class="flex items-start gap-4">
                <div class="text-4xl">{{ $card['icon'] }}</div>
                <div>
                    <h2 class="text-lg font-bold text-slate-900">{{ $card['title'] }}</h2>
                    <p class="text-sm text-slate-500 mt-1">{{ $card['desc'] }}</p>
                </div>
            </div>
        </a>
    @endforeach
</div>
@endsection
