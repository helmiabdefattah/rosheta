@extends('clinic.layouts.app')

@section('content')
@php $p = $appointment->client; @endphp

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
    <div>
        <a href="{{ route('practice.doctor.dashboard') }}" class="text-sm text-indigo-600 hover:underline">{{ __('app.examine.back_to_dashboard') }}</a>
        <h1 class="text-2xl font-bold text-slate-900 mt-1">{{ __('app.examine.title', ['name' => $p->name]) }}</h1>
        <p class="text-slate-500 text-sm">
            {{ __('app.examine.queue', ['num' => $appointment->queue_number]) }} &middot; {{ $appointment->typeLabel() }}
            &middot; {{ $appointment->scheduled_at->format('H:i') }}
            &middot; <span>{{ $appointment->statusLabel() }}</span>
        </p>
    </div>
    <div class="flex gap-2">
        @if ($appointment->status === 'scheduled')
            <form method="POST" action="{{ route('practice.appointments.status', $appointment) }}">
                @csrf <input type="hidden" name="status" value="under_examination">
                <button class="bg-amber-600 text-white text-sm px-4 py-2 rounded-lg">{{ __('app.examine.start_examination') }}</button>
            </form>
        @endif
        @if (! in_array($appointment->status, ['completed', 'cancelled']))
            <form method="POST" action="{{ route('practice.appointments.status', $appointment) }}">
                @csrf <input type="hidden" name="status" value="completed">
                <button class="bg-emerald-600 text-white text-sm px-4 py-2 rounded-lg">{{ __('app.examine.complete') }}</button>
            </form>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- LEFT: patient info + attachments --}}
    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-slate-800">{{ __('app.examine.patient_info') }}</h2>
                <button onclick="toggle('edit-profile')"
                        class="text-xs px-2 py-1 rounded bg-indigo-100 hover:bg-indigo-200 text-indigo-700">✏️ {{ __('app.patient.edit') }}</button>
            </div>
            <dl class="text-sm space-y-1.5">
                <div class="flex justify-between"><dt class="text-slate-400">{{ __('app.common.gender') }}</dt><dd class="capitalize">{{ $p->gender }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">{{ __('app.common.age') }}</dt><dd>{{ $p->age ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">{{ __('app.common.phone') }}</dt><dd>{{ $p->phone_number ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">{{ __('app.common.blood_type') }}</dt><dd>{{ $p->blood_type ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">{{ __('app.common.allergies') }}</dt><dd class="text-red-600">{{ filled($p->allergies) ? implode('، ', (array) $p->allergies) : __('app.common.none') }}</dd></div>
                <div class="flex justify-between"><dt class="text-slate-400">{{ __('app.common.chronic_diseases') }}</dt><dd>{{ filled($p->chronic_diseases) ? implode('، ', (array) $p->chronic_diseases) : __('app.common.none') }}</dd></div>
            </dl>
            <a href="{{ route('practice.patients.show', $p) }}" class="block mt-3 text-sm text-indigo-600 hover:underline">{{ __('app.examine.view_full_profile') }}</a>

            {{-- Editable patient profile (incl. allergies) --}}
            <div id="edit-profile" class="hidden mt-4 pt-4 border-t border-slate-100">
                <h3 class="font-semibold text-slate-800 mb-3">{{ __('app.patient.edit_heading') }}</h3>
                <form method="POST" action="{{ route('practice.patients.update', $p) }}" class="space-y-3">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">{{ __('app.patient.name') }}</label>
                        <input type="text" name="name" required value="{{ old('name', $p->name) }}"
                               class="w-full border rounded px-2 py-1.5 text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">{{ __('app.common.gender') }}</label>
                            <select name="gender" class="w-full border rounded px-2 py-1.5 text-sm">
                                <option value="male" @selected($p->gender === 'male')>{{ __('app.genders.male') }}</option>
                                <option value="female" @selected($p->gender === 'female')>{{ __('app.genders.female') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">{{ __('app.patient.dob') }}</label>
                            <input type="date" name="dob" value="{{ old('dob', $p->dob?->format('Y-m-d')) }}"
                                   class="w-full border rounded px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">{{ __('app.common.phone') }}</label>
                            <input type="text" name="phone_number" value="{{ old('phone_number', $p->phone_number) }}"
                                   class="w-full border rounded px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">{{ __('app.common.email') }}</label>
                            <input type="email" name="email" value="{{ old('email', $p->email) }}"
                                   class="w-full border rounded px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">{{ __('app.common.national_id') }}</label>
                            <input type="text" name="national_id" value="{{ old('national_id', $p->national_id) }}"
                                   class="w-full border rounded px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-500 mb-1">{{ __('app.common.blood_type') }}</label>
                            <input type="text" name="blood_type" value="{{ old('blood_type', $p->blood_type) }}"
                                   class="w-full border rounded px-2 py-1.5 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">{{ __('app.common.address') }}</label>
                        <input type="text" name="address" value="{{ old('address', $p->address) }}"
                               class="w-full border rounded px-2 py-1.5 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">{{ __('app.patient.medical_history') }}</label>
                        <textarea name="medical_history" rows="2"
                                  class="w-full border rounded px-2 py-1.5 text-sm">{{ old('medical_history', $p->medical_history) }}</textarea>
                    </div>
                    <div class="flex gap-2">
                        <button class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-lg">{{ __('app.patient.save') }}</button>
                        <button type="button" onclick="toggle('edit-profile')" class="text-sm text-slate-500 px-3">{{ __('app.patient.cancel') }}</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Allergies (editable list) --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h2 class="font-semibold text-slate-800 mb-3">{{ __('app.examine.allergies_section') }}</h2>
            <form method="POST" action="{{ route('practice.doctor.allergies.update', $appointment) }}">
                @csrf @method('PUT')
                <div id="allergy-list" class="space-y-2 mb-3">
                    @php $allergies = old('allergies', (array) $p->allergies); @endphp
                    @forelse ($allergies as $allergy)
                        <div class="allergy-row flex items-center gap-2">
                            <input name="allergies[]" value="{{ $allergy }}"
                                   placeholder="{{ __('app.examine.allergies_placeholder') }}"
                                   class="flex-1 border rounded px-2 py-1.5 text-sm">
                            <button type="button" onclick="this.closest('.allergy-row').remove()"
                                    class="text-red-500 px-2">✕</button>
                        </div>
                    @empty
                        <div class="allergy-row flex items-center gap-2">
                            <input name="allergies[]" value=""
                                   placeholder="{{ __('app.examine.allergies_placeholder') }}"
                                   class="flex-1 border rounded px-2 py-1.5 text-sm">
                            <button type="button" onclick="this.closest('.allergy-row').remove()"
                                    class="text-red-500 px-2">✕</button>
                        </div>
                    @endforelse
                </div>
                <button type="button" onclick="addAllergyRow()" class="text-indigo-600 text-sm hover:underline mb-3 block">{{ __('app.examine.allergies_add') }}</button>
                <button class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-lg">{{ __('app.examine.allergies_save') }}</button>
            </form>
        </div>

        {{-- Chronic diseases (editable list) --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h2 class="font-semibold text-slate-800 mb-3">{{ __('app.examine.chronic_section') }}</h2>
            <form method="POST" action="{{ route('practice.doctor.chronic.update', $appointment) }}">
                @csrf @method('PUT')
                <div id="chronic-list" class="space-y-2 mb-3">
                    @php $diseases = old('chronic_diseases', (array) $p->chronic_diseases); @endphp
                    @forelse ($diseases as $disease)
                        <div class="chronic-row flex items-center gap-2">
                            <input name="chronic_diseases[]" value="{{ $disease }}"
                                   placeholder="{{ __('app.examine.chronic_placeholder') }}"
                                   class="flex-1 border rounded px-2 py-1.5 text-sm">
                            <button type="button" onclick="this.closest('.chronic-row').remove()"
                                    class="text-red-500 px-2">✕</button>
                        </div>
                    @empty
                        <div class="chronic-row flex items-center gap-2">
                            <input name="chronic_diseases[]" value=""
                                   placeholder="{{ __('app.examine.chronic_placeholder') }}"
                                   class="flex-1 border rounded px-2 py-1.5 text-sm">
                            <button type="button" onclick="this.closest('.chronic-row').remove()"
                                    class="text-red-500 px-2">✕</button>
                        </div>
                    @endforelse
                </div>
                <button type="button" onclick="addChronicRow()" class="text-indigo-600 text-sm hover:underline mb-3 block">{{ __('app.examine.chronic_add') }}</button>
                <button class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-lg">{{ __('app.examine.chronic_save') }}</button>
            </form>
        </div>

        {{-- Attachments --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h2 class="font-semibold text-slate-800 mb-3">{{ __('app.examine.attachments') }}</h2>
            <ul class="space-y-2 text-sm mb-4">
                @forelse ($p->attachments as $att)
                    <li class="flex items-center justify-between">
                        <a href="{{ $att->url }}" target="_blank" class="text-indigo-600 hover:underline truncate">
                            📎 {{ $att->title ?? $att->file_name }}
                        </a>
                        <span class="text-xs text-slate-400">{{ $att->file_type }}</span>
                    </li>
                @empty
                    <li class="text-slate-400 italic">{{ __('app.examine.no_attachments') }}</li>
                @endforelse
            </ul>
            <form method="POST" action="{{ route('practice.attachments.store', $appointment) }}" enctype="multipart/form-data" class="space-y-2">
                @csrf
                <input type="text" name="title" placeholder="{{ __('app.examine.title_placeholder') }}" class="w-full border rounded px-2 py-1 text-sm">
                <input type="file" name="file" required class="w-full text-sm">
                <button class="w-full bg-blue-600 text-white text-sm py-1.5 rounded">{{ __('app.examine.upload_attachment') }}</button>
            </form>
        </div>
    </div>

    {{-- MIDDLE + RIGHT --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Diagnosis + treatment plan --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h2 class="font-semibold text-slate-800 mb-3">{{ __('app.examine.diagnosis_section') }}</h2>
            <form method="POST" action="{{ route('practice.doctor.diagnosis.store', $appointment) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-sm text-slate-500 mb-1">{{ __('app.examine.diagnosis_label') }}</label>
                    <textarea name="diagnosis" rows="3" required
                              class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('diagnosis', $appointment->diagnosis?->diagnosis) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm text-slate-500 mb-1">{{ __('app.examine.treatment_plan') }}</label>
                    <textarea name="treatment_plan" rows="3"
                              class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('treatment_plan', $appointment->diagnosis?->treatment_plan) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm text-slate-500 mb-1">{{ __('app.common.notes') }}</label>
                    <textarea name="notes" rows="2"
                              class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('notes', $appointment->diagnosis?->notes) }}</textarea>
                </div>
                <button class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-lg">{{ __('app.examine.save_diagnosis') }}</button>
            </form>
        </div>

        {{-- Medical requests: examinations / tests / radiology --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h2 class="font-semibold text-slate-800 mb-3">{{ __('app.examine.requests_section') }}</h2>
            <ul class="space-y-2 text-sm mb-4">
                @forelse ($appointment->medicalRequests as $req)
                    <li class="flex items-center justify-between border-b border-slate-50 pb-1">
                        <span><span class="text-xs px-2 py-0.5 rounded bg-slate-100 mx-2">{{ $req->typeLabel() }}</span>{{ $req->name }}</span>
                        <form method="POST" action="{{ route('practice.doctor.requests.destroy', $req) }}">
                            @csrf @method('DELETE')
                            <button class="text-red-500 text-xs hover:underline">{{ __('app.common.remove') }}</button>
                        </form>
                    </li>
                @empty
                    <li class="text-slate-400 italic">{{ __('app.examine.no_requests') }}</li>
                @endforelse
            </ul>
            <form method="POST" action="{{ route('practice.doctor.requests.store', $appointment) }}" class="flex flex-wrap items-end gap-2">
                @csrf
                <select name="type" class="border rounded px-2 py-1.5 text-sm">
                    <option value="examination">{{ __('app.request_types.examination') }}</option>
                    <option value="lab_test">{{ __('app.request_types.lab_test') }}</option>
                    <option value="radiology">{{ __('app.request_types.radiology') }}</option>
                </select>
                <input type="text" name="name" placeholder="{{ __('app.examine.request_name_placeholder') }}" required
                       class="border rounded px-2 py-1.5 text-sm flex-1 min-w-[180px]">
                <button class="bg-slate-700 text-white text-sm px-3 py-1.5 rounded">{{ __('app.examine.add') }}</button>
            </form>
        </div>

        {{-- Prescription / medicines --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h2 class="font-semibold text-slate-800 mb-3">{{ __('app.examine.new_prescription') }}</h2>
            <form method="POST" action="{{ route('practice.doctor.prescriptions.store', $appointment) }}">
                @csrf
                <div class="overflow-x-auto -mx-1">
                <table class="w-full text-sm mb-2 min-w-[560px]" id="rx-table">
                    <thead class="text-slate-400 text-start text-xs">
                        <tr>
                            <th class="py-1">{{ __('app.examine.medicine') }}</th>
                            <th class="py-1">{{ __('app.examine.dose') }}</th>
                            <th class="py-1">{{ __('app.examine.frequency') }}</th>
                            <th class="py-1">{{ __('app.examine.duration') }}</th>
                            <th class="py-1">{{ __('app.examine.instructions') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="rx-row">
                            <td class="pr-1 py-1"><input name="items[0][medicine_name]" required class="w-full border rounded px-2 py-1"></td>
                            <td class="pr-1 py-1"><input name="items[0][dose]" placeholder="500 mg" class="w-full border rounded px-2 py-1"></td>
                            <td class="pr-1 py-1"><input name="items[0][frequency]" placeholder="2x/day" class="w-full border rounded px-2 py-1"></td>
                            <td class="pr-1 py-1"><input name="items[0][duration]" placeholder="7 days" class="w-full border rounded px-2 py-1"></td>
                            <td class="pr-1 py-1"><input name="items[0][instructions]" placeholder="after meals" class="w-full border rounded px-2 py-1"></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
                </div>
                <button type="button" onclick="addRxRow()" class="text-indigo-600 text-sm hover:underline mb-3">{{ __('app.examine.add_medicine') }}</button>
                <div class="mb-3">
                    <label class="block text-sm text-slate-500 mb-1">{{ __('app.examine.prescription_notes') }}</label>
                    <textarea name="notes" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                </div>
                <button class="bg-purple-600 text-white text-sm px-4 py-2 rounded-lg">{{ __('app.examine.create_prescription') }}</button>
            </form>
        </div>

        {{-- Existing prescriptions --}}
        @if ($appointment->prescriptions->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm p-5">
                <h2 class="font-semibold text-slate-800 mb-3">{{ __('app.examine.prescriptions') }}</h2>
                <ul class="space-y-2 text-sm">
                    @foreach ($appointment->prescriptions as $rx)
                        <li class="flex items-center justify-between border-b border-slate-50 pb-2">
                            <div>
                                <span class="font-mono text-xs bg-slate-100 px-2 py-0.5 rounded">{{ $rx->code }}</span>
                                <span class="text-slate-500 mx-2">{{ __('app.examine.medicines_count', ['count' => $rx->items->count()]) }}</span>
                            </div>
                            <a href="{{ route('practice.prescriptions.print', $rx) }}" target="_blank"
                               class="bg-purple-100 text-purple-700 px-3 py-1 rounded text-xs">{{ __('app.common.print') }}</a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Add another allergy input row.
    function addAllergyRow() {
        const list = document.getElementById('allergy-list');
        const row = document.createElement('div');
        row.className = 'allergy-row flex items-center gap-2';
        row.innerHTML = `
            <input name="allergies[]" value="" placeholder="{{ __('app.examine.allergies_placeholder') }}"
                   class="flex-1 border rounded px-2 py-1.5 text-sm">
            <button type="button" onclick="this.closest('.allergy-row').remove()" class="text-red-500 px-2">✕</button>`;
        list.appendChild(row);
    }

    // Add another chronic-disease input row.
    function addChronicRow() {
        const list = document.getElementById('chronic-list');
        const row = document.createElement('div');
        row.className = 'chronic-row flex items-center gap-2';
        row.innerHTML = `
            <input name="chronic_diseases[]" value="" placeholder="{{ __('app.examine.chronic_placeholder') }}"
                   class="flex-1 border rounded px-2 py-1.5 text-sm">
            <button type="button" onclick="this.closest('.chronic-row').remove()" class="text-red-500 px-2">✕</button>`;
        list.appendChild(row);
    }

    let rxIndex = 1;
    function addRxRow() {
        const tbody = document.querySelector('#rx-table tbody');
        const tr = document.createElement('tr');
        tr.className = 'rx-row';
        tr.innerHTML = `
            <td class="pr-1 py-1"><input name="items[${rxIndex}][medicine_name]" class="w-full border rounded px-2 py-1"></td>
            <td class="pr-1 py-1"><input name="items[${rxIndex}][dose]" placeholder="500 mg" class="w-full border rounded px-2 py-1"></td>
            <td class="pr-1 py-1"><input name="items[${rxIndex}][frequency]" placeholder="2x/day" class="w-full border rounded px-2 py-1"></td>
            <td class="pr-1 py-1"><input name="items[${rxIndex}][duration]" placeholder="7 days" class="w-full border rounded px-2 py-1"></td>
            <td class="pr-1 py-1"><input name="items[${rxIndex}][instructions]" placeholder="after meals" class="w-full border rounded px-2 py-1"></td>
            <td><button type="button" onclick="this.closest('tr').remove()" class="text-red-500 px-2">✕</button></td>`;
        tbody.appendChild(tr);
        rxIndex++;
    }

    // Re-open the profile editor if it failed validation (only that form submits "name").
    @if ($errors->any() && old('name'))
        document.getElementById('edit-profile')?.classList.remove('hidden');
    @endif
</script>
@endpush
@endsection
