{{-- Shared create/edit form. Expects $expense (nullable), $categories, $action, $method. --}}
@php($e = $expense ?? null)
<form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="card p-5 space-y-4 max-w-2xl">
    @csrf
    @if (($method ?? 'POST') === 'PUT') @method('PUT') @endif

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('app.expenses.f.title') }} <span class="text-red-500">*</span></label>
        <input type="text" name="title" value="{{ old('title', $e?->title ?? '') }}" required class="w-full rounded-lg border px-3 py-2">
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('app.expenses.f.category') }}</label>
            <select name="expense_category_id" class="w-full rounded-lg border px-3 py-2">
                <option value="">{{ __('app.expenses.f.uncategorised') }}</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected((string) old('expense_category_id', $e?->expense_category_id ?? '') === (string) $cat->id)>{{ $cat->icon }} {{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('app.expenses.f.vendor') }}</label>
            <select name="vendor_id" class="w-full rounded-lg border px-3 py-2">
                <option value="">{{ __('app.expenses.f.no_vendor') }}</option>
                @foreach ($vendors as $v)
                    <option value="{{ $v->id }}" @selected((string) old('vendor_id', $e?->vendor_id ?? '') === (string) $v->id)>{{ $v->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('app.expenses.f.date') }} <span class="text-red-500">*</span></label>
            <input type="date" name="expense_date" value="{{ old('expense_date', isset($e) ? $e?->expense_date?->format('Y-m-d') : now()->format('Y-m-d')) }}" required class="w-full rounded-lg border px-3 py-2">
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('app.expenses.f.amount') }} <span class="text-red-500">*</span></label>
            <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount', $e?->amount ?? '') }}" required class="w-full rounded-lg border px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('app.expenses.f.tax') }}</label>
            <input type="number" step="0.01" min="0" name="tax_amount" value="{{ old('tax_amount', $e?->tax_amount ?? '') }}" class="w-full rounded-lg border px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('app.expenses.f.reference') }}</label>
            <input type="text" name="reference_no" value="{{ old('reference_no', $e?->reference_no ?? '') }}" class="w-full rounded-lg border px-3 py-2">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('app.expenses.f.description') }}</label>
        <textarea name="description" rows="3" class="w-full rounded-lg border px-3 py-2">{{ old('description', $e?->description ?? '') }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('app.expenses.f.receipts') }}</label>
        <input type="file" name="receipts[]" multiple accept=".jpg,.jpeg,.png,.webp,.pdf" class="block w-full text-sm">
        <p class="text-xs text-slate-400 mt-1">{{ __('app.expenses.f.receipts_hint') }}</p>
    </div>

    <div class="flex items-center gap-2 pt-2">
        <button type="submit" class="btn btn-primary">{{ __('app.expenses.f.save') }}</button>
        <a href="{{ route('practice.doctor.expenses.index') }}" class="btn btn-secondary">{{ __('app.dental.actions.cancel') }}</a>
    </div>
</form>
