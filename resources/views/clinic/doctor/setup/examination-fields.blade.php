@extends('clinic.layouts.app')

@section('content')
@php
    $types = ['text', 'select', 'number', 'percentage', 'file'];
@endphp
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-slate-900">🧾 {{ __('app.field.title_plural') }}</h1>
    <a href="{{ route('practice.doctor.setup.index') }}"
       class="text-sm font-medium px-4 py-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700">
        ← {{ __('app.setup.title') }}
    </a>
</div>

{{-- Add a field --}}
<div class="bg-white rounded-xl shadow-sm p-5 mb-6">
    <h2 class="font-semibold text-slate-800 mb-4">{{ __('app.field.new') }}</h2>
    <form method="POST" action="{{ route('practice.doctor.setup.examination-fields.store') }}"
          class="flex flex-wrap items-end gap-3">
        @csrf
        <div>
            <label class="block text-xs text-slate-500 mb-1">{{ __('app.field.label') }}</label>
            <input type="text" name="label" value="{{ old('label') }}" required class="w-48 border rounded px-2 py-1.5 text-sm">
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">{{ __('app.field.type') }}</label>
            <select name="type" class="border rounded px-2 py-1.5 text-sm" onchange="this.closest('form').querySelector('.opts').classList.toggle('hidden', this.value !== 'select')">
                @foreach ($types as $t)
                    <option value="{{ $t }}" @selected(old('type') === $t)>{{ __('app.field.types.'.$t) }}</option>
                @endforeach
            </select>
        </div>
        <div class="opts {{ old('type') === 'select' ? '' : 'hidden' }}">
            <label class="block text-xs text-slate-500 mb-1">{{ __('app.field.options') }}</label>
            <input type="text" name="options" value="{{ old('options') }}" placeholder="{{ __('app.field.options_placeholder') }}"
                   class="w-64 border rounded px-2 py-1.5 text-sm">
        </div>
        <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg">{{ __('app.field.add_button') }}</button>
    </form>
    @error('label')<p class="text-red-600 text-xs mt-2">{{ $message }}</p>@enderror
    @error('options')<p class="text-red-600 text-xs mt-2">{{ $message }}</p>@enderror
</div>

{{-- Existing fields --}}
<div class="bg-white rounded-xl shadow-sm overflow-hidden">
    <div class="px-5 py-3 border-b border-slate-100 font-semibold text-slate-800">{{ __('app.field.defined') }}</div>
    <ul class="divide-y divide-slate-100">
        @forelse ($fields as $field)
            <li class="px-5 py-3">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <span class="font-medium text-slate-800">{{ $field->label }}</span>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 ms-2">{{ __('app.field.types.'.$field->type) }}</span>
                        @unless ($field->is_active)
                            <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 ms-1">{{ __('app.field.inactive') }}</span>
                        @endunless
                        @if ($field->type === 'select')
                            <div class="text-xs text-slate-400 mt-0.5">{{ $field->options }}</div>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="button" onclick="toggle('field-edit-{{ $field->id }}')"
                                class="text-sm px-3 py-1.5 rounded-lg border border-slate-300 hover:bg-slate-50 text-slate-700">✏️ {{ __('app.plan.edit') }}</button>
                        <form method="POST" action="{{ route('practice.doctor.setup.examination-fields.destroy', $field) }}"
                              onsubmit="return confirm('{{ __('app.field.delete_confirm') }}')">
                            @csrf @method('DELETE')
                            <button class="text-sm px-3 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50">🗑</button>
                        </form>
                    </div>
                </div>

                {{-- Inline edit --}}
                <div id="field-edit-{{ $field->id }}" class="hidden mt-3 pt-3 border-t border-slate-100">
                    <form method="POST" action="{{ route('practice.doctor.setup.examination-fields.update', $field) }}"
                          class="flex flex-wrap items-end gap-3">
                        @csrf @method('PUT')
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">{{ __('app.field.label') }}</label>
                            <input type="text" name="label" value="{{ $field->label }}" required class="w-48 border rounded px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">{{ __('app.field.type') }}</label>
                            <select name="type" class="border rounded px-2 py-1.5 text-sm"
                                    onchange="this.closest('form').querySelector('.opts').classList.toggle('hidden', this.value !== 'select')">
                                @foreach ($types as $t)
                                    <option value="{{ $t }}" @selected($field->type === $t)>{{ __('app.field.types.'.$t) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="opts {{ $field->type === 'select' ? '' : 'hidden' }}">
                            <label class="block text-xs text-slate-500 mb-1">{{ __('app.field.options') }}</label>
                            <input type="text" name="options" value="{{ $field->options }}" class="w-64 border rounded px-2 py-1.5 text-sm">
                        </div>
                        <label class="flex items-center gap-1.5 text-sm text-slate-600">
                            <input type="checkbox" name="is_active" value="1" @checked($field->is_active)> {{ __('app.field.active') }}
                        </label>
                        <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg">{{ __('app.plan.update_button') }}</button>
                    </form>
                </div>
            </li>
        @empty
            <li class="px-5 py-10 text-center text-slate-400">{{ __('app.field.empty') }}</li>
        @endforelse
    </ul>
</div>
@endsection
