@php
    /**
     * One clinic card. Used twice: by the server-side loop (with $i set, so the
     * inputs carry real name="clinics[i]..." attributes) and inside the <template>
     * for JS-added rows (with $i === null, so only data-key is emitted and the
     * repeater script fills in the name on insert / reindex).
     */
    $i = $i ?? null;
    $isTpl = $i === null;
    $row = $row ?? [];

    // Emit `name="clinics[i]<suffix>"` on real rows, and always a data-key the
    // reindex script keys off.
    $nm = function (string $suffix) use ($i, $isTpl) {
        $dk = 'data-key="' . e($suffix) . '"';
        return $isTpl ? $dk : ('name="clinics[' . $i . ']' . e($suffix) . '" ' . $dk);
    };

    $mode = $row['doctor_mode'] ?? 'existing';
    $rowHours = $row['hours'] ?? [];
@endphp

<details class="clinic-row group rounded-xl border border-slate-200 bg-slate-50 overflow-hidden" open>
    <summary class="flex items-center justify-between gap-3 px-4 py-3 cursor-pointer select-none list-none bg-white hover:bg-slate-50 transition-colors">
        <span class="flex items-center gap-2 min-w-0">
            <svg class="w-4 h-4 text-slate-400 transition-transform group-open:rotate-90 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ app()->getLocale() === 'ar' ? 'M15 19l-7-7 7-7' : 'M9 5l7 7-7 7' }}"/></svg>
            <span class="font-bold text-slate-800 clinic-index truncate">{{ $l ? 'عيادة' : 'Clinic' }}</span>
        </span>
        <button type="button" class="remove-clinic shrink-0 inline-flex items-center gap-1 text-red-500 text-xs font-semibold hover:text-red-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            {{ $l ? 'إزالة' : 'Remove' }}
        </button>
    </summary>

    <div class="p-4 space-y-5 border-t border-slate-100">
        <input type="hidden" {!! $nm('[id]') !!} value="{{ $row['id'] ?? '' }}">

        {{-- Basic --}}
        <div class="grid sm:grid-cols-2 gap-3">
            <div>
                <label class="{{ $lbl }}">{{ $l ? 'اسم العيادة' : 'Clinic name' }} <span class="text-red-500">*</span></label>
                <input type="text" {!! $nm('[name]') !!} value="{{ $row['name'] ?? '' }}" class="{{ $inp }} clinic-name-input">
            </div>
            <div>
                <label class="{{ $lbl }}">{{ $l ? 'هاتف العيادة' : 'Clinic phone' }}</label>
                <input type="text" {!! $nm('[phone_number]') !!} value="{{ $row['phone_number'] ?? '' }}" class="{{ $inp }}" dir="ltr">
            </div>
        </div>

        {{-- Doctor: existing or new --}}
        <div class="rounded-lg border border-slate-200 bg-white p-3">
            <div class="flex flex-wrap items-center gap-4 mb-3">
                <span class="text-sm font-bold text-slate-700">{{ $l ? 'الطبيب' : 'Doctor' }}</span>
                <label class="inline-flex items-center gap-1.5 text-sm text-slate-600">
                    <input type="radio" {!! $nm('[doctor_mode]') !!} value="existing" class="doctor-mode text-primary" @checked($mode !== 'new')>
                    {{ $l ? 'طبيب موجود' : 'Existing doctor' }}
                </label>
                <label class="inline-flex items-center gap-1.5 text-sm text-slate-600">
                    <input type="radio" {!! $nm('[doctor_mode]') !!} value="new" class="doctor-mode text-primary" @checked($mode === 'new')>
                    {{ $l ? 'طبيب جديد' : 'New doctor' }}
                </label>
            </div>

            <div class="doctor-existing {{ $mode === 'new' ? 'hidden' : '' }}">
                <select {!! $nm('[doctor_id]') !!} class="{{ $inp }}">
                    <option value="">{{ $l ? 'بدون طبيب' : 'No doctor' }}</option>
                    @foreach($doctors as $doc)
                        <option value="{{ $doc->id }}" @selected(($row['doctor_id'] ?? '') == $doc->id)>{{ $doc->name }}@if($doc->specialization) — {{ $l ? ($doc->specialization->name_ar ?? $doc->specialization->name) : $doc->specialization->name }}@endif</option>
                    @endforeach
                </select>
            </div>

            <div class="doctor-new {{ $mode === 'new' ? '' : 'hidden' }} space-y-3">
                <div class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <label class="{{ $lbl }}">{{ $l ? 'اسم الطبيب' : 'Doctor name' }} <span class="text-red-500">*</span></label>
                        <input type="text" {!! $nm('[new_doctor][name]') !!} value="{{ $row['new_doctor']['name'] ?? '' }}" class="{{ $inp }}">
                    </div>
                    <div>
                        <label class="{{ $lbl }}">{{ $l ? 'التخصص' : 'Specialization' }} <span class="text-red-500">*</span></label>
                        <select {!! $nm('[new_doctor][specialization_id]') !!} class="{{ $inp }}">
                            <option value="">{{ $l ? 'اختر' : 'Select' }}</option>
                            @foreach($specializations as $sp)
                                <option value="{{ $sp->id }}" @selected(($row['new_doctor']['specialization_id'] ?? '') == $sp->id)>{{ $l ? ($sp->name_ar ?? $sp->name) : $sp->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <details class="rounded-lg bg-slate-50 border border-slate-200 p-3">
                    <summary class="text-xs font-semibold text-slate-600 cursor-pointer">{{ $l ? 'إنشاء حساب دخول للطبيب (اختياري)' : 'Create a login account for the doctor (optional)' }}</summary>
                    <div class="grid sm:grid-cols-3 gap-3 mt-3">
                        <div>
                            <label class="{{ $lbl }}">{{ $l ? 'البريد الإلكتروني' : 'Email' }}</label>
                            <input type="email" {!! $nm('[new_doctor][email]') !!} value="{{ $row['new_doctor']['email'] ?? '' }}" class="{{ $inp }}" dir="ltr">
                        </div>
                        <div>
                            <label class="{{ $lbl }}">{{ $l ? 'الهاتف' : 'Phone' }}</label>
                            <input type="text" {!! $nm('[new_doctor][phone]') !!} value="{{ $row['new_doctor']['phone'] ?? '' }}" class="{{ $inp }}" dir="ltr">
                        </div>
                        <div>
                            <label class="{{ $lbl }}">{{ $l ? 'كلمة المرور' : 'Password' }}</label>
                            <input type="text" {!! $nm('[new_doctor][password]') !!} value="" class="{{ $inp }}" dir="ltr" autocomplete="new-password">
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 mt-2">{{ $l ? 'اترك الحقول فارغة لإنشاء الطبيب بدون حساب دخول.' : 'Leave blank to create the doctor without a login account.' }}</p>
                </details>
            </div>
        </div>

        {{-- Pricing & slots --}}
        <div class="grid sm:grid-cols-4 gap-3">
            <div>
                <label class="{{ $lbl }}">{{ $l ? 'سعر الكشف' : 'Exam price' }}</label>
                <input type="number" step="0.01" min="0" {!! $nm('[medical_examination_price]') !!} value="{{ $row['medical_examination_price'] ?? '' }}" class="{{ $inp }}">
            </div>
            <div>
                <label class="{{ $lbl }}">{{ $l ? 'سعر المتابعة' : 'Follow-up price' }}</label>
                <input type="number" step="0.01" min="0" {!! $nm('[follow_up_price]') !!} value="{{ $row['follow_up_price'] ?? '' }}" class="{{ $inp }}">
            </div>
            <div>
                <label class="{{ $lbl }}">{{ $l ? 'مدة الكشف (دقيقة)' : 'Visit duration (min)' }}</label>
                <input type="number" step="1" min="5" max="240" {!! $nm('[appointment_duration]') !!} value="{{ $row['appointment_duration'] ?? 30 }}" class="{{ $inp }}">
            </div>
            <div>
                <label class="{{ $lbl }}">{{ $l ? 'حجوزات لكل فترة' : 'Slots / interval' }}</label>
                <input type="number" step="1" min="1" max="50" {!! $nm('[slots_per_interval]') !!} value="{{ $row['slots_per_interval'] ?? 1 }}" class="{{ $inp }}">
            </div>
        </div>

        {{-- Weekly opening hours (collapsed by default to keep the card short) --}}
        <details class="hours-block group/hours rounded-lg border border-slate-200 bg-white overflow-hidden">
            <summary class="flex items-center justify-between gap-2 px-3 py-2.5 cursor-pointer select-none list-none bg-slate-50 hover:bg-slate-100 transition-colors">
                <span class="text-sm font-semibold text-slate-700">{{ $l ? 'مواعيد العمل الأسبوعية' : 'Weekly opening hours' }}</span>
                <svg class="w-4 h-4 text-slate-400 transition-transform group-open/hours:rotate-180 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </summary>
            <div class="divide-y divide-slate-100 border-t border-slate-100">
                @foreach($days as $day)
                    @php
                        $h = $rowHours[$day] ?? [];
                        $closed = ! empty($h['closed']);
                        $open = $h['open'] ?? '09:00';
                        $close = $h['close'] ?? '17:00';
                    @endphp
                    <div class="day-row flex flex-wrap items-center gap-3 px-3 py-2">
                        <span class="w-20 text-sm font-semibold text-slate-700 shrink-0">{{ $dayLabels[$day] }}</span>
                        <label class="inline-flex items-center gap-1.5 text-xs text-slate-500">
                            <input type="checkbox" {!! $nm('[hours][' . $day . '][closed]') !!} value="1" class="day-closed w-4 h-4 rounded border-slate-300 text-primary" @checked($closed)>
                            {{ $l ? 'مغلق' : 'Closed' }}
                        </label>
                        <div class="day-times flex items-center gap-2 {{ $closed ? 'opacity-40 pointer-events-none' : '' }}">
                            <input type="time" {!! $nm('[hours][' . $day . '][open]') !!} value="{{ $open }}" class="border border-slate-300 rounded-lg p-1.5 text-sm">
                            <span class="text-slate-400 text-sm">—</span>
                            <input type="time" {!! $nm('[hours][' . $day . '][close]') !!} value="{{ $close }}" class="border border-slate-300 rounded-lg p-1.5 text-sm">
                        </div>
                    </div>
                @endforeach
            </div>
        </details>
    </div>
</details>
