{{--
    Editable medicine-rows table for a medical plan (create/edit).
    Expects: $items — iterable of rows (medicine_name, dose, frequency,
    duration, instructions), may be empty. Include once per page.
--}}
@php $rows = collect($items ?? [])->values(); @endphp
<div class="overflow-x-auto -mx-1">
    <table class="w-full text-sm mb-2 min-w-[560px]" id="plan-table">
        <thead class="text-slate-400 text-start text-xs">
            <tr>
                <th class="py-1 text-start">{{ __('app.examine.medicine') }}</th>
                <th class="py-1 text-start">{{ __('app.examine.dose') }}</th>
                <th class="py-1 text-start">{{ __('app.examine.frequency') }}</th>
                <th class="py-1 text-start">{{ __('app.examine.duration') }}</th>
                <th class="py-1 text-start">{{ __('app.examine.instructions') }}</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $i => $row)
                @php $r = is_array($row) ? $row : $row->toArray(); @endphp
                <tr class="plan-row">
                    <td class="pr-1 py-1"><input name="items[{{ $i }}][medicine_name]" value="{{ $r['medicine_name'] ?? '' }}" class="w-full border rounded px-2 py-1"></td>
                    <td class="pr-1 py-1"><input name="items[{{ $i }}][dose]" value="{{ $r['dose'] ?? '' }}" placeholder="500 mg" class="w-full border rounded px-2 py-1"></td>
                    <td class="pr-1 py-1"><input name="items[{{ $i }}][frequency]" value="{{ $r['frequency'] ?? '' }}" placeholder="2x/day" class="w-full border rounded px-2 py-1"></td>
                    <td class="pr-1 py-1"><input name="items[{{ $i }}][duration]" value="{{ $r['duration'] ?? '' }}" placeholder="7 days" class="w-full border rounded px-2 py-1"></td>
                    <td class="pr-1 py-1"><input name="items[{{ $i }}][instructions]" value="{{ $r['instructions'] ?? '' }}" placeholder="after meals" class="w-full border rounded px-2 py-1"></td>
                    <td><button type="button" onclick="this.closest('tr').remove()" class="text-red-500 px-2">✕</button></td>
                </tr>
            @empty
                <tr class="plan-row">
                    <td class="pr-1 py-1"><input name="items[0][medicine_name]" class="w-full border rounded px-2 py-1"></td>
                    <td class="pr-1 py-1"><input name="items[0][dose]" placeholder="500 mg" class="w-full border rounded px-2 py-1"></td>
                    <td class="pr-1 py-1"><input name="items[0][frequency]" placeholder="2x/day" class="w-full border rounded px-2 py-1"></td>
                    <td class="pr-1 py-1"><input name="items[0][duration]" placeholder="7 days" class="w-full border rounded px-2 py-1"></td>
                    <td class="pr-1 py-1"><input name="items[0][instructions]" placeholder="after meals" class="w-full border rounded px-2 py-1"></td>
                    <td></td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<button type="button" onclick="addPlanRow()" class="text-indigo-600 text-sm hover:underline mb-3">{{ __('app.examine.add_medicine') }}</button>

@push('scripts')
<script>
    let planRowIndex = {{ max($rows->count(), 1) }};
    function addPlanRow() {
        const tbody = document.querySelector('#plan-table tbody');
        const tr = document.createElement('tr');
        tr.className = 'plan-row';
        tr.innerHTML = `
            <td class="pr-1 py-1"><input name="items[${planRowIndex}][medicine_name]" class="w-full border rounded px-2 py-1"></td>
            <td class="pr-1 py-1"><input name="items[${planRowIndex}][dose]" placeholder="500 mg" class="w-full border rounded px-2 py-1"></td>
            <td class="pr-1 py-1"><input name="items[${planRowIndex}][frequency]" placeholder="2x/day" class="w-full border rounded px-2 py-1"></td>
            <td class="pr-1 py-1"><input name="items[${planRowIndex}][duration]" placeholder="7 days" class="w-full border rounded px-2 py-1"></td>
            <td class="pr-1 py-1"><input name="items[${planRowIndex}][instructions]" placeholder="after meals" class="w-full border rounded px-2 py-1"></td>
            <td><button type="button" onclick="this.closest('tr').remove()" class="text-red-500 px-2">✕</button></td>`;
        tbody.appendChild(tr);
        planRowIndex++;
    }
</script>
@endpush
