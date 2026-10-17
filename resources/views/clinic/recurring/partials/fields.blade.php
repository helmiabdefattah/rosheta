{{-- Shared recurring-expense fields. Expects $template (nullable), $categories, $vendors, $intervals. --}}
@php($t = $template ?? null)
<div>
    <label class="block text-xs text-slate-500 mb-1">{{ __('app.expenses.f.title') }} <span class="text-red-500">*</span></label>
    <input type="text" name="title" value="{{ old('title', $t?->title ?? '') }}" required class="w-full rounded-lg border px-3 py-2">
</div>
<div class="grid grid-cols-2 gap-2">
    <div>
        <label class="block text-xs text-slate-500 mb-1">{{ __('app.expenses.f.amount') }} <span class="text-red-500">*</span></label>
        <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount', $t?->amount ?? '') }}" required class="w-full rounded-lg border px-3 py-2">
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">{{ __('app.expenses.f.tax') }}</label>
        <input type="number" step="0.01" min="0" name="tax_amount" value="{{ old('tax_amount', $t?->tax_amount ?? '') }}" class="w-full rounded-lg border px-3 py-2">
    </div>
</div>
<div class="grid grid-cols-2 gap-2">
    <div>
        <label class="block text-xs text-slate-500 mb-1">{{ __('app.recurring.interval') }}</label>
        <select name="interval" class="w-full rounded-lg border px-3 py-2">
            @foreach ($intervals as $i)
                <option value="{{ $i->value }}" @selected(old('interval', $t?->interval?->value ?? 'monthly') === $i->value)>{{ $i->label() }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">{{ __('app.recurring.next_run') }} <span class="text-red-500">*</span></label>
        <input type="date" name="next_run_on" value="{{ old('next_run_on', isset($t) ? $t?->next_run_on?->format('Y-m-d') : now()->format('Y-m-d')) }}" required class="w-full rounded-lg border px-3 py-2">
    </div>
</div>
<div class="grid grid-cols-2 gap-2">
    <div>
        <label class="block text-xs text-slate-500 mb-1">{{ __('app.expenses.f.category') }}</label>
        <select name="expense_category_id" class="w-full rounded-lg border px-3 py-2">
            <option value="">{{ __('app.expenses.f.uncategorised') }}</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" @selected((string) old('expense_category_id', $t?->expense_category_id ?? '') === (string) $cat->id)>{{ $cat->icon }} {{ $cat->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs text-slate-500 mb-1">{{ __('app.expenses.f.vendor') }}</label>
        <select name="vendor_id" class="w-full rounded-lg border px-3 py-2">
            <option value="">{{ __('app.expenses.f.no_vendor') }}</option>
            @foreach ($vendors as $v)
                <option value="{{ $v->id }}" @selected((string) old('vendor_id', $t?->vendor_id ?? '') === (string) $v->id)>{{ $v->name }}</option>
            @endforeach
        </select>
    </div>
</div>
@isset($template)
    <label class="flex items-center gap-2 text-sm text-slate-600">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $template->is_active))> {{ __('app.recurring.active') }}
    </label>
@endisset
