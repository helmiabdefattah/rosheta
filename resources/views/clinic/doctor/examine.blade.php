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
                @if ($appointment->insurance && $appointment->insurance->insuranceCompany)
                    <div class="flex justify-between"><dt class="text-slate-400">{{ __('app.insurance.title') }}</dt>
                        <dd class="text-cyan-800 font-medium text-end">
                            🛡 {{ $appointment->insurance->insuranceCompany->displayName() }}
                            <div class="text-[11px] text-slate-500 font-normal">
                                {{ __('app.insurance.patient_amount') }}: {{ number_format((float) $appointment->insurance->patient_amount, 2) }}
                                · {{ __('app.insurance.insurance_amount') }}: {{ number_format((float) $appointment->insurance->insurance_amount, 2) }}
                            </div>
                        </dd>
                    </div>
                @endif
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

        {{-- Medical history: this patient's examinations across visits & doctors.
             The acting doctor's own records are editable; others are view-only. --}}
        @php $history = $appointment->client->diagnoses->where('appointment_id', '!=', $appointment->id); @endphp
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h2 class="font-semibold text-slate-800 mb-1">📋 {{ __('app.examine.history_section') }}</h2>
            <p class="text-xs text-slate-500 mb-4">{{ __('app.examine.history_hint') }}</p>
            @forelse ($history as $past)
                @php $mine = $past->doctor_id === $actingDoctorId; @endphp
                <div class="border border-slate-100 rounded-lg p-3 mb-3">
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <div class="text-sm">
                            <span class="font-medium text-slate-700">👨‍⚕️ {{ $past->doctor?->name ?? __('app.common.none') }}</span>
                            <span class="text-xs text-slate-400 mx-1">{{ optional($past->appointment?->scheduled_at ?? $past->created_at)->format('Y-m-d') }}</span>
                        </div>
                        @if ($mine)
                            <span class="text-xs px-2 py-0.5 rounded bg-emerald-100 text-emerald-700">✎ {{ __('app.examine.history_mine') }}</span>
                        @else
                            <span class="text-xs px-2 py-0.5 rounded bg-slate-100 text-slate-500">🔒 {{ __('app.examine.history_view_only') }}</span>
                        @endif
                    </div>

                    @if ($mine)
                        <form method="POST" action="{{ route('practice.doctor.diagnoses.update', $past) }}" class="space-y-2">
                            @csrf @method('PUT')
                            <div>
                                <label class="block text-xs text-slate-400 mb-1">{{ __('app.examine.history_diagnosis') }}</label>
                                <textarea name="diagnosis" rows="2" required
                                          class="w-full border rounded px-2 py-1.5 text-sm">{{ $past->diagnosis }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs text-slate-400 mb-1">{{ __('app.examine.treatment_plan') }}</label>
                                <textarea name="treatment_plan" rows="2"
                                          class="w-full border rounded px-2 py-1.5 text-sm">{{ $past->treatment_plan }}</textarea>
                            </div>
                            <div>
                                <label class="block text-xs text-slate-400 mb-1">{{ __('app.common.notes') }}</label>
                                <textarea name="notes" rows="1"
                                          class="w-full border rounded px-2 py-1.5 text-sm">{{ $past->notes }}</textarea>
                            </div>
                            <button class="bg-indigo-600 text-white text-xs px-3 py-1.5 rounded">{{ __('app.examine.history_save') }}</button>
                        </form>
                    @else
                        <dl class="text-sm space-y-1.5">
                            <div>
                                <dt class="text-xs text-slate-400">{{ __('app.examine.history_diagnosis') }}</dt>
                                <dd class="text-slate-700 whitespace-pre-line">{{ $past->diagnosis }}</dd>
                            </div>
                            @if ($past->treatment_plan)
                                <div>
                                    <dt class="text-xs text-slate-400">{{ __('app.examine.treatment_plan') }}</dt>
                                    <dd class="text-slate-700 whitespace-pre-line">{{ $past->treatment_plan }}</dd>
                                </div>
                            @endif
                            @if ($past->notes)
                                <div>
                                    <dt class="text-xs text-slate-400">{{ __('app.common.notes') }}</dt>
                                    <dd class="text-slate-600 whitespace-pre-line">{{ $past->notes }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif
                </div>
            @empty
                <p class="text-sm text-slate-400 italic">{{ __('app.examine.no_history') }}</p>
            @endforelse
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
                <select name="type" id="req-type" class="border rounded px-2 py-1.5 text-sm">
                    <option value="examination">{{ __('app.request_types.examination') }}</option>
                    <option value="lab_test">{{ __('app.request_types.lab_test') }}</option>
                    <option value="radiology">{{ __('app.request_types.radiology') }}</option>
                </select>
                <input type="text" name="name" id="req-name" list="req-suggestions" autocomplete="off"
                       placeholder="{{ __('app.examine.request_name_placeholder') }}" required
                       class="border rounded px-2 py-1.5 text-sm flex-1 min-w-[180px]">
                <datalist id="req-suggestions"></datalist>
                <button class="bg-slate-700 text-white text-sm px-3 py-1.5 rounded">{{ __('app.examine.add') }}</button>
            </form>
        </div>

        {{-- Test results: lab / radiology files attached to the patient --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h2 class="font-semibold text-slate-800 mb-3">🧪 {{ __('app.examine.tests_section') }}</h2>

            {{-- Existing tests for this patient (across all visits) --}}
            <ul class="space-y-3 text-sm mb-5">
                @forelse ($appointment->client->patientTests as $test)
                    <li class="border border-slate-100 rounded-lg p-3">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <div>
                                <span class="text-xs px-2 py-0.5 rounded
                                    {{ $test->type === 'radiology' ? 'bg-purple-100 text-purple-700' : 'bg-teal-100 text-teal-700' }}">
                                    {{ $test->typeLabel() }}
                                </span>
                                @if ($test->title)
                                    <span class="text-slate-800 mx-1">{{ $test->title }}</span>
                                @endif
                                <span class="text-xs text-slate-400 mx-1">{{ $test->created_at->format('Y-m-d') }}</span>
                            </div>
                            <form method="POST" action="{{ route('practice.doctor.tests.destroy', $test) }}"
                                  onsubmit="return confirm('{{ __('app.examine.test_remove_confirm') }}')">
                                @csrf @method('DELETE')
                                <button class="text-red-500 text-xs hover:underline">{{ __('app.common.remove') }}</button>
                            </form>
                        </div>
                        @if ($test->notes)
                            <p class="text-xs text-slate-500 mb-2">{{ $test->notes }}</p>
                        @endif
                        <ul class="flex flex-wrap gap-2">
                            @foreach ($test->attachments as $file)
                                <li>
                                    <a href="{{ $file->url }}" target="_blank"
                                       class="inline-flex items-center gap-1 text-indigo-600 hover:underline bg-slate-50 border border-slate-100 rounded px-2 py-1 text-xs">
                                        📎 {{ $file->file_name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @empty
                    <li class="text-slate-400 italic">{{ __('app.examine.no_tests') }}</li>
                @endforelse
            </ul>

            {{-- Attach a new test result --}}
            <form method="POST" action="{{ route('practice.doctor.tests.store', $appointment) }}"
                  enctype="multipart/form-data" class="space-y-2 border-t border-slate-100 pt-4">
                @csrf
                <div class="flex flex-wrap items-end gap-2">
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">{{ __('app.examine.test_type') }}</label>
                        <select name="type" class="border rounded px-2 py-1.5 text-sm">
                            <option value="lab">{{ __('app.test_types.lab') }}</option>
                            <option value="radiology">{{ __('app.test_types.radiology') }}</option>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[180px]">
                        <label class="block text-xs text-slate-500 mb-1">{{ __('app.examine.test_title') }}</label>
                        <input type="text" name="title" placeholder="{{ __('app.examine.test_title_placeholder') }}"
                               class="w-full border rounded px-2 py-1.5 text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">{{ __('app.examine.test_files') }}</label>
                    <input type="file" name="files[]" multiple required class="w-full text-sm">
                    <p class="text-[11px] text-slate-400 mt-1">{{ __('app.examine.test_files_hint') }}</p>
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">{{ __('app.common.notes') }}</label>
                    <textarea name="notes" rows="2" class="w-full border rounded px-2 py-1.5 text-sm"></textarea>
                </div>
                <button class="bg-teal-600 hover:bg-teal-700 text-white text-sm px-4 py-2 rounded-lg">{{ __('app.examine.add_test') }}</button>
            </form>
        </div>

        @include('clinic.partials.lab-results')

        {{-- Prescription / medicines --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h2 class="font-semibold text-slate-800 mb-3">{{ __('app.examine.new_prescription') }}</h2>

            {{-- Load a saved medical plan into the rows, or save the current
                 rows as a reusable plan. --}}
            <div class="flex flex-wrap items-end gap-3 mb-4 pb-4 border-b border-slate-100">
                @if ($medicalPlans->isNotEmpty())
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">{{ __('app.plan.load_label') }}</label>
                        <div class="flex items-center gap-2">
                            <select id="plan-select" class="border rounded px-2 py-1.5 text-sm w-52">
                                <option value="">— {{ __('app.plan.select') }} —</option>
                                @foreach ($medicalPlans as $plan)
                                    <option value="{{ $plan->id }}">{{ $plan->title }}</option>
                                @endforeach
                            </select>
                            <button type="button" onclick="loadPlan()"
                                    class="text-sm px-3 py-1.5 rounded-lg border border-indigo-300 text-indigo-700 hover:bg-indigo-50">
                                ↧ {{ __('app.plan.load_button') }}
                            </button>
                        </div>
                    </div>
                @endif
                <div class="ms-auto flex items-end gap-2">
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">{{ __('app.plan.save_as_label') }}</label>
                        <input id="plan-title-input" type="text" placeholder="{{ __('app.plan.title_label') }}"
                               class="border rounded px-2 py-1.5 text-sm w-44">
                    </div>
                    <button type="button" onclick="saveAsPlan()"
                            class="text-sm px-3 py-1.5 rounded-lg border border-purple-300 text-purple-700 hover:bg-purple-50">
                        💾 {{ __('app.plan.save_as_button') }}
                    </button>
                </div>
            </div>

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

        {{-- Hidden form used to POST "save as plan" without touching the Rx form. --}}
        <form id="save-plan-form" method="POST" action="{{ route('practice.doctor.setup.medical-plans.store') }}" class="hidden">
            @csrf
            <input type="hidden" name="title" id="save-plan-title">
            <div id="save-plan-items"></div>
        </form>

        {{-- Custom examination fields defined by the doctor --}}
        <div class="bg-white rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-slate-800">🧾 {{ __('app.field.title_plural') }}</h2>
                <a href="{{ route('practice.doctor.setup.examination-fields') }}" class="text-xs text-indigo-600 hover:underline">{{ __('app.field.manage') }}</a>
            </div>
            @if ($examinationFields->isEmpty())
                <p class="text-sm text-slate-400">{{ __('app.field.none_hint') }}</p>
            @else
                <form method="POST" action="{{ route('practice.doctor.examination-values.store', $appointment) }}"
                      enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach ($examinationFields as $field)
                            @php $val = $examinationValues[$field->id] ?? null; @endphp
                            <div>
                                <label class="block text-sm text-slate-500 mb-1">{{ $field->label }}</label>
                                @switch($field->type)
                                    @case('select')
                                        <select name="fields[{{ $field->id }}]" class="w-full border rounded-lg px-3 py-2 text-sm">
                                            <option value="">—</option>
                                            @foreach ($field->optionsArray() as $opt)
                                                <option value="{{ $opt }}" @selected($val && $val->value === $opt)>{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                        @break
                                    @case('number')
                                        <input type="number" step="any" name="fields[{{ $field->id }}]" value="{{ $val?->value }}"
                                               class="w-full border rounded-lg px-3 py-2 text-sm">
                                        @break
                                    @case('percentage')
                                        <div class="relative">
                                            <input type="number" step="any" min="0" max="100" name="fields[{{ $field->id }}]" value="{{ $val?->value }}"
                                                   class="w-full border rounded-lg px-3 py-2 pe-8 text-sm">
                                            <span class="absolute end-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">%</span>
                                        </div>
                                        @break
                                    @case('file')
                                        @if ($val && $val->attachment)
                                            <a href="{{ asset('storage/'.$val->attachment->file_path) }}" target="_blank"
                                               class="block text-xs text-indigo-600 hover:underline mb-1">📎 {{ $val->attachment->file_name }}</a>
                                        @endif
                                        <input type="file" name="field_files[{{ $field->id }}]" class="w-full text-sm">
                                        @break
                                    @default
                                        <input type="text" name="fields[{{ $field->id }}]" value="{{ $val?->value }}"
                                               class="w-full border rounded-lg px-3 py-2 text-sm">
                                @endswitch
                            </div>
                        @endforeach
                    </div>
                    <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg">{{ __('app.field.save_values') }}</button>
                </form>
            @endif
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

        {{-- Charges to collect: visit fee + any extras added here --}}
        @php
            $visitFee = $appointment->visitPrice();
            $extras = $appointment->itemsTotal();
            $due = $appointment->dueAmount();
            $paid = $appointment->collectedAmount();
        @endphp
        <div class="bg-white rounded-xl shadow-sm p-5">
            <h2 class="font-semibold text-slate-800 mb-1">💵 {{ __('app.items.heading') }}</h2>
            <p class="text-xs text-slate-500 mb-4">{{ __('app.items.hint') }}</p>

            {{-- Existing charges --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm mb-3">
                    <tbody class="divide-y divide-slate-100">
                        <tr class="text-slate-500">
                            <td class="py-2">{{ __('app.items.visit_fee') }} — {{ $appointment->typeLabel() }}</td>
                            <td class="py-2 text-center w-16">1</td>
                            <td class="py-2 text-end w-24">{{ number_format($visitFee, 2) }}</td>
                            <td class="py-2 text-end w-8">
                                <button type="button" onclick="toggle('edit-fee')"
                                        class="text-indigo-600 hover:text-indigo-800 text-xs"
                                        title="{{ __('app.items.edit_fee') }}">✏️</button>
                            </td>
                        </tr>
                        {{-- Discount / surcharge on the visit fee itself --}}
                        <tr id="edit-fee" class="hidden">
                            <td colspan="4" class="py-3">
                                <form method="POST" action="{{ route('practice.appointments.price', $appointment) }}"
                                      class="flex flex-wrap items-end gap-2 bg-indigo-50/60 rounded-lg p-3">
                                    @csrf
                                    <div>
                                        <label class="block text-xs text-slate-500 mb-1">
                                            {{ __('app.items.visit_fee') }} ({{ __('app.clinic.currency') }})
                                        </label>
                                        <input type="number" name="price" step="0.01" min="0" required
                                               value="{{ number_format($visitFee, 2, '.', '') }}"
                                               class="w-32 border rounded px-2 py-1.5 text-sm">
                                    </div>
                                    <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg">
                                        {{ __('app.items.save_fee') }}
                                    </button>
                                    <button type="button" onclick="toggle('edit-fee')"
                                            class="text-sm text-slate-500 px-2">{{ __('app.collection.cancel') }}</button>
                                    <p class="w-full text-xs text-slate-500">{{ __('app.items.fee_hint') }}</p>
                                    <p class="w-full text-xs text-amber-600">{{ __('app.items.fee_reprice_warning') }}</p>
                                </form>
                            </td>
                        </tr>
                        @forelse ($appointment->items as $item)
                            <tr>
                                <td class="py-2 text-slate-800">{{ $item->name }}</td>
                                <td class="py-2 text-center text-slate-500">×{{ $item->quantity }}</td>
                                <td class="py-2 text-end font-medium text-slate-800">{{ number_format($item->total(), 2) }}</td>
                                <td class="py-2 text-end">
                                    <form method="POST" action="{{ route('practice.appointment-items.destroy', $item) }}"
                                          onsubmit="return confirm('{{ __('app.items.remove_confirm') }}')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-500 hover:text-red-700 text-xs">✖</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-2 text-xs text-slate-400 italic">{{ __('app.items.none') }}</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-slate-200 font-semibold text-slate-900">
                            <td class="py-2" colspan="2">{{ __('app.items.total_due') }}</td>
                            <td class="py-2 text-end">{{ number_format($due, 2) }}</td>
                            <td></td>
                        </tr>
                        @if ($paid > 0)
                            <tr class="text-xs text-emerald-700">
                                <td class="py-1" colspan="2">{{ __('app.collection.collected') }}</td>
                                <td class="py-1 text-end">{{ number_format($paid, 2) }}</td>
                                <td></td>
                            </tr>
                        @endif
                        @if ($appointment->refundDue() > 0)
                            {{-- Discounted below what was already paid --}}
                            <tr class="text-xs font-semibold text-red-600">
                                <td class="py-1" colspan="2">↩ {{ __('app.collection.refund_due') }}</td>
                                <td class="py-1 text-end">{{ number_format($appointment->refundDue(), 2) }}</td>
                                <td></td>
                            </tr>
                        @else
                            <tr class="text-xs {{ $appointment->remainingAmount() > 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                                <td class="py-1" colspan="2">{{ __('app.collection.remaining') }}</td>
                                <td class="py-1 text-end">{{ number_format($appointment->remainingAmount(), 2) }}</td>
                                <td></td>
                            </tr>
                        @endif
                    </tfoot>
                </table>
            </div>

            {{-- Add a charge: pick from the price list, or name a new one --}}
            <form method="POST" action="{{ route('practice.appointment-items.store', $appointment) }}"
                  class="flex flex-wrap items-end gap-2 border-t border-slate-100 pt-4" id="add-item-form">
                @csrf
                <div class="grow min-w-[12rem]">
                    <label class="block text-xs text-slate-500 mb-1">{{ __('app.items.item') }}</label>
                    <select name="billable_item_id" id="item-select" class="w-full border rounded px-2 py-1.5 text-sm">
                        <option value="">{{ __('app.items.select') }}</option>
                        @foreach ($billableItems as $bi)
                            <option value="{{ $bi->id }}">{{ $bi->name }} — {{ number_format((float) $bi->price, 2) }} {{ __('app.clinic.currency') }}</option>
                        @endforeach
                        <option value="__new__">{{ __('app.items.new') }}</option>
                    </select>
                </div>

                {{-- Revealed when "New item…" is chosen --}}
                <div id="new-item-fields" class="hidden flex flex-wrap items-end gap-2">
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">{{ __('app.items.new_name') }}</label>
                        <input type="text" name="new_name" id="new-name" class="w-40 border rounded px-2 py-1.5 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-1">{{ __('app.items.new_price') }}</label>
                        <input type="number" name="new_price" id="new-price" step="0.01" min="0" value="0"
                               class="w-24 border rounded px-2 py-1.5 text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs text-slate-500 mb-1">{{ __('app.items.quantity') }}</label>
                    <input type="number" name="quantity" value="1" min="1" max="999"
                           class="w-20 border rounded px-2 py-1.5 text-sm">
                </div>
                <button class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm px-4 py-2 rounded-lg">
                    {{ __('app.items.add') }}
                </button>
            </form>
        </div>
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

    // "New item…" swaps the catalog picker for name + price inputs. The select
    // is cleared so the controller sees no billable_item_id and takes the
    // new-item branch.
    (function () {
        var select = document.getElementById('item-select');
        var fields = document.getElementById('new-item-fields');
        if (!select || !fields) return;

        function sync() {
            var isNew = select.value === '__new__';
            fields.classList.toggle('hidden', !isNew);
            document.getElementById('new-name').required = isNew;
            if (isNew) { document.getElementById('new-name').focus(); }
        }
        select.addEventListener('change', sync);

        document.getElementById('add-item-form').addEventListener('submit', function () {
            if (select.value === '__new__') { select.value = ''; }
        });

        @if ($errors->any() && old('new_name'))
            select.value = '__new__';
        @endif
        sync();
    })();

    // ---- Requests: autocomplete the name field from the selected type ----
    // Suggestions come from the medical-tests catalogue; typing a value that
    // isn't listed is still allowed (native <datalist> behaviour).
    const TEST_SUGGESTIONS = @json($testSuggestions);
    (function () {
        const type = document.getElementById('req-type');
        const list = document.getElementById('req-suggestions');
        const nameInput = document.getElementById('req-name');
        if (!type || !list) return;

        function fillSuggestions(clearStale) {
            const names = TEST_SUGGESTIONS[type.value] || [];
            list.replaceChildren(...names.map(function (n) {
                const opt = document.createElement('option');
                opt.value = n;
                return opt;
            }));
            // On a user-initiated type switch, drop a value that doesn't belong
            // to the new type so the picker starts fresh (free text still allowed).
            if (clearStale && nameInput && nameInput.value && !names.includes(nameInput.value)) {
                nameInput.value = '';
            }
        }
        type.addEventListener('change', function () { fillSuggestions(true); });
        fillSuggestions(false);
    })();

    // ---- Medical plans: load into the Rx table / save current rows as a plan ----
    @php
        $plansPayload = $medicalPlans->mapWithKeys(fn ($p) => [$p->id => [
            'id' => $p->id,
            'items' => $p->items->map(fn ($i) => [
                'medicine_name' => $i->medicine_name,
                'dose' => $i->dose,
                'frequency' => $i->frequency,
                'duration' => $i->duration,
                'instructions' => $i->instructions,
            ])->values(),
        ]]);
    @endphp
    const MEDICAL_PLANS = @json($plansPayload);

    function addRxRowWith(v) {
        addRxRow();
        const rows = document.querySelectorAll('#rx-table tbody tr.rx-row');
        const tr = rows[rows.length - 1];
        ['medicine_name', 'dose', 'frequency', 'duration', 'instructions'].forEach(function (k) {
            const input = tr.querySelector('input[name$="[' + k + ']"]');
            if (input) input.value = v[k] || '';
        });
    }

    function loadPlan() {
        const sel = document.getElementById('plan-select');
        const plan = MEDICAL_PLANS[sel.value];
        if (!plan) return;

        // Drop the first row if it's still empty, so a fresh form isn't left blank.
        const first = document.querySelector('#rx-table tbody tr.rx-row input[name$="[medicine_name]"]');
        if (first && !first.value.trim() && document.querySelectorAll('#rx-table tbody tr.rx-row').length === 1) {
            document.querySelector('#rx-table tbody tr.rx-row').remove();
        }
        (plan.items || []).forEach(addRxRowWith);
    }

    function saveAsPlan() {
        const title = (document.getElementById('plan-title-input').value || '').trim();
        if (!title) { alert(@json(__('app.plan.title_required'))); return; }

        const container = document.getElementById('save-plan-items');
        container.innerHTML = '';
        let i = 0;
        document.querySelectorAll('#rx-table tbody tr.rx-row').forEach(function (tr) {
            const name = tr.querySelector('input[name$="[medicine_name]"]');
            if (!name || !name.value.trim()) return;
            ['medicine_name', 'dose', 'frequency', 'duration', 'instructions'].forEach(function (k) {
                const input = tr.querySelector('input[name$="[' + k + ']"]');
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'items[' + i + '][' + k + ']';
                hidden.value = input ? input.value : '';
                container.appendChild(hidden);
            });
            i++;
        });

        if (i === 0) { alert(@json(__('app.plan.needs_item'))); return; }
        document.getElementById('save-plan-title').value = title;
        document.getElementById('save-plan-form').submit();
    }
</script>
@endpush
@endsection
