@extends('clinic.layouts.app', ['title' => __('app.examine.title', ['name' => $appointment->client->name])])

@section('content')
@php
    $p = $appointment->client;
    $isOpen = ! in_array($appointment->status, ['completed', 'cancelled']);
    $hasAllergies = filled($p->allergies);
    $allergyList = $hasAllergies ? implode('، ', (array) $p->allergies) : null;
    $chronicList = filled($p->chronic_diseases) ? implode('، ', (array) $p->chronic_diseases) : null;
    $genderLabel = in_array($p->gender, ['male', 'female'], true) ? __('app.genders.'.$p->gender) : ($p->gender ?: '—');
    $history = $p->diagnoses->where('appointment_id', '!=', $appointment->id);
    $statusTone = match ($appointment->status) {
        'under_examination' => 'bg-amber-100 text-amber-800',
        'completed' => 'bg-emerald-100 text-emerald-800',
        'cancelled' => 'bg-red-100 text-red-700',
        default => 'bg-slate-200 text-slate-700',
    };
    // Which tab of the investigations card to open when a submit there failed
    // validation: the form that carries the error must stay in view.
    $defaultTab = ($errors->has('files') || $errors->has('type')) ? 'results' : '';
@endphp

{{-- Page header: who is being seen. --}}
<div class="mb-3">
    <a href="{{ route('practice.doctor.dashboard') }}" class="text-sm text-indigo-600 hover:underline">{{ __('app.examine.back_to_dashboard') }}</a>
    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 mt-1 leading-tight">{{ __('app.examine.title', ['name' => $p->name]) }}</h1>
    <p class="text-slate-500 text-sm">
        {{ __('app.examine.queue', ['num' => $appointment->queue_number]) }} &middot; {{ $appointment->typeLabel() }}
        &middot; {{ $appointment->scheduled_at->format('H:i') }}
    </p>
</div>

{{-- Action bar: sticks to the top so the visit's controls are always one tap
     away, however far down the doctor has scrolled. Calling the next patient
     fires the same action as the dashboard's button, so the waiting-room
     counter still announces them; once they have started, this screen moves
     on to them. --}}
<div id="examine-bar" class="sticky top-0 z-30 -mx-4 px-4 py-2 mb-5 bg-slate-100/95 backdrop-blur border-b border-slate-200">
    <div class="flex items-center justify-between gap-3">
        <div class="min-w-0 flex items-center gap-2 text-sm" data-next-patient>
            <span class="shrink-0 text-xs font-semibold px-2 py-1 rounded-full {{ $statusTone }}">{{ $appointment->statusLabel() }}</span>
            <span class="hidden sm:inline min-w-0 truncate text-slate-500">
                @if ($nextPatient)
                    {{ __('app.examine.next_up') }}:
                    <span class="font-medium text-slate-800">
                        @if ($nextPatient->queue_number)#{{ $nextPatient->queue_number }} · @endif{{ $nextPatient->client?->name }}
                    </span>
                    <span class="text-slate-400">· {{ $nextPatient->scheduled_at?->format('H:i') }}</span>
                @else
                    {{ __('app.examine.waiting_none') }}
                @endif
            </span>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            @if ($appointment->status === 'scheduled')
                <form method="POST" action="{{ route('practice.appointments.status', $appointment) }}">
                    @csrf <input type="hidden" name="status" value="under_examination">
                    <button class="btn btn-start btn-sm" title="{{ __('app.examine.start_examination') }}">
                        <span aria-hidden="true">▶</span><span class="hidden sm:inline">{{ __('app.examine.start_examination') }}</span>
                        <span class="sr-only sm:hidden">{{ __('app.examine.start_examination') }}</span>
                    </button>
                </form>
            @endif
            @if ($isOpen)
                <form method="POST" action="{{ route('practice.appointments.status', $appointment) }}">
                    @csrf <input type="hidden" name="status" value="completed">
                    <button class="btn btn-complete btn-sm" title="{{ __('app.examine.complete') }}">
                        <span aria-hidden="true">✔</span><span class="hidden sm:inline">{{ __('app.examine.complete') }}</span>
                        <span class="sr-only sm:hidden">{{ __('app.examine.complete') }}</span>
                    </button>
                </form>
            @endif
            <button type="button" id="examine-next-btn"
                    data-url="{{ route('practice.display.next', $appointment->clinic_id) }}"
                    data-examine-base="{{ url('practice/doctor/appointments') }}"
                    class="btn btn-secondary btn-sm" title="{{ __('app.display.next') }}"
                    @disabled(! $nextPatient)>
                <span aria-hidden="true">⏭</span><span class="hidden sm:inline">{{ __('app.display.next') }}</span>
                <span class="sr-only sm:hidden">{{ __('app.display.next') }}</span>
            </button>
        </div>
    </div>
    {{-- Laptop: jump straight to a part of the visit. --}}
    <nav class="hidden lg:flex items-center gap-4 mt-1.5 text-xs text-slate-500" aria-label="{{ __('app.examine.jump_to') }}">
        <span class="text-slate-400">{{ __('app.examine.jump_to') }}:</span>
        <a href="#diagnosis" class="hover:text-indigo-700">{{ __('app.examine.history_diagnosis') }}</a>
        <a href="#investigations" class="hover:text-indigo-700">{{ __('app.examine.investigations') }}</a>
        <a href="#prescription" class="hover:text-indigo-700">{{ __('app.examine.history_prescription') }}</a>
        <a href="#charges" class="hover:text-indigo-700">{{ __('app.items.heading') }}</a>
        <a href="#patient-info" class="hover:text-indigo-700">{{ __('app.examine.patient_info') }}</a>
    </nav>
</div>

{{-- Phone and tablet: the facts that matter before the diagnosis, in one line.
     The full patient card sits below the visit on these widths. --}}
<div class="lg:hidden mb-5 card px-4 py-3 text-sm flex flex-wrap items-center gap-x-3 gap-y-1.5">
    <span class="text-slate-700">{{ $genderLabel }} · {{ $p->age ?? '—' }}</span>
    @if ($p->phone_number)<span class="text-slate-500" dir="ltr">{{ $p->phone_number }}</span>@endif
    @if ($hasAllergies)
        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-red-100 text-red-700">{{ __('app.common.allergies') }}: {{ $allergyList }}</span>
    @endif
    @if ($chronicList)
        <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">{{ $chronicList }}</span>
    @endif
    <a href="#patient-info" class="ms-auto text-xs text-indigo-600 hover:underline">{{ __('app.patient.edit') }}</a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 lg:gap-6">
    {{-- The visit itself. First on phones and tablets, beside the patient card on laptops. --}}
    <div class="lg:col-span-2 order-1 lg:order-2 space-y-5">
        {{-- Diagnosis + treatment plan --}}
        <section id="diagnosis" class="card p-4 sm:p-5">
            <h2 class="text-base font-semibold text-slate-900 mb-3">{{ __('app.examine.diagnosis_section') }}</h2>
            <form method="POST" action="{{ route('practice.doctor.diagnosis.store', $appointment) }}" class="space-y-3">
                @csrf
                <div>
                    <label for="diagnosis-text" class="block text-sm text-slate-500 mb-1">{{ __('app.examine.diagnosis_label') }}</label>
                    <textarea name="diagnosis" id="diagnosis-text" rows="3" required
                              class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('diagnosis', $appointment->diagnosis?->diagnosis) }}</textarea>
                </div>
                <div>
                    <label for="treatment-plan" class="block text-sm text-slate-500 mb-1">{{ __('app.examine.treatment_plan') }}</label>
                    <textarea name="treatment_plan" id="treatment-plan" rows="3"
                              class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('treatment_plan', $appointment->diagnosis?->treatment_plan) }}</textarea>
                </div>
                <div>
                    <label for="diagnosis-notes" class="block text-sm text-slate-500 mb-1">{{ __('app.common.notes') }}</label>
                    <textarea name="notes" id="diagnosis-notes" rows="2"
                              class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('notes', $appointment->diagnosis?->notes) }}</textarea>
                </div>
                <button class="btn btn-primary">{{ __('app.examine.save_diagnosis') }}</button>
            </form>
        </section>

        {{-- The specialisation's body chart. Renders nothing for specialisations
             that do not use one. --}}
        @include('clinic.partials.clinical-chart', ['appointment' => $appointment, 'doctor' => $doctor])

        {{-- Investigations: what was requested, what came back, and what the labs
             system holds. One card, three tabs. --}}
        <section id="investigations" class="card" data-default-tab="{{ $defaultTab }}">
            <div class="px-4 sm:px-5 pt-4 sm:pt-5">
                <h2 class="text-base font-semibold text-slate-900">{{ __('app.examine.investigations') }}</h2>
            </div>
            <div class="px-4 sm:px-5 mt-2 flex gap-1 border-b border-slate-200 overflow-x-auto overflow-y-hidden tab-strip" role="tablist">
                <button type="button" role="tab" data-tab="requested" aria-selected="true"
                        class="shrink-0 -mb-px px-3 py-2 text-sm font-medium border-b-2 border-indigo-600 text-indigo-700">
                    {{ __('app.examine.tab_requested') }}
                    <span class="ms-1 text-xs px-1.5 py-0.5 rounded-full bg-slate-100 text-slate-600">{{ $appointment->medicalRequests->count() }}</span>
                </button>
                <button type="button" role="tab" data-tab="results" aria-selected="false"
                        class="shrink-0 -mb-px px-3 py-2 text-sm font-medium border-b-2 border-transparent text-slate-500 hover:text-slate-800">
                    {{ __('app.examine.tab_results') }}
                    <span class="ms-1 text-xs px-1.5 py-0.5 rounded-full bg-slate-100 text-slate-600">{{ $p->patientTests->count() }}</span>
                </button>
                <button type="button" role="tab" data-tab="labs" aria-selected="false"
                        class="shrink-0 -mb-px px-3 py-2 text-sm font-medium border-b-2 border-transparent text-slate-500 hover:text-slate-800">
                    {{ __('app.examine.tab_from_labs') }}
                    <span class="ms-1 text-xs px-1.5 py-0.5 rounded-full bg-slate-100 text-slate-600">{{ $labResults->count() }}</span>
                </button>
            </div>

            {{-- Requested examinations / tests / radiology --}}
            <div data-tab-panel="requested" class="p-4 sm:p-5">
                <ul class="space-y-2 text-sm mb-4">
                    @forelse ($appointment->medicalRequests as $req)
                        <li class="flex items-center justify-between gap-2 border-b border-slate-50 pb-1">
                            <span class="min-w-0"><span class="text-xs px-2 py-0.5 rounded bg-slate-100 me-2">{{ $req->typeLabel() }}</span>{{ $req->name }}</span>
                            <form method="POST" action="{{ route('practice.doctor.requests.destroy', $req) }}">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm">{{ __('app.common.remove') }}</button>
                            </form>
                        </li>
                    @empty
                        <li class="text-slate-400 italic">{{ __('app.examine.no_requests') }}</li>
                    @endforelse
                </ul>
                <form method="POST" action="{{ route('practice.doctor.requests.store', $appointment) }}" class="flex flex-wrap items-end gap-2">
                    @csrf
                    <div>
                        <label for="req-type" class="block text-xs text-slate-500 mb-1">{{ __('app.examine.test_type') }}</label>
                        <select name="type" id="req-type" class="border rounded px-2 py-1.5 text-sm">
                            <option value="examination">{{ __('app.request_types.examination') }}</option>
                            <option value="lab_test">{{ __('app.request_types.lab_test') }}</option>
                            <option value="radiology">{{ __('app.request_types.radiology') }}</option>
                        </select>
                    </div>
                    <div class="flex-1 min-w-[180px]">
                        <label for="req-name" class="block text-xs text-slate-500 mb-1">{{ __('app.examine.test_title') }}</label>
                        <input type="text" name="name" id="req-name" list="req-suggestions" autocomplete="off"
                               placeholder="{{ __('app.examine.request_name_placeholder') }}" required
                               class="w-full border rounded px-2 py-1.5 text-sm">
                        <datalist id="req-suggestions"></datalist>
                    </div>
                    <button class="btn btn-primary btn-sm">{{ __('app.examine.add') }}</button>
                </form>
            </div>

            {{-- Results attached here: files from the patient, across visits --}}
            <div data-tab-panel="results" class="p-4 sm:p-5 hidden">
                <ul class="space-y-3 text-sm mb-4">
                    @forelse ($p->patientTests as $test)
                        <li class="border border-slate-100 rounded-lg p-3">
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <div class="min-w-0">
                                    <span class="text-xs px-2 py-0.5 rounded {{ $test->type === 'radiology' ? 'bg-purple-100 text-purple-700' : 'bg-teal-100 text-teal-700' }}">
                                        {{ $test->typeLabel() }}
                                    </span>
                                    @if ($test->title)<span class="text-slate-800 mx-1">{{ $test->title }}</span>@endif
                                    <span class="text-xs text-slate-400 mx-1">{{ $test->created_at->format('Y-m-d') }}</span>
                                </div>
                                <form method="POST" action="{{ route('practice.doctor.tests.destroy', $test) }}"
                                      onsubmit="return confirm('{{ __('app.examine.test_remove_confirm') }}')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm">{{ __('app.common.remove') }}</button>
                                </form>
                            </div>
                            @if ($test->notes)<p class="text-xs text-slate-500 mb-2">{{ $test->notes }}</p>@endif
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

                <button type="button" onclick="toggle('attach-test-form')" class="btn btn-secondary btn-sm">
                    + {{ __('app.examine.attach_result_toggle') }}
                </button>
                <form method="POST" action="{{ route('practice.doctor.tests.store', $appointment) }}"
                      enctype="multipart/form-data" id="attach-test-form"
                      class="{{ $defaultTab === 'results' ? '' : 'hidden' }} space-y-3 mt-3 rounded-lg border border-slate-200 bg-slate-50/60 p-3">
                    @csrf
                    <div class="flex flex-wrap items-end gap-2">
                        <div>
                            <label for="test-type" class="block text-xs text-slate-500 mb-1">{{ __('app.examine.test_type') }}</label>
                            <select name="type" id="test-type" class="border rounded px-2 py-1.5 text-sm">
                                <option value="lab">{{ __('app.test_types.lab') }}</option>
                                <option value="radiology">{{ __('app.test_types.radiology') }}</option>
                            </select>
                        </div>
                        <div class="flex-1 min-w-[180px]">
                            <label for="test-title" class="block text-xs text-slate-500 mb-1">{{ __('app.examine.test_title') }}</label>
                            <input type="text" name="title" id="test-title" placeholder="{{ __('app.examine.test_title_placeholder') }}"
                                   class="w-full border rounded px-2 py-1.5 text-sm">
                        </div>
                    </div>
                    <div>
                        <span class="block text-xs text-slate-500 mb-1">{{ __('app.examine.test_files') }}</span>
                        <label class="file-field relative">
                            <input type="file" name="files[]" multiple required>
                            <span class="btn btn-secondary btn-sm">{{ __('app.examine.choose_files') }}</span>
                            <span class="file-name" data-empty="{{ __('app.examine.no_file_chosen') }}">{{ __('app.examine.no_file_chosen') }}</span>
                        </label>
                        <p class="text-[11px] text-slate-400 mt-1">{{ __('app.examine.test_files_hint') }}</p>
                    </div>
                    <div>
                        <label for="test-notes" class="block text-xs text-slate-500 mb-1">{{ __('app.common.notes') }}</label>
                        <textarea name="notes" id="test-notes" rows="2" class="w-full border rounded px-2 py-1.5 text-sm"></textarea>
                    </div>
                    <button class="btn btn-primary btn-sm">{{ __('app.examine.add_test') }}</button>
                </form>
            </div>

            {{-- Results held by the labs system --}}
            <div data-tab-panel="labs" class="p-4 sm:p-5 hidden">
                @include('clinic.partials.lab-results', ['embedded' => true])
            </div>
        </section>

        {{-- Medical history: this patient's examinations across visits & doctors.
             The acting doctor's own records are editable; others are view-only. --}}
        <section id="history" class="card p-4 sm:p-5">
            <div class="flex items-center justify-between gap-2">
                <h2 class="text-base font-semibold text-slate-900">{{ __('app.examine.history_section') }}</h2>
                <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">{{ $history->count() }}</span>
            </div>
            @if ($history->isEmpty())
                <p class="text-sm text-slate-400 mt-1">{{ __('app.examine.no_history') }}</p>
            @else
                <p class="text-xs text-slate-500 mt-1 mb-4">{{ __('app.examine.history_hint') }}</p>
                @foreach ($history as $past)
                    @php $mine = $past->doctor_id === $actingDoctorId; @endphp
                    <div class="border border-slate-100 rounded-lg p-3 mb-3">
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <div class="text-sm min-w-0">
                                <span class="font-medium text-slate-700">{{ $past->doctor?->name ?? __('app.common.none') }}</span>
                                <span class="text-xs text-slate-400 mx-1">{{ optional($past->appointment?->scheduled_at ?? $past->created_at)->format('Y-m-d') }}</span>
                            </div>
                            @if ($mine)
                                <span class="shrink-0 text-xs px-2 py-0.5 rounded bg-emerald-100 text-emerald-700">{{ __('app.examine.history_mine') }}</span>
                            @else
                                <span class="shrink-0 text-xs px-2 py-0.5 rounded bg-slate-100 text-slate-500">{{ __('app.examine.history_view_only') }}</span>
                            @endif
                        </div>

                        @if ($mine)
                            <form method="POST" action="{{ route('practice.doctor.diagnoses.update', $past) }}" class="space-y-2">
                                @csrf @method('PUT')
                                <div>
                                    <label for="past-dx-{{ $past->id }}" class="block text-xs text-slate-400 mb-1">{{ __('app.examine.history_diagnosis') }}</label>
                                    <textarea name="diagnosis" id="past-dx-{{ $past->id }}" rows="2" required
                                              class="w-full border rounded px-2 py-1.5 text-sm">{{ $past->diagnosis }}</textarea>
                                </div>
                                <div>
                                    <label for="past-plan-{{ $past->id }}" class="block text-xs text-slate-400 mb-1">{{ __('app.examine.treatment_plan') }}</label>
                                    <textarea name="treatment_plan" id="past-plan-{{ $past->id }}" rows="2"
                                              class="w-full border rounded px-2 py-1.5 text-sm">{{ $past->treatment_plan }}</textarea>
                                </div>
                                <div>
                                    <label for="past-notes-{{ $past->id }}" class="block text-xs text-slate-400 mb-1">{{ __('app.common.notes') }}</label>
                                    <textarea name="notes" id="past-notes-{{ $past->id }}" rows="1"
                                              class="w-full border rounded px-2 py-1.5 text-sm">{{ $past->notes }}</textarea>
                                </div>
                                <button class="btn btn-primary btn-sm">{{ __('app.examine.history_save') }}</button>
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

                        {{-- What was prescribed that day, with its medicines. --}}
                        @foreach ($past->appointment?->prescriptions ?? [] as $pastRx)
                            <div class="mt-3 pt-3 border-t border-dashed border-slate-200">
                                <div class="flex items-center justify-between gap-2 mb-1.5">
                                    <span class="text-xs font-semibold text-slate-700">
                                        {{ __('app.examine.history_prescription') }}
                                        <span class="font-mono font-normal text-slate-400">{{ $pastRx->code }}</span>
                                    </span>
                                    <a href="{{ route('practice.prescriptions.print', $pastRx) }}" target="_blank"
                                       class="text-xs text-indigo-600 hover:underline">{{ __('app.common.print') }}</a>
                                </div>
                                <ol class="text-sm text-slate-700 space-y-0.5 ps-4 list-decimal">
                                    @foreach ($pastRx->items as $it)
                                        <li>
                                            <span class="font-medium">{{ $it->medicine_name }}</span>
                                            @php $meta = array_filter([$it->dose, $it->frequency, $it->duration]); @endphp
                                            @if ($meta)<span class="text-xs text-slate-500"> — {{ implode(' · ', $meta) }}</span>@endif
                                            @if ($it->substitute_name)<span class="text-xs text-teal-700"> ↔ {{ $it->substitute_name }}</span>@endif
                                        </li>
                                    @endforeach
                                </ol>
                                @if ($pastRx->sick_leave_days)
                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ __('app.print.sick_leave') }}:
                                        {{ trans_choice('app.print.sick_leave_days', $pastRx->sick_leave_days, ['count' => $pastRx->sick_leave_days]) }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endforeach
            @endif
        </section>

        {{-- Prescription / medicines --}}
        <section id="prescription" class="card p-4 sm:p-5">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                <h2 class="text-base font-semibold text-slate-900">{{ __('app.examine.new_prescription') }}</h2>
                @if ($medicalPlans->isNotEmpty())
                    {{-- Start from a saved plan --}}
                    <div class="flex items-center gap-2">
                        <label for="plan-select" class="sr-only">{{ __('app.plan.load_label') }}</label>
                        <select id="plan-select" class="border rounded px-2 py-1.5 text-sm max-w-[13rem]">
                            <option value="">— {{ __('app.plan.select') }} —</option>
                            @foreach ($medicalPlans as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->title }}</option>
                            @endforeach
                        </select>
                        <button type="button" onclick="loadPlan()" class="btn btn-secondary btn-sm">{{ __('app.plan.load_button') }}</button>
                    </div>
                @endif
            </div>

            <form method="POST" action="{{ route('practice.doctor.prescriptions.store', $appointment) }}">
                @csrf
                <p class="text-[11px] text-slate-400 mb-2">{{ __('app.examine.medicine_search_hint') }}</p>
                <div id="rx-rows">
                    @include('clinic.partials.rx-row', ['index' => 0])
                </div>

                {{-- Cloned by addRxRow(); same partial as the first row above. --}}
                <template id="rx-row-template">
                    @include('clinic.partials.rx-row', ['index' => '__INDEX__'])
                </template>

                <button type="button" onclick="addRxRow()" class="btn btn-ghost btn-sm mb-3">{{ __('app.examine.add_medicine') }}</button>
                <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="md:col-span-2">
                        <label for="rx-notes" class="block text-sm text-slate-500 mb-1">{{ __('app.examine.prescription_notes') }}</label>
                        <textarea name="notes" id="rx-notes" rows="2" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
                    </div>
                    <div>
                        <label for="sick_leave_days" class="block text-sm text-slate-500 mb-1">{{ __('app.examine.sick_leave') }}</label>
                        <input type="number" name="sick_leave_days" id="sick_leave_days"
                               min="1" max="365" inputmode="numeric"
                               placeholder="{{ __('app.examine.sick_leave_placeholder') }}"
                               class="w-full border rounded-lg px-3 py-2 text-sm">
                        <p class="mt-1 text-[11px] text-slate-400">{{ __('app.examine.sick_leave_hint') }}</p>
                        @error('sick_leave_days')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <button class="btn btn-primary">{{ __('app.examine.create_prescription') }}</button>
                    {{-- Keep these rows as a reusable plan: secondary, out of the way. --}}
                    <details class="relative">
                        <summary class="btn btn-secondary btn-sm list-none select-none">{{ __('app.plan.save_as_button') }}</summary>
                        <div class="absolute end-0 mt-2 w-72 max-w-[calc(100vw-2rem)] bg-white border border-slate-200 rounded-xl shadow-lg p-3 z-20">
                            <label for="plan-title-input" class="block text-xs text-slate-500 mb-1">{{ __('app.plan.save_as_label') }}</label>
                            <div class="flex items-center gap-2">
                                <input id="plan-title-input" type="text" placeholder="{{ __('app.plan.title_label') }}"
                                       class="flex-1 min-w-0 border rounded px-2 py-1.5 text-sm">
                                <button type="button" onclick="saveAsPlan()" class="btn btn-primary btn-sm">{{ __('app.plan.save_as_button') }}</button>
                            </div>
                        </div>
                    </details>
                </div>
            </form>
        </section>

        {{-- Hidden form used to POST "save as plan" without touching the Rx form. --}}
        <form id="save-plan-form" method="POST" action="{{ route('practice.doctor.setup.medical-plans.store') }}" class="hidden">
            @csrf
            <input type="hidden" name="title" id="save-plan-title">
            <div id="save-plan-items"></div>
        </form>

        {{-- Prescriptions already issued on this visit --}}
        @if ($appointment->prescriptions->isNotEmpty())
            <section class="card p-4 sm:p-5">
                <h2 class="text-base font-semibold text-slate-900 mb-3">{{ __('app.examine.prescriptions') }}</h2>
                <ul class="space-y-3 text-sm">
                    @foreach ($appointment->prescriptions as $rx)
                        <li class="border-b border-slate-50 pb-3 last:border-0 last:pb-0">
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <div>
                                    <span class="font-mono text-xs bg-slate-100 px-2 py-0.5 rounded">{{ $rx->code }}</span>
                                    <span class="text-slate-500 mx-2">{{ __('app.examine.medicines_count', ['count' => $rx->items->count()]) }}</span>
                                </div>
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <a href="{{ route('practice.prescriptions.print', ['prescription' => $rx, 'auto' => 1]) }}" target="_blank"
                                       class="btn btn-secondary btn-sm">🖨️ {{ __('app.print.print_pdf') }}</a>
                                    <a href="{{ route('practice.prescriptions.pdf', ['prescription' => $rx, 'download' => 1]) }}"
                                       class="btn btn-secondary btn-sm">⬇️ {{ __('app.print.download_pdf') }}</a>
                                    <button type="button" onclick="printRxThermal(this, {{ $rx->id }})"
                                            class="btn btn-secondary btn-sm">🧾 {{ __('app.print.print_thermal') }}</button>
                                </div>
                            </div>
                            {{-- Medicines with their optional alternative (both names shown). --}}
                            <ul class="mt-2 ps-1 space-y-0.5 text-xs text-slate-600">
                                @foreach ($rx->items as $it)
                                    <li>
                                        <span class="font-medium text-slate-800">{{ $loop->iteration }}. {{ $it->medicine_name }}</span>
                                        @if ($it->substitute_name)
                                            <span class="text-teal-700">↔ {{ __('app.print.substitute') }}: {{ $it->substitute_name }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        {{-- Custom examination fields defined by the doctor --}}
        <section class="card p-4 sm:p-5">
            <div class="flex items-center justify-between gap-2 {{ $examinationFields->isEmpty() ? '' : 'mb-3' }}">
                <h2 class="text-base font-semibold text-slate-900">{{ __('app.field.title_plural') }}</h2>
                <a href="{{ route('practice.doctor.setup.examination-fields') }}" class="text-xs text-indigo-600 hover:underline">{{ __('app.field.manage') }}</a>
            </div>
            @if ($examinationFields->isEmpty())
                <p class="text-sm text-slate-400 mt-1">{{ __('app.field.none_hint') }}</p>
            @else
                <form method="POST" action="{{ route('practice.doctor.examination-values.store', $appointment) }}"
                      enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach ($examinationFields as $field)
                            @php $val = $examinationValues[$field->id] ?? null; $fid = 'field-'.$field->id; @endphp
                            <div>
                                <label for="{{ $fid }}" class="block text-sm text-slate-500 mb-1">{{ $field->label }}</label>
                                @switch($field->type)
                                    @case('select')
                                        <select name="fields[{{ $field->id }}]" id="{{ $fid }}" class="w-full border rounded-lg px-3 py-2 text-sm">
                                            <option value="">—</option>
                                            @foreach ($field->optionsArray() as $opt)
                                                <option value="{{ $opt }}" @selected($val && $val->value === $opt)>{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                        @break
                                    @case('number')
                                        <input type="number" step="any" name="fields[{{ $field->id }}]" id="{{ $fid }}" value="{{ $val?->value }}"
                                               class="w-full border rounded-lg px-3 py-2 text-sm">
                                        @break
                                    @case('percentage')
                                        <div class="relative">
                                            <input type="number" step="any" min="0" max="100" name="fields[{{ $field->id }}]" id="{{ $fid }}" value="{{ $val?->value }}"
                                                   class="w-full border rounded-lg px-3 py-2 pe-8 text-sm">
                                            <span class="absolute end-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">%</span>
                                        </div>
                                        @break
                                    @case('file')
                                        @if ($val && $val->attachment)
                                            <a href="{{ asset('storage/'.$val->attachment->file_path) }}" target="_blank"
                                               class="block text-xs text-indigo-600 hover:underline mb-1">📎 {{ $val->attachment->file_name }}</a>
                                        @endif
                                        <label class="file-field relative">
                                            <input type="file" name="field_files[{{ $field->id }}]" id="{{ $fid }}">
                                            <span class="btn btn-secondary btn-sm">{{ __('app.examine.choose_file') }}</span>
                                            <span class="file-name" data-empty="{{ __('app.examine.no_file_chosen') }}">{{ __('app.examine.no_file_chosen') }}</span>
                                        </label>
                                        @break
                                    @default
                                        <input type="text" name="fields[{{ $field->id }}]" id="{{ $fid }}" value="{{ $val?->value }}"
                                               class="w-full border rounded-lg px-3 py-2 text-sm">
                                @endswitch
                            </div>
                        @endforeach
                    </div>
                    <button class="btn btn-primary">{{ __('app.field.save_values') }}</button>
                </form>
            @endif
        </section>

        {{-- Charges to collect: visit fee + any extras added here --}}
        @php
            $visitFee = $appointment->visitPrice();
            $due = $appointment->dueAmount();
            $paid = $appointment->collectedAmount();
        @endphp
        <section id="charges" class="card p-4 sm:p-5">
            <h2 class="text-base font-semibold text-slate-900 mb-1">{{ __('app.items.heading') }}</h2>
            <p class="text-xs text-slate-500 mb-4">{{ __('app.items.hint') }}</p>

            {{-- Existing charges --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm mb-3">
                    <tbody class="divide-y divide-slate-100">
                        <tr class="text-slate-500">
                            <td class="py-2">{{ __('app.items.visit_fee') }} — {{ $appointment->typeLabel() }}</td>
                            <td class="py-2 text-center w-16">1</td>
                            <td class="py-2 text-end w-24">{{ number_format($visitFee, 2) }}</td>
                            <td class="py-2 text-end w-10">
                                <button type="button" onclick="toggle('edit-fee')" class="btn btn-ghost btn-sm"
                                        aria-label="{{ __('app.items.edit_fee') }}" title="{{ __('app.items.edit_fee') }}">✏️</button>
                            </td>
                        </tr>
                        {{-- Discount / surcharge on the visit fee itself --}}
                        <tr id="edit-fee" class="hidden">
                            <td colspan="4" class="py-3">
                                <form method="POST" action="{{ route('practice.appointments.price', $appointment) }}"
                                      class="flex flex-wrap items-end gap-2 bg-indigo-50/60 rounded-lg p-3">
                                    @csrf
                                    <div>
                                        <label for="visit-price" class="block text-xs text-slate-500 mb-1">
                                            {{ __('app.items.visit_fee') }} ({{ __('app.clinic.currency') }})
                                        </label>
                                        <input type="number" name="price" id="visit-price" step="0.01" min="0" required
                                               value="{{ number_format($visitFee, 2, '.', '') }}"
                                               class="w-32 border rounded px-2 py-1.5 text-sm">
                                    </div>
                                    <button class="btn btn-primary btn-sm">{{ __('app.items.save_fee') }}</button>
                                    <button type="button" onclick="toggle('edit-fee')" class="btn btn-ghost btn-sm">{{ __('app.collection.cancel') }}</button>
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
                                        <button class="btn btn-danger btn-sm" aria-label="{{ __('app.common.remove') }}">✖</button>
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
                    <label for="item-select" class="block text-xs text-slate-500 mb-1">{{ __('app.items.item') }}</label>
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
                        <label for="new-name" class="block text-xs text-slate-500 mb-1">{{ __('app.items.new_name') }}</label>
                        <input type="text" name="new_name" id="new-name" class="w-40 border rounded px-2 py-1.5 text-sm">
                    </div>
                    <div>
                        <label for="new-price" class="block text-xs text-slate-500 mb-1">{{ __('app.items.new_price') }}</label>
                        <input type="number" name="new_price" id="new-price" step="0.01" min="0" value="0"
                               class="w-24 border rounded px-2 py-1.5 text-sm">
                    </div>
                </div>

                <div>
                    <label for="item-qty" class="block text-xs text-slate-500 mb-1">{{ __('app.items.quantity') }}</label>
                    <input type="number" name="quantity" id="item-qty" value="1" min="1" max="999"
                           class="w-20 border rounded px-2 py-1.5 text-sm">
                </div>
                <button class="btn btn-primary btn-sm">{{ __('app.items.add') }}</button>
            </form>
        </section>
    </div>

    {{-- The patient: summary open, the seldom-used editors folded away. --}}
    <div class="order-2 lg:order-1 space-y-5">
        <section id="patient-info" class="card p-4 sm:p-5">
            <div class="flex items-center justify-between gap-2 mb-3">
                <h2 class="text-base font-semibold text-slate-900">{{ __('app.examine.patient_info') }}</h2>
                <button type="button" onclick="toggle('edit-profile')" class="btn btn-ghost btn-sm">✏️ {{ __('app.patient.edit') }}</button>
            </div>
            <dl class="text-sm space-y-1.5">
                <div class="flex justify-between gap-3"><dt class="text-slate-400">{{ __('app.common.gender') }}</dt><dd>{{ $genderLabel }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-400">{{ __('app.common.age') }}</dt><dd>{{ $p->age ?? '—' }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-400">{{ __('app.common.phone') }}</dt><dd dir="ltr">{{ $p->phone_number ?? '—' }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-400">{{ __('app.common.blood_type') }}</dt><dd>{{ $p->blood_type ?? '—' }}</dd></div>
                {{-- Red only when there is something to be careful about. --}}
                <div class="flex justify-between gap-3"><dt class="text-slate-400">{{ __('app.common.allergies') }}</dt>
                    <dd class="text-end {{ $hasAllergies ? 'text-red-600 font-medium' : 'text-slate-500' }}">{{ $allergyList ?? __('app.common.none') }}</dd></div>
                <div class="flex justify-between gap-3"><dt class="text-slate-400">{{ __('app.common.chronic_diseases') }}</dt>
                    <dd class="text-end {{ $chronicList ? '' : 'text-slate-500' }}">{{ $chronicList ?? __('app.common.none') }}</dd></div>
                @if ($appointment->insurance && $appointment->insurance->insuranceCompany)
                    <div class="flex justify-between gap-3"><dt class="text-slate-400">{{ __('app.insurance.title') }}</dt>
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

            {{-- Editable patient profile --}}
            <div id="edit-profile" class="hidden mt-4 pt-4 border-t border-slate-100">
                <h3 class="font-semibold text-slate-800 mb-3">{{ __('app.patient.edit_heading') }}</h3>
                <form method="POST" action="{{ route('practice.patients.update', $p) }}" class="space-y-3">
                    @csrf @method('PUT')
                    <div>
                        <label for="patient-name" class="block text-xs text-slate-500 mb-1">{{ __('app.patient.name') }}</label>
                        <input type="text" name="name" id="patient-name" required value="{{ old('name', $p->name) }}"
                               class="w-full border rounded px-2 py-1.5 text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="patient-gender" class="block text-xs text-slate-500 mb-1">{{ __('app.common.gender') }}</label>
                            <select name="gender" id="patient-gender" class="w-full border rounded px-2 py-1.5 text-sm">
                                <option value="male" @selected($p->gender === 'male')>{{ __('app.genders.male') }}</option>
                                <option value="female" @selected($p->gender === 'female')>{{ __('app.genders.female') }}</option>
                            </select>
                        </div>
                        <div>
                            <label for="patient-dob" class="block text-xs text-slate-500 mb-1">{{ __('app.patient.dob') }}</label>
                            <input type="date" name="dob" id="patient-dob" value="{{ old('dob', $p->dob?->format('Y-m-d')) }}"
                                   class="w-full border rounded px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label for="patient-phone" class="block text-xs text-slate-500 mb-1">{{ __('app.common.phone') }}</label>
                            <input type="text" name="phone_number" id="patient-phone" inputmode="tel" value="{{ old('phone_number', $p->phone_number) }}"
                                   class="w-full border rounded px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label for="patient-email" class="block text-xs text-slate-500 mb-1">{{ __('app.common.email') }}</label>
                            <input type="email" name="email" id="patient-email" value="{{ old('email', $p->email) }}"
                                   class="w-full border rounded px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label for="patient-national-id" class="block text-xs text-slate-500 mb-1">{{ __('app.common.national_id') }}</label>
                            <input type="text" name="national_id" id="patient-national-id" value="{{ old('national_id', $p->national_id) }}"
                                   class="w-full border rounded px-2 py-1.5 text-sm">
                        </div>
                        <div>
                            <label for="patient-blood" class="block text-xs text-slate-500 mb-1">{{ __('app.common.blood_type') }}</label>
                            <input type="text" name="blood_type" id="patient-blood" value="{{ old('blood_type', $p->blood_type) }}"
                                   class="w-full border rounded px-2 py-1.5 text-sm">
                        </div>
                    </div>
                    <div>
                        <label for="patient-address" class="block text-xs text-slate-500 mb-1">{{ __('app.common.address') }}</label>
                        <input type="text" name="address" id="patient-address" value="{{ old('address', $p->address) }}"
                               class="w-full border rounded px-2 py-1.5 text-sm">
                    </div>
                    <div>
                        <label for="patient-history" class="block text-xs text-slate-500 mb-1">{{ __('app.patient.medical_history') }}</label>
                        <textarea name="medical_history" id="patient-history" rows="2"
                                  class="w-full border rounded px-2 py-1.5 text-sm">{{ old('medical_history', $p->medical_history) }}</textarea>
                    </div>
                    <div class="flex gap-2">
                        <button class="btn btn-primary btn-sm">{{ __('app.patient.save') }}</button>
                        <button type="button" onclick="toggle('edit-profile')" class="btn btn-ghost btn-sm">{{ __('app.patient.cancel') }}</button>
                    </div>
                </form>
            </div>
        </section>

        {{-- Allergies (editable list), folded until needed --}}
        <details id="allergies" class="card group">
            <summary class="cursor-pointer select-none list-none p-4 sm:p-5 flex items-center justify-between gap-3">
                <span class="text-base font-semibold text-slate-900">{{ __('app.examine.allergies_section') }}</span>
                <span class="flex items-center gap-2 text-xs">
                    <span class="px-2 py-0.5 rounded-full {{ $hasAllergies ? 'bg-red-100 text-red-700 font-semibold' : 'bg-slate-100 text-slate-500' }}">
                        {{ $hasAllergies ? count((array) $p->allergies) : __('app.common.none') }}
                    </span>
                    <span class="text-slate-400 transition-transform group-open:rotate-180">▾</span>
                </span>
            </summary>
            <form method="POST" action="{{ route('practice.doctor.allergies.update', $appointment) }}" class="px-4 sm:px-5 pb-4 sm:pb-5">
                @csrf @method('PUT')
                <div id="allergy-list" class="space-y-2 mb-3">
                    @php $allergies = old('allergies', (array) $p->allergies); @endphp
                    @forelse ($allergies as $allergy)
                        <div class="allergy-row flex items-center gap-2">
                            <input name="allergies[]" value="{{ $allergy }}" aria-label="{{ __('app.examine.allergies_section') }}"
                                   placeholder="{{ __('app.examine.allergies_placeholder') }}"
                                   class="flex-1 border rounded px-2 py-1.5 text-sm">
                            <button type="button" onclick="this.closest('.allergy-row').remove()"
                                    class="btn btn-danger btn-sm" aria-label="{{ __('app.common.remove') }}">✕</button>
                        </div>
                    @empty
                        <div class="allergy-row flex items-center gap-2">
                            <input name="allergies[]" value="" aria-label="{{ __('app.examine.allergies_section') }}"
                                   placeholder="{{ __('app.examine.allergies_placeholder') }}"
                                   class="flex-1 border rounded px-2 py-1.5 text-sm">
                        </div>
                    @endforelse
                </div>
                <div class="flex items-center justify-between gap-2">
                    <button type="button" onclick="addAllergyRow()" class="btn btn-ghost btn-sm">{{ __('app.examine.allergies_add') }}</button>
                    <button class="btn btn-primary btn-sm">{{ __('app.examine.allergies_save') }}</button>
                </div>
            </form>
        </details>

        {{-- Chronic diseases (editable list), folded until needed --}}
        <details id="chronic" class="card group">
            <summary class="cursor-pointer select-none list-none p-4 sm:p-5 flex items-center justify-between gap-3">
                <span class="text-base font-semibold text-slate-900">{{ __('app.examine.chronic_section') }}</span>
                <span class="flex items-center gap-2 text-xs">
                    <span class="px-2 py-0.5 rounded-full {{ $chronicList ? 'bg-slate-200 text-slate-700 font-semibold' : 'bg-slate-100 text-slate-500' }}">
                        {{ $chronicList ? count((array) $p->chronic_diseases) : __('app.common.none') }}
                    </span>
                    <span class="text-slate-400 transition-transform group-open:rotate-180">▾</span>
                </span>
            </summary>
            <form method="POST" action="{{ route('practice.doctor.chronic.update', $appointment) }}" class="px-4 sm:px-5 pb-4 sm:pb-5">
                @csrf @method('PUT')
                <div id="chronic-list" class="space-y-2 mb-3">
                    @php $diseases = old('chronic_diseases', (array) $p->chronic_diseases); @endphp
                    @forelse ($diseases as $disease)
                        <div class="chronic-row flex items-center gap-2">
                            <input name="chronic_diseases[]" value="{{ $disease }}" aria-label="{{ __('app.examine.chronic_section') }}"
                                   placeholder="{{ __('app.examine.chronic_placeholder') }}"
                                   class="flex-1 border rounded px-2 py-1.5 text-sm">
                            <button type="button" onclick="this.closest('.chronic-row').remove()"
                                    class="btn btn-danger btn-sm" aria-label="{{ __('app.common.remove') }}">✕</button>
                        </div>
                    @empty
                        <div class="chronic-row flex items-center gap-2">
                            <input name="chronic_diseases[]" value="" aria-label="{{ __('app.examine.chronic_section') }}"
                                   placeholder="{{ __('app.examine.chronic_placeholder') }}"
                                   class="flex-1 border rounded px-2 py-1.5 text-sm">
                        </div>
                    @endforelse
                </div>
                <div class="flex items-center justify-between gap-2">
                    <button type="button" onclick="addChronicRow()" class="btn btn-ghost btn-sm">{{ __('app.examine.chronic_add') }}</button>
                    <button class="btn btn-primary btn-sm">{{ __('app.examine.chronic_save') }}</button>
                </div>
            </form>
        </details>

        {{-- Attachments, folded until needed --}}
        <details id="attachments" class="card group" @if ($p->attachments->isNotEmpty()) open @endif>
            <summary class="cursor-pointer select-none list-none p-4 sm:p-5 flex items-center justify-between gap-3">
                <span class="text-base font-semibold text-slate-900">{{ __('app.examine.attachments') }}</span>
                <span class="flex items-center gap-2 text-xs">
                    <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">{{ $p->attachments->count() }}</span>
                    <span class="text-slate-400 transition-transform group-open:rotate-180">▾</span>
                </span>
            </summary>
            <div class="px-4 sm:px-5 pb-4 sm:pb-5">
                <ul class="space-y-2 text-sm mb-4">
                    @forelse ($p->attachments as $att)
                        <li class="flex items-center justify-between gap-2">
                            <a href="{{ $att->url }}" target="_blank" class="text-indigo-600 hover:underline truncate">
                                📎 {{ $att->title ?? $att->file_name }}
                            </a>
                            <span class="shrink-0 text-xs text-slate-400">{{ $att->file_type }}</span>
                        </li>
                    @empty
                        <li class="text-slate-400 italic">{{ __('app.examine.no_attachments') }}</li>
                    @endforelse
                </ul>
                <form method="POST" action="{{ route('practice.attachments.store', $appointment) }}" enctype="multipart/form-data" class="space-y-2">
                    @csrf
                    <label for="attachment-title" class="sr-only">{{ __('app.examine.title_placeholder') }}</label>
                    <input type="text" name="title" id="attachment-title" placeholder="{{ __('app.examine.title_placeholder') }}" class="w-full border rounded px-2 py-1.5 text-sm">
                    <label class="file-field relative">
                        <input type="file" name="file" required>
                        <span class="btn btn-secondary btn-sm">{{ __('app.examine.choose_file') }}</span>
                        <span class="file-name" data-empty="{{ __('app.examine.no_file_chosen') }}">{{ __('app.examine.no_file_chosen') }}</span>
                    </label>
                    <button class="btn btn-primary btn-sm btn-block">{{ __('app.examine.upload_attachment') }}</button>
                </form>
            </div>
        </details>
    </div>
</div>

@push('styles')
<style>
    /* Anchored sections land below the sticky action bar, not under it. */
    #diagnosis, #investigations, #prescription, #charges, #patient-info, #clinical-chart { scroll-margin-top: 5.5rem; }
    /* Folded cards: hide the native triangle. */
    details.card > summary::-webkit-details-marker { display: none; }
    details.card > summary::marker { content: ''; }
    /* The tab strip scrolls sideways on narrow screens without showing a bar. */
    .tab-strip { scrollbar-width: none; }
    .tab-strip::-webkit-scrollbar { display: none; }
</style>
@endpush

@push('scripts')
<script>
    /**
     * Runs a page initialiser now, and again after each background submit
     * replaces <main>. Self-installing so script load order does not matter —
     * the clinical chart registers from its own partial.
     */
    function onExamineReady(fn) {
        (window.examineReady = window.examineReady || []).push(fn);
        fn();
    }

    // Call the next patient. Completes whoever is in the chair (this visit),
    // starts the next one, then opens that patient's examination.
    onExamineReady(function () {
        var btn = document.getElementById('examine-next-btn');
        if (!btn) return;

        btn.addEventListener('click', function () {
            btn.disabled = true;
            fetch(btn.dataset.url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                cache: 'no-store',
            })
                .then(function (r) { return r.ok ? r.json() : Promise.reject(r); })
                .then(function (d) {
                    if (d.current && d.current.id) {
                        window.location.href = btn.dataset.examineBase + '/' + d.current.id + '/examine';
                        return;
                    }
                    window.toastr.info(@json(__('app.examine.no_next_patient')));
                    btn.disabled = false;
                })
                .catch(function () {
                    window.toastr.error(@json(__('app.examine.call_next_failed')));
                    btn.disabled = false;
                });
        });
    });

    // Investigations tabs. The chosen tab survives background submits (which
    // replace <main>) through sessionStorage, keyed to this visit.
    onExamineReady(function () {
        var card = document.getElementById('investigations');
        if (!card) return;

        var KEY = 'examine.tab.' + @json($appointment->id);
        var tabs = card.querySelectorAll('[data-tab]');

        function show(name) {
            tabs.forEach(function (t) {
                var on = t.dataset.tab === name;
                t.setAttribute('aria-selected', on ? 'true' : 'false');
                t.classList.toggle('border-indigo-600', on);
                t.classList.toggle('text-indigo-700', on);
                t.classList.toggle('border-transparent', !on);
                t.classList.toggle('text-slate-500', !on);
            });
            card.querySelectorAll('[data-tab-panel]').forEach(function (p) {
                p.classList.toggle('hidden', p.dataset.tabPanel !== name);
            });
            try { sessionStorage.setItem(KEY, name); } catch (e) {}
        }

        tabs.forEach(function (t) {
            t.addEventListener('click', function () { show(t.dataset.tab); });
        });

        // A failed submit wins (its form must stay in view), then the last
        // choice, then the first tab.
        var initial = card.dataset.defaultTab || '';
        if (!initial) { try { initial = sessionStorage.getItem(KEY) || ''; } catch (e) {} }
        if (!initial || !card.querySelector('[data-tab="' + initial + '"]')) initial = 'requested';
        show(initial);
    });

    // Add another allergy input row.
    function addAllergyRow() {
        const list = document.getElementById('allergy-list');
        const row = document.createElement('div');
        row.className = 'allergy-row flex items-center gap-2';
        row.innerHTML = `
            <input name="allergies[]" value="" placeholder="{{ __('app.examine.allergies_placeholder') }}"
                   aria-label="{{ __('app.examine.allergies_section') }}"
                   class="flex-1 border rounded px-2 py-1.5 text-sm">
            <button type="button" onclick="this.closest('.allergy-row').remove()" class="btn btn-danger btn-sm" aria-label="{{ __('app.common.remove') }}">✕</button>`;
        list.appendChild(row);
        row.querySelector('input').focus();
    }

    // Add another chronic-disease input row.
    function addChronicRow() {
        const list = document.getElementById('chronic-list');
        const row = document.createElement('div');
        row.className = 'chronic-row flex items-center gap-2';
        row.innerHTML = `
            <input name="chronic_diseases[]" value="" placeholder="{{ __('app.examine.chronic_placeholder') }}"
                   aria-label="{{ __('app.examine.chronic_section') }}"
                   class="flex-1 border rounded px-2 py-1.5 text-sm">
            <button type="button" onclick="this.closest('.chronic-row').remove()" class="btn btn-danger btn-sm" aria-label="{{ __('app.common.remove') }}">✕</button>`;
        list.appendChild(row);
        row.querySelector('input').focus();
    }

    // Send a saved prescription to the clinic's Bluetooth thermal printer.
    function printRxThermal(btn, id) {
        const original = btn.innerHTML;
        btn.disabled = true;
        fetch(`{{ url('practice/prescriptions') }}/${id}/print-thermal`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
        .then(res => res.ok ? res.json() : Promise.reject(res))
        .then(() => {
            btn.innerHTML = '✅';
            setTimeout(() => { btn.innerHTML = original; btn.disabled = false; }, 2500);
        })
        .catch(() => {
            btn.disabled = false;
            window.toastr.error(@json(__('app.print.thermal_failed')));
        });
    }

    // ---- Prescription rows: clone the template, never hand-build the markup ----
    let rxIndex = 1;

    function addRxRow() {
        const tpl = document.getElementById('rx-row-template');
        const html = tpl.innerHTML.split('__INDEX__').join(String(rxIndex));
        const holder = document.createElement('div');
        holder.innerHTML = html;
        const row = holder.querySelector('.rx-row');
        document.getElementById('rx-rows').appendChild(row);
        rxIndex++;
        return row;
    }

    // A row is only removable while it is not the last one: the form needs one.
    /**
     * Registered so it runs now and again after every background submit, when
     * the swap has replaced the nodes it binds to. Binding only to elements
     * inside <main> is what makes re-running safe.
     */
    onExamineReady(function () {
        var rows = document.getElementById('rx-rows');
        if (!rows) return;

        rows.addEventListener('click', function (e) {
            if (!e.target.closest('.rx-remove')) return;
            const all = document.querySelectorAll('#rx-rows .rx-row');
            if (all.length <= 1) {
                e.target.closest('.rx-row').querySelectorAll('input').forEach(function (i) { i.value = ''; });
                return;
            }
            e.target.closest('.rx-row').remove();
        });
    });

    // ---- Medicine type-ahead over the catalogue -------------------------------
    /**
     * Registered so it runs now and again after every background submit, when
     * the swap has replaced the nodes it binds to. Binding only to elements
     * inside <main> is what makes re-running safe.
     */
    onExamineReady(function () {
        const SEARCH_URL = @json(route('practice.doctor.medicines.search'));
        const NO_MATCH = @json(__('app.examine.no_medicine_matches'));
        const rows = document.getElementById('rx-rows');
        if (!rows) return;
        let timer = null;
        let seq = 0;

        function group(input) { return input.closest('.rx-group'); }
        function listOf(input) { return group(input).querySelector('.rx-suggest'); }
        function hintOf(input) { return group(input).querySelector('.rx-form-hint'); }
        function doseOf(input) { return group(input).querySelector('.rx-dose'); }

        function closeList(input) {
            const ul = listOf(input);
            ul.classList.add('hidden');
            ul.innerHTML = '';
        }

        /** Offer the dose choices that fit this medicine's dosage form. */
        function applyMedicine(input, med) {
            input.value = med.name;
            hintOf(input).textContent = [med.form, med.ingredient].filter(Boolean).join(' · ');

            const dose = doseOf(input);
            const list = dose && dose.list;
            if (!list) return;
            list.innerHTML = '';
            (med.dose_options || []).forEach(function (opt) {
                const o = document.createElement('option');
                o.value = opt;
                list.appendChild(o);
            });
        }

        function render(input, items) {
            const ul = listOf(input);
            ul.innerHTML = '';

            if (!items.length) {
                const li = document.createElement('li');
                li.className = 'px-3 py-2 text-slate-400';
                li.textContent = NO_MATCH;
                ul.appendChild(li);
            } else {
                items.forEach(function (med) {
                    const li = document.createElement('li');
                    li.className = 'cursor-pointer px-3 py-2 hover:bg-indigo-50';
                    li.innerHTML = '<span class="font-medium">' + med.name.replace(/[<>&]/g, '') + '</span>'
                        + (med.form ? ' <span class="text-xs text-slate-400">' + String(med.form).replace(/[<>&]/g, '') + '</span>' : '');
                    li.addEventListener('mousedown', function (e) {
                        e.preventDefault();
                        applyMedicine(input, med);
                        closeList(input);
                    });
                    ul.appendChild(li);
                });
            }
            ul.classList.remove('hidden');
        }

        rows.addEventListener('input', function (e) {
            const input = e.target.closest('.rx-medicine');
            if (!input) return;

            const term = input.value.trim();
            hintOf(input).textContent = '';
            if (term.length < 2) { closeList(input); return; }

            clearTimeout(timer);
            const mine = ++seq;
            timer = setTimeout(function () {
                fetch(SEARCH_URL + '?q=' + encodeURIComponent(term), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                })
                    .then(function (r) { return r.json(); })
                    .then(function (json) {
                        // Ignore a slow reply the doctor has already typed past.
                        if (mine !== seq) return;
                        render(input, json.data || []);
                    })
                    .catch(function () { closeList(input); });
            }, 200);
        });

        rows.addEventListener('focusout', function (e) {
            const input = e.target.closest('.rx-medicine');
            if (input) setTimeout(function () { closeList(input); }, 120);
        });
    });

    // Re-open the profile editor if it failed validation (only that form submits "name").
    @if ($errors->any() && old('name'))
        document.getElementById('edit-profile')?.classList.remove('hidden');
    @endif

    // "New item…" swaps the catalog picker for name + price inputs. The select
    // is cleared so the controller sees no billable_item_id and takes the
    // new-item branch.
    /**
     * Registered so it runs now and again after every background submit, when
     * the swap has replaced the nodes it binds to. Binding only to elements
     * inside <main> is what makes re-running safe.
     */
    onExamineReady(function () {
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
    });

    // ---- Requests: autocomplete the name field from the selected type ----
    // Suggestions come from the medical-tests catalogue; typing a value that
    // isn't listed is still allowed (native <datalist> behaviour).
    const TEST_SUGGESTIONS = @json($testSuggestions);
    /**
     * Registered so it runs now and again after every background submit, when
     * the swap has replaced the nodes it binds to. Binding only to elements
     * inside <main> is what makes re-running safe.
     */
    onExamineReady(function () {
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
    });

    // ---- Medical plans: load into the Rx table / save current rows as a plan ----
    // Every field of a line, primary and alternative, travels with the plan.
    const RX_FIELDS = [
        'medicine_name', 'dose', 'frequency', 'duration', 'instructions',
        'substitute_name', 'substitute_dose', 'substitute_frequency',
        'substitute_duration', 'substitute_instructions',
    ];

    @php
        $rxFields = ['medicine_name', 'dose', 'frequency', 'duration', 'instructions',
                     'substitute_name', 'substitute_dose', 'substitute_frequency',
                     'substitute_duration', 'substitute_instructions'];
        $plansPayload = $medicalPlans->mapWithKeys(fn ($p) => [$p->id => [
            'id' => $p->id,
            'items' => $p->items->map(fn ($i) => collect($rxFields)
                ->mapWithKeys(fn ($f) => [$f => $i->{$f}])
                ->all())->values(),
        ]]);
    @endphp
    const MEDICAL_PLANS = @json($plansPayload);

    /** Exact field lookup: names end in "[field]", so a suffix match is enough. */
    function rxField(row, field) {
        return row.querySelector('[name$="[' + field + ']"]');
    }

    function addRxRowWith(v) {
        const row = addRxRow();
        let hasSubstitute = false;

        RX_FIELDS.forEach(function (k) {
            const input = rxField(row, k);
            if (!input) return;
            input.value = v[k] || '';
            if (k === 'substitute_name' && input.value) hasSubstitute = true;
        });

        // Open the alternative panel when the plan actually carries one,
        // otherwise it looks like the plan lost it.
        if (hasSubstitute) {
            const alt = row.querySelector('details.rx-alt');
            if (alt) alt.open = true;
        }
    }

    function loadPlan() {
        const sel = document.getElementById('plan-select');
        const plan = MEDICAL_PLANS[sel.value];
        if (!plan) return;

        // Drop the first row if it's still empty, so a fresh form isn't left blank.
        const rows = document.querySelectorAll('#rx-rows .rx-row');
        const first = rows.length === 1 ? rxField(rows[0], 'medicine_name') : null;
        if (first && !first.value.trim()) {
            rows[0].remove();
        }
        (plan.items || []).forEach(addRxRowWith);
    }

    function saveAsPlan() {
        const title = (document.getElementById('plan-title-input').value || '').trim();
        if (!title) { window.toastr.warning(@json(__('app.plan.title_required'))); return; }

        const container = document.getElementById('save-plan-items');
        container.innerHTML = '';
        let i = 0;
        document.querySelectorAll('#rx-rows .rx-row').forEach(function (row) {
            const name = rxField(row, 'medicine_name');
            if (!name || !name.value.trim()) return;
            RX_FIELDS.forEach(function (k) {
                const input = rxField(row, k);
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'items[' + i + '][' + k + ']';
                hidden.value = input ? input.value : '';
                container.appendChild(hidden);
            });
            i++;
        });

        if (i === 0) { window.toastr.warning(@json(__('app.plan.needs_item'))); return; }
        document.getElementById('save-plan-title').value = title;
        // requestSubmit, not submit: only the former fires the submit event the
        // background handler listens for.
        document.getElementById('save-plan-form').requestSubmit();
    }
</script>
@endpush

@push('scripts')
@include('clinic.partials.examine-ajax')
@endpush
@endsection
