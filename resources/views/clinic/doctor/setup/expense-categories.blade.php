@extends('clinic.layouts.app')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">🏷 {{ __('app.expenses.categories') }}</h1>
        <p class="text-slate-500 text-sm">@if ($clinic) {{ $clinic->name }} @endif</p>
    </div>
    <a href="{{ route('practice.doctor.setup.index') }}" class="btn btn-secondary btn-sm">← {{ __('app.setup.title') }}</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    {{-- Add --}}
    <form method="POST" action="{{ route('practice.doctor.setup.expense-categories.store') }}" class="card p-5 space-y-3">
        @csrf
        <h2 class="font-semibold text-slate-800">{{ __('app.expenses.category_new') }}</h2>
        <div>
            <label class="block text-xs text-slate-500 mb-1">{{ __('app.expenses.f.title') }}</label>
            <input type="text" name="name" required class="w-full rounded-lg border px-3 py-2">
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">{{ __('app.expenses.category_icon') }}</label>
            <input type="text" name="icon" maxlength="16" placeholder="🗂" class="w-full rounded-lg border px-3 py-2">
        </div>
        <button class="btn btn-primary btn-block">{{ __('app.expenses.f.save') }}</button>
    </form>

    {{-- List --}}
    <div class="card p-5 lg:col-span-2">
        @if ($categories->isEmpty())
            <p class="text-sm text-slate-400">{{ __('app.expenses.empty') }}</p>
        @else
            <div class="divide-y">
                @foreach ($categories as $cat)
                    <div class="py-3 flex flex-wrap items-center gap-2">
                        <form method="POST" action="{{ route('practice.doctor.setup.expense-categories.update', $cat) }}" class="flex flex-wrap items-center gap-2 flex-1">
                            @csrf @method('PUT')
                            <input type="text" name="icon" value="{{ $cat->icon }}" maxlength="16" class="w-14 rounded-lg border px-2 py-1 text-center">
                            <input type="text" name="name" value="{{ $cat->name }}" required class="flex-1 min-w-[8rem] rounded-lg border px-3 py-1">
                            <label class="flex items-center gap-1 text-xs text-slate-500">
                                <input type="checkbox" name="is_active" value="1" @checked($cat->is_active)> {{ __('app.expenses.active') }}
                            </label>
                            <span class="text-xs text-slate-400">{{ __('app.expenses.used_count', ['count' => $cat->expenses_count]) }}</span>
                            <button class="btn btn-secondary btn-sm">{{ __('app.expenses.f.save') }}</button>
                        </form>
                        <form method="POST" action="{{ route('practice.doctor.setup.expense-categories.destroy', $cat) }}"
                              onsubmit="return confirm('{{ __('app.dental.actions.confirm_delete') }}')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline text-xs">{{ __('app.expenses.f.delete') }}</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
