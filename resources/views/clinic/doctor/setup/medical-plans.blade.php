@extends('clinic.layouts.app')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-slate-900">💊 {{ __('app.plan.title_plural') }}</h1>
    <a href="{{ route('practice.doctor.setup.index') }}"
       class="text-sm font-medium px-4 py-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700">
        ← {{ __('app.setup.title') }}
    </a>
</div>

{{-- Create a new plan --}}
<div class="bg-white rounded-xl shadow-sm p-5 mb-6">
    <h2 class="font-semibold text-slate-800 mb-4">{{ __('app.plan.new') }}</h2>
    <form method="POST" action="{{ route('practice.doctor.setup.medical-plans.store') }}">
        @csrf
        <div class="mb-3 max-w-md">
            <label class="block text-sm text-slate-500 mb-1">{{ __('app.plan.title_label') }}</label>
            <input type="text" name="title" value="{{ old('title') }}" required
                   class="w-full border rounded-lg px-3 py-2 text-sm">
            @error('title')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            @error('items')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        @include('clinic.partials.plan-items-editor', ['items' => old('items', [])])
        <button class="bg-purple-600 hover:bg-purple-700 text-white text-sm px-4 py-2 rounded-lg">{{ __('app.plan.save_button') }}</button>
    </form>
</div>

{{-- Existing plans --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-5 py-3 border-b border-slate-100 font-semibold text-slate-800">{{ __('app.plan.saved_plans') }}</div>
    <ul class="divide-y divide-slate-100">
        @forelse ($plans as $plan)
            <li class="px-5 py-3 flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <div class="font-medium text-slate-800">{{ $plan->title }}</div>
                    <div class="text-xs text-slate-400 truncate">
                        {{ $plan->items->pluck('medicine_name')->take(4)->join('، ') }}@if ($plan->items->count() > 4) …@endif
                        <span class="text-slate-300">·</span> {{ __('app.examine.medicines_count', ['count' => $plan->items->count()]) }}
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('practice.doctor.setup.medical-plans.edit', $plan) }}"
                       class="text-sm px-3 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-700">✏️ {{ __('app.plan.edit') }}</a>
                    <form method="POST" action="{{ route('practice.doctor.setup.medical-plans.destroy', $plan) }}"
                          onsubmit="return confirm('{{ __('app.plan.delete_confirm') }}')">
                        @csrf @method('DELETE')
                        <button class="text-sm px-3 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50">🗑 {{ __('app.plan.delete') }}</button>
                    </form>
                </div>
            </li>
        @empty
            <li class="px-5 py-10 text-center text-slate-400">{{ __('app.plan.empty') }}</li>
        @endforelse
    </ul>
</div>
@endsection
