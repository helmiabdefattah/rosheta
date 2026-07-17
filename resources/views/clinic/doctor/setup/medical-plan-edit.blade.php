@extends('clinic.layouts.app')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-slate-900">✏️ {{ __('app.plan.edit') }}</h1>
    <a href="{{ route('practice.doctor.setup.medical-plans') }}"
       class="text-sm font-medium px-4 py-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700">
        ← {{ __('app.plan.title_plural') }}
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm p-5 max-w-3xl">
    <form method="POST" action="{{ route('practice.doctor.setup.medical-plans.update', $plan) }}">
        @csrf @method('PUT')
        <div class="mb-3 max-w-md">
            <label class="block text-sm text-slate-500 mb-1">{{ __('app.plan.title_label') }}</label>
            <input type="text" name="title" value="{{ old('title', $plan->title) }}" required
                   class="w-full border rounded-lg px-3 py-2 text-sm">
            @error('title')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
            @error('items')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        @include('clinic.partials.plan-items-editor', ['items' => old('items', $plan->items)])
        <button class="bg-purple-600 hover:bg-purple-700 text-white text-sm px-4 py-2 rounded-lg">{{ __('app.plan.update_button') }}</button>
    </form>
</div>
@endsection
