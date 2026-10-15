@php $lab = $lab ?? null; $inp = 'w-full border rounded-lg px-3 py-2 text-sm'; $lbl = 'block text-sm font-semibold text-slate-700 mb-1'; @endphp

<div class="card p-6 max-w-2xl space-y-4">
    <div>
        <label class="{{ $lbl }}">{{ __('app.dental.lab.name') }} <span class="text-red-500">*</span></label>
        <input type="text" name="name" value="{{ old('name', $lab->name ?? '') }}" required class="{{ $inp }}">
    </div>
    <div class="grid sm:grid-cols-2 gap-4">
        <div>
            <label class="{{ $lbl }}">{{ __('app.dental.lab.phone') }}</label>
            <input type="text" name="phone" value="{{ old('phone', $lab->phone ?? '') }}" class="{{ $inp }}" dir="ltr">
        </div>
        <div>
            <label class="{{ $lbl }}">{{ __('app.dental.lab.status') }}</label>
            <label class="flex items-center gap-2 mt-2">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $lab->is_active ?? true)) class="w-5 h-5 rounded">
                <span class="text-sm text-slate-700">{{ __('app.dental.common.active') }}</span>
            </label>
        </div>
    </div>
    <div>
        <label class="{{ $lbl }}">{{ __('app.dental.lab.address') }}</label>
        <input type="text" name="address" value="{{ old('address', $lab->address ?? '') }}" class="{{ $inp }}">
    </div>
    <div>
        <label class="{{ $lbl }}">{{ __('app.dental.lab.notes') }}</label>
        <textarea name="notes" rows="3" class="{{ $inp }}">{{ old('notes', $lab->notes ?? '') }}</textarea>
    </div>

    <div class="flex gap-2 pt-2">
        <button class="btn btn-primary">{{ __('app.dental.actions.save') }}</button>
        <a href="{{ route('practice.dental-lab.labs.index') }}" class="btn btn-secondary">{{ __('app.dental.actions.cancel') }}</a>
    </div>
</div>
