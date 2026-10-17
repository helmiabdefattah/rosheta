{{-- Shared vendor form fields. Expects $vendor (nullable), $types. --}}
@php($v = $vendor ?? null)
<div>
    <label class="block text-xs text-slate-500 mb-1">{{ __('app.vendors.f.name') }} <span class="text-red-500">*</span></label>
    <input type="text" name="name" value="{{ old('name', $v?->name ?? '') }}" required class="w-full rounded-lg border px-3 py-2">
</div>
<div>
    <label class="block text-xs text-slate-500 mb-1">{{ __('app.vendors.f.type') }}</label>
    <select name="type" class="w-full rounded-lg border px-3 py-2">
        @foreach ($types as $t)
            <option value="{{ $t->value }}" @selected(old('type', $v?->type?->value ?? 'supplier') === $t->value)>{{ $t->label() }}</option>
        @endforeach
    </select>
</div>
<div class="grid grid-cols-2 gap-2">
    <div>
        <label class="block text-xs text-slate-500 mb-1">{{ __('app.vendors.f.phone') }}</label>
        <input type="text" name="phone" value="{{ old('phone', $v?->phone ?? '') }}" class="w-full rounded-lg border px-3 py-2">
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">{{ __('app.vendors.f.email') }}</label>
        <input type="email" name="email" value="{{ old('email', $v?->email ?? '') }}" class="w-full rounded-lg border px-3 py-2">
    </div>
</div>
<div>
    <label class="block text-xs text-slate-500 mb-1">{{ __('app.vendors.f.opening') }}</label>
    <input type="number" step="0.01" min="0" name="opening_balance" value="{{ old('opening_balance', $v?->opening_balance ?? '0') }}" class="w-full rounded-lg border px-3 py-2">
</div>
<div>
    <label class="block text-xs text-slate-500 mb-1">{{ __('app.vendors.f.notes') }}</label>
    <textarea name="notes" rows="2" class="w-full rounded-lg border px-3 py-2">{{ old('notes', $v?->notes ?? '') }}</textarea>
</div>
@isset($vendor)
    <label class="flex items-center gap-2 text-sm text-slate-600">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $vendor->is_active))> {{ __('app.vendors.active') }}
    </label>
@endisset
