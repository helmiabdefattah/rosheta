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
            <div class="flex items-center gap-2">
                <select name="vendor_id" id="expense-vendor-select" class="w-full rounded-lg border px-3 py-2">
                    <option value="">{{ __('app.expenses.f.no_vendor') }}</option>
                    @foreach ($vendors as $v)
                        <option value="{{ $v->id }}" @selected((string) old('vendor_id', $e?->vendor_id ?? '') === (string) $v->id)>{{ $v->name }}</option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-secondary btn-sm shrink-0" onclick="openVendorModal()" title="{{ __('app.vendors.new') }}">➕</button>
            </div>
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

{{-- Add-vendor modal: creates a vendor via fetch and selects it in the form
     above, so the user never leaves the expense screen. --}}
<div id="vendor-modal" class="hidden fixed inset-0 z-[90] flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/50" onclick="closeVendorModal()"></div>
    <div class="relative card w-full max-w-md p-5 space-y-3 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold text-slate-800">{{ __('app.vendors.new') }}</h3>
            <button type="button" class="text-slate-400 hover:text-slate-700 text-2xl leading-none" onclick="closeVendorModal()">&times;</button>
        </div>

        <div id="vendor-modal-error" class="hidden rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-3 py-2"></div>

        <div>
            <label class="block text-xs text-slate-500 mb-1">{{ __('app.vendors.f.name') }} <span class="text-red-500">*</span></label>
            <input type="text" id="vm-name" class="w-full rounded-lg border px-3 py-2">
        </div>
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="block text-xs text-slate-500 mb-1">{{ __('app.vendors.f.type') }}</label>
                <select id="vm-type" class="w-full rounded-lg border px-3 py-2">
                    @foreach (\App\Enums\VendorType::cases() as $t)
                        <option value="{{ $t->value }}" @selected($t->value === 'supplier')>{{ $t->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">{{ __('app.vendors.f.phone') }}</label>
                <input type="text" id="vm-phone" class="w-full rounded-lg border px-3 py-2">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-2">
            <div>
                <label class="block text-xs text-slate-500 mb-1">{{ __('app.vendors.f.email') }}</label>
                <input type="email" id="vm-email" class="w-full rounded-lg border px-3 py-2">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">{{ __('app.vendors.f.opening') }}</label>
                <input type="number" step="0.01" min="0" id="vm-opening" value="0" class="w-full rounded-lg border px-3 py-2">
            </div>
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">{{ __('app.vendors.f.notes') }}</label>
            <textarea id="vm-notes" rows="2" class="w-full rounded-lg border px-3 py-2"></textarea>
        </div>

        <div class="flex items-center gap-2 pt-1">
            <button type="button" id="vm-save" class="btn btn-primary" onclick="saveVendor()">{{ __('app.expenses.f.save') }}</button>
            <button type="button" class="btn btn-secondary" onclick="closeVendorModal()">{{ __('app.dental.actions.cancel') }}</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var modal = document.getElementById('vendor-modal');
    var storeUrl = @json(route('practice.doctor.vendors.store'));
    var token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    window.openVendorModal = function () {
        document.getElementById('vendor-modal-error').classList.add('hidden');
        modal.classList.remove('hidden');
        document.getElementById('vm-name').focus();
    };
    window.closeVendorModal = function () { modal.classList.add('hidden'); };

    function showError(msg) {
        var box = document.getElementById('vendor-modal-error');
        box.textContent = msg;
        box.classList.remove('hidden');
    }

    window.saveVendor = function () {
        var btn = document.getElementById('vm-save');
        var payload = {
            name: document.getElementById('vm-name').value.trim(),
            type: document.getElementById('vm-type').value,
            phone: document.getElementById('vm-phone').value.trim(),
            email: document.getElementById('vm-email').value.trim(),
            opening_balance: document.getElementById('vm-opening').value,
            notes: document.getElementById('vm-notes').value.trim(),
        };
        if (!payload.name) { showError(@json(__('app.vendors.f.name'))); return; }

        btn.disabled = true;
        fetch(storeUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
        }).then(function (res) {
            return res.json().then(function (data) { return { ok: res.ok, status: res.status, data: data }; });
        }).then(function (r) {
            if (!r.ok) {
                var msg = r.data && r.data.message ? r.data.message : 'Error';
                if (r.data && r.data.errors) {
                    msg = Object.values(r.data.errors).map(function (e) { return e[0]; }).join(' ');
                }
                showError(msg);
                return;
            }
            var select = document.getElementById('expense-vendor-select');
            var opt = document.createElement('option');
            opt.value = r.data.id;
            opt.textContent = r.data.name;
            opt.selected = true;
            select.appendChild(opt);
            // Reset + close.
            ['vm-name', 'vm-phone', 'vm-email', 'vm-notes'].forEach(function (id) { document.getElementById(id).value = ''; });
            document.getElementById('vm-opening').value = '0';
            closeVendorModal();
        }).catch(function () {
            showError('Network error');
        }).finally(function () {
            btn.disabled = false;
        });
    };

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeVendorModal();
    });
})();
</script>
@endpush
