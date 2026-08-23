@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'إنشاء عيادة متكاملة' : 'Clinic Quick Setup')
@section('page-title', app()->getLocale() === 'ar' ? 'إنشاء عيادة متكاملة' : 'Clinic Quick Setup')
@section('page-description', app()->getLocale() === 'ar'
    ? 'أنشئ الطبيب وعيادته وحساب مساعده وبيانات تجريبية كاملة للعرض في خطوة واحدة'
    : 'Create a doctor, their clinic, an assistant account and a full demo dataset in one step')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
#locationMap { height: 320px; width: 100%; border-radius: 0.75rem; border: 1px solid #e2e8f0; }
</style>
@endpush

@section('content')
@php
    $ar = app()->getLocale() === 'ar';
    $result = session('onboarding_result');
    $days = $ar
        ? ['saturday' => 'السبت', 'sunday' => 'الأحد', 'monday' => 'الإثنين', 'tuesday' => 'الثلاثاء', 'wednesday' => 'الأربعاء', 'thursday' => 'الخميس', 'friday' => 'الجمعة']
        : ['saturday' => 'Saturday', 'sunday' => 'Sunday', 'monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday'];
    $oldClosed = old('closed_days', ['friday']);
@endphp

<div class="max-w-5xl mx-auto">

    {{-- ---------------------------------------------------------------- --}}
    {{-- Result of the previous run: credentials + what was created.      --}}
    {{-- ---------------------------------------------------------------- --}}
    @if($result)
        <div class="mb-8 bg-emerald-50 border border-emerald-200 rounded-2xl overflow-hidden">
            <div class="px-6 py-4 bg-emerald-100/60 border-b border-emerald-200 flex items-center gap-3">
                <svg class="w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="font-bold text-emerald-900">{{ $ar ? 'تم إنشاء العيادة بنجاح' : 'Clinic created successfully' }}</h3>
                    <p class="text-sm text-emerald-700">{{ $result['doctor_name'] }} — {{ $result['clinic_name'] }}</p>
                </div>
            </div>

            <div class="p-6 space-y-6">
                <p class="text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
                    {{ $ar
                        ? 'احفظ كلمات المرور الآن — لن تظهر مرة أخرى بعد مغادرة هذه الصفحة.'
                        : 'Save these passwords now — they are shown only once and will not appear again.' }}
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach(['doctor' => ($ar ? 'حساب الطبيب' : 'Doctor account'), 'assistant' => ($ar ? 'حساب مساعد الطبيب' : 'Assistant account')] as $key => $heading)
                        @php $account = $result['credentials'][$key]; @endphp
                        <div class="bg-white rounded-xl border border-emerald-100 p-4">
                            <h4 class="font-semibold text-slate-800 mb-3">{{ $heading }}</h4>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between gap-3">
                                    <dt class="text-slate-500">{{ $ar ? 'الاسم' : 'Name' }}</dt>
                                    <dd class="font-medium text-slate-800 text-end">{{ $account['name'] }}</dd>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <dt class="text-slate-500">{{ $ar ? 'البريد' : 'Email' }}</dt>
                                    <dd class="font-mono text-slate-800 text-end break-all">{{ $account['email'] }}</dd>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <dt class="text-slate-500">{{ $ar ? 'الهاتف' : 'Phone' }}</dt>
                                    <dd class="font-mono text-slate-800">{{ $account['phone'] }}</dd>
                                </div>
                                <div class="flex justify-between gap-3">
                                    <dt class="text-slate-500">{{ $ar ? 'كلمة المرور' : 'Password' }}</dt>
                                    <dd class="font-mono font-bold text-emerald-700">{{ $account['password'] }}</dd>
                                </div>
                            </dl>
                        </div>
                    @endforeach
                </div>

                {{-- What was seeded --}}
                @php
                    $labels = [
                        'patients' => $ar ? 'مرضى' : 'Patients',
                        'appointments' => $ar ? 'مواعيد' : 'Appointments',
                        'diagnoses' => $ar ? 'تشخيصات' : 'Diagnoses',
                        'prescriptions' => $ar ? 'روشتات' : 'Prescriptions',
                        'medical_requests' => $ar ? 'طلبات طبية' : 'Medical orders',
                        'patient_tests' => $ar ? 'نتائج تحاليل/أشعة' : 'Lab / radiology results',
                        'lab_offers' => $ar ? 'نتائج من المعامل' : 'Marketplace results',
                        'medical_plans' => $ar ? 'خطط علاجية' : 'Treatment roadmaps',
                        'examination_fields' => $ar ? 'حقول فحص مخصصة' : 'Examination fields',
                        'billable_items' => $ar ? 'خدمات إضافية' : 'Billable extras',
                        'collections' => $ar ? 'تحصيلات' : 'Collections',
                        'insurance_splits' => $ar ? 'تغطيات تأمينية' : 'Insurance splits',
                        'extra_items' => $ar ? 'بنود مضافة للزيارات' : 'Visit extras',
                    ];
                @endphp
                <div>
                    <h4 class="font-semibold text-slate-800 mb-3">{{ $ar ? 'البيانات التجريبية التي تم إنشاؤها' : 'Demo data created' }}</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-2">
                        @foreach($labels as $key => $label)
                            <div class="bg-white rounded-lg border border-emerald-100 px-3 py-2">
                                <div class="text-lg font-bold text-slate-800">{{ $result['stats'][$key] ?? 0 }}</div>
                                <div class="text-xs text-slate-500">{{ $label }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 pt-2">
                    <a href="{{ route('admin.doctors.show', $result['doctor_id']) }}"
                       class="px-4 py-2 text-sm font-medium bg-white border border-slate-300 rounded-xl hover:bg-slate-50">
                        {{ $ar ? 'ملف الطبيب' : 'Doctor profile' }}
                    </a>
                    <a href="{{ route('admin.clinics.show', $result['clinic_id']) }}"
                       class="px-4 py-2 text-sm font-medium bg-white border border-slate-300 rounded-xl hover:bg-slate-50">
                        {{ $ar ? 'ملف العيادة' : 'Clinic profile' }}
                    </a>
                    <a href="{{ route('practice.display.screen', $result['clinic_id']) }}" target="_blank"
                       class="px-4 py-2 text-sm font-medium bg-white border border-slate-300 rounded-xl hover:bg-slate-50">
                        {{ $ar ? 'شاشة الانتظار' : 'Waiting-room display' }}
                    </a>
                    <a href="{{ route('practice.kiosk.welcome', $result['clinic_id']) }}" target="_blank"
                       class="px-4 py-2 text-sm font-medium bg-white border border-slate-300 rounded-xl hover:bg-slate-50">
                        {{ $ar ? 'شاشة الحجز الذاتي' : 'Self-service kiosk' }}
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- ---------------------------------------------------------------- --}}
    {{-- The form                                                          --}}
    {{-- ---------------------------------------------------------------- --}}
    <form action="{{ route('admin.clinic-onboarding.store') }}" method="POST" class="space-y-6">
        @csrf

        <x-admin.ui.form-card
            :title="$ar ? 'بيانات الطبيب' : 'Doctor'"
            :description="$ar ? 'الاسم والتخصص — سيتم إنشاء حساب دخول للطبيب تلقائياً' : 'Name and specialization — a login is created for the doctor automatically'">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-admin.ui.label for="doctor_name" required>{{ $ar ? 'اسم الطبيب' : 'Doctor name' }}</x-admin.ui.label>
                    <x-admin.ui.input name="doctor_name" :value="old('doctor_name')" required
                                      :placeholder="$ar ? 'د. سمير عبد الله' : 'Dr. Samir Abdallah'" />
                </div>
                <div>
                    <x-admin.ui.label for="specialization_id" required>{{ $ar ? 'التخصص' : 'Specialization' }}</x-admin.ui.label>
                    <x-admin.ui.select name="specialization_id" required>
                        <option value="">{{ $ar ? 'اختر التخصص' : 'Select specialization' }}</option>
                        @foreach($specializations as $s)
                            <option value="{{ $s->id }}" {{ old('specialization_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </x-admin.ui.select>
                </div>
                <div class="md:col-span-2">
                    <x-admin.ui.label for="brief">{{ $ar ? 'نبذة عن الطبيب' : 'Doctor brief' }}</x-admin.ui.label>
                    <textarea name="brief" id="brief" rows="2"
                              class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/10 focus:outline-none">{{ old('brief') }}</textarea>
                    @error('brief')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>
        </x-admin.ui.form-card>

        <x-admin.ui.form-card
            :title="$ar ? 'بيانات العيادة' : 'Clinic'"
            :description="$ar ? 'إذا تركت اسم العيادة فارغاً سيتم استخدام اسم الطبيب' : 'Leave the clinic name empty and the doctor\'s name is used instead'">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-admin.ui.label for="clinic_name">{{ $ar ? 'اسم العيادة' : 'Clinic name' }}</x-admin.ui.label>
                    <x-admin.ui.input name="clinic_name" :value="old('clinic_name')"
                                      :placeholder="$ar ? 'اتركه فارغاً لاستخدام اسم الطبيب' : 'Leave empty to use the doctor name'" />
                </div>
                <div>
                    <x-admin.ui.label for="phone_number">{{ $ar ? 'هاتف العيادة' : 'Clinic phone' }}</x-admin.ui.label>
                    <x-admin.ui.input name="phone_number" :value="old('phone_number')" placeholder="0233334444" />
                </div>
                <div class="md:col-span-2">
                    <x-admin.ui.label for="address" required>{{ $ar ? 'عنوان العيادة' : 'Clinic address' }}</x-admin.ui.label>
                    <textarea name="address" id="address" rows="2" required
                              class="w-full px-4 py-2.5 bg-white border border-slate-200 rounded-xl focus:border-primary-500 focus:ring-2 focus:ring-primary-500/10 focus:outline-none"
                              placeholder="{{ $ar ? '15 شارع التحرير، الدقي، الجيزة' : '15 Tahrir St., Dokki, Giza' }}">{{ old('address') }}</textarea>
                    @error('address')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <x-admin.ui.label for="governorate_id">{{ $ar ? 'المحافظة' : 'Governorate' }}</x-admin.ui.label>
                    <x-admin.ui.select name="governorate_id">
                        <option value="">{{ $ar ? 'اختر المحافظة' : 'Select' }}</option>
                        @foreach($governorates as $g)
                            <option value="{{ $g->id }}" {{ old('governorate_id') == $g->id ? 'selected' : '' }}>{{ $ar ? ($g->name_ar ?? $g->name) : $g->name }}</option>
                        @endforeach
                    </x-admin.ui.select>
                </div>
                <div>
                    <x-admin.ui.label for="city_id">{{ $ar ? 'المدينة' : 'City' }}</x-admin.ui.label>
                    <x-admin.ui.select name="city_id">
                        <option value="">{{ $ar ? 'اختر المدينة' : 'Select' }}</option>
                    </x-admin.ui.select>
                </div>

                <div class="md:col-span-2">
                    <x-admin.ui.label>
                        {{ $ar ? 'موقع العيادة على الخريطة' : 'Clinic location on map' }}
                        <span class="font-normal text-slate-400">({{ $ar ? 'اختياري' : 'optional' }})</span>
                    </x-admin.ui.label>
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-2">
                        <p class="text-sm text-slate-500">{{ $ar
                            ? 'انقر على الخريطة أو اسحب العلامة لتحديد الموقع — يمكنك تركه فارغاً.'
                            : 'Click the map or drag the marker to set the location — you can leave it empty.' }}</p>
                        <div class="flex items-center gap-2">
                            <button type="button" id="locate-me"
                                    class="px-3 py-1.5 text-xs font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50">
                                {{ $ar ? 'موقعي الحالي' : 'Use my location' }}
                            </button>
                            <button type="button" id="clear-location"
                                    class="px-3 py-1.5 text-xs font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hidden">
                                {{ $ar ? 'مسح الموقع' : 'Clear location' }}
                            </button>
                        </div>
                    </div>
                    <div id="locationMap"></div>
                    <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                    <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                    <p id="location-readout" class="mt-1.5 text-xs text-slate-500">{{ $ar ? 'لم يتم تحديد موقع' : 'No location selected' }}</p>
                    @error('latitude')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                    @error('longitude')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <x-admin.ui.label for="medical_examination_price" required>{{ $ar ? 'سعر الكشف' : 'Examination price' }}</x-admin.ui.label>
                    <x-admin.ui.input type="number" name="medical_examination_price" :value="old('medical_examination_price', 200)" step="0.01" min="0" required />
                </div>
                <div>
                    <x-admin.ui.label for="follow_up_price" required>{{ $ar ? 'سعر المتابعة' : 'Follow-up price' }}</x-admin.ui.label>
                    <x-admin.ui.input type="number" name="follow_up_price" :value="old('follow_up_price', 100)" step="0.01" min="0" required />
                </div>

                <div>
                    <x-admin.ui.label for="appointment_duration" required>{{ $ar ? 'مدة الكشف (دقيقة)' : 'Slot duration (minutes)' }}</x-admin.ui.label>
                    <x-admin.ui.input type="number" name="appointment_duration" :value="old('appointment_duration', 30)" min="5" max="180" required />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-admin.ui.label for="open_from" required>{{ $ar ? 'من' : 'Opens at' }}</x-admin.ui.label>
                        <x-admin.ui.input type="time" name="open_from" :value="old('open_from', '09:00')" required />
                    </div>
                    <div>
                        <x-admin.ui.label for="open_to" required>{{ $ar ? 'إلى' : 'Closes at' }}</x-admin.ui.label>
                        <x-admin.ui.input type="time" name="open_to" :value="old('open_to', '17:00')" required />
                    </div>
                </div>

                <div class="md:col-span-2">
                    <x-admin.ui.label>{{ $ar ? 'أيام الإجازة' : 'Closed days' }}</x-admin.ui.label>
                    <div class="flex flex-wrap gap-2 mt-1">
                        @foreach($days as $key => $label)
                            <label class="inline-flex items-center gap-2 px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-100">
                                <input type="checkbox" name="closed_days[]" value="{{ $key }}"
                                       class="rounded border-slate-300 text-primary focus:ring-primary"
                                       {{ in_array($key, (array) $oldClosed, true) ? 'checked' : '' }}>
                                <span class="text-sm text-slate-700">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('closed_days')<p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
            </div>
        </x-admin.ui.form-card>

        <x-admin.ui.form-card
            :title="$ar ? 'حساب مساعد الطبيب' : 'Doctor\'s assistant'"
            :description="$ar ? 'يدير الاستقبال وطابور الانتظار والتحصيل' : 'Runs the front desk, the queue and payments'">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-admin.ui.label for="assistant_name">{{ $ar ? 'اسم المساعد' : 'Assistant name' }}</x-admin.ui.label>
                    <x-admin.ui.input name="assistant_name" :value="old('assistant_name')"
                                      :placeholder="$ar ? 'اتركه فارغاً للتسمية التلقائية' : 'Leave empty to auto-name'" />
                </div>
                <div></div>
                <div>
                    <x-admin.ui.label for="assistant_email">{{ $ar ? 'البريد الإلكتروني' : 'Email' }}</x-admin.ui.label>
                    <x-admin.ui.input type="email" name="assistant_email" :value="old('assistant_email')" autocomplete="off"
                                      :placeholder="$ar ? 'يتم توليده تلقائياً' : 'Generated automatically'" />
                </div>
                <div>
                    <x-admin.ui.label for="assistant_phone">{{ $ar ? 'رقم الهاتف' : 'Phone' }}</x-admin.ui.label>
                    <x-admin.ui.input name="assistant_phone" :value="old('assistant_phone')" autocomplete="off"
                                      :placeholder="$ar ? 'يتم توليده تلقائياً' : 'Generated automatically'" />
                </div>
                <div>
                    <x-admin.ui.label for="assistant_password">{{ $ar ? 'كلمة المرور' : 'Password' }}</x-admin.ui.label>
                    <x-admin.ui.input type="password" name="assistant_password" autocomplete="new-password"
                                      :placeholder="$ar ? 'الافتراضي: password' : 'Defaults to: password'" />
                </div>
            </div>
        </x-admin.ui.form-card>

        <x-admin.ui.form-card
            :title="$ar ? 'حساب دخول الطبيب' : 'Doctor login'"
            :description="$ar ? 'اتركها فارغة ليتم توليدها تلقائياً' : 'Leave blank to have them generated'">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <x-admin.ui.label for="doctor_email">{{ $ar ? 'البريد الإلكتروني' : 'Email' }}</x-admin.ui.label>
                    <x-admin.ui.input type="email" name="doctor_email" :value="old('doctor_email')" autocomplete="off" />
                </div>
                <div>
                    <x-admin.ui.label for="doctor_phone">{{ $ar ? 'رقم الهاتف' : 'Phone' }}</x-admin.ui.label>
                    <x-admin.ui.input name="doctor_phone" :value="old('doctor_phone')" autocomplete="off" />
                </div>
                <div>
                    <x-admin.ui.label for="doctor_password">{{ $ar ? 'كلمة المرور' : 'Password' }}</x-admin.ui.label>
                    <x-admin.ui.input type="password" name="doctor_password" autocomplete="new-password"
                                      :placeholder="$ar ? 'الافتراضي: password' : 'Defaults to: password'" />
                </div>
            </div>
        </x-admin.ui.form-card>

        <x-admin.ui.form-card
            :title="$ar ? 'البيانات التجريبية للعرض' : 'Demo data for presenting'"
            :description="$ar
                ? 'مرضى بملفات طبية كاملة، مواعيد لليوم والأيام التالية، تشخيصات وخطط علاج، روشتات، تحاليل وأشعة، تحصيلات وتأمين'
                : 'Patients with full medical files, appointments for today and the next days, diagnoses and treatment roadmaps, prescriptions, lab and radiology results, collections and insurance'">
            <div class="space-y-6">
                <label class="flex items-start gap-3 p-4 bg-teal-50 border border-teal-200 rounded-xl cursor-pointer">
                    <input type="checkbox" name="seed_demo" value="1" id="seed_demo"
                           class="mt-0.5 rounded border-slate-300 text-primary focus:ring-primary"
                           {{ old('seed_demo', true) ? 'checked' : '' }}>
                    <span>
                        <span class="block font-semibold text-slate-800">{{ $ar ? 'إنشاء بيانات تجريبية كاملة' : 'Create the full demo dataset' }}</span>
                        <span class="block text-sm text-slate-600">{{ $ar
                            ? 'يشمل كذلك الخطط العلاجية وحقول الفحص المخصصة والخدمات الإضافية القابلة للتحصيل.'
                            : 'Also includes treatment roadmaps, custom examination fields and billable extras.' }}</span>
                    </span>
                </label>

                <div id="demo-options" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <x-admin.ui.label for="patients_count">{{ $ar ? 'عدد المرضى' : 'Patients' }}</x-admin.ui.label>
                        <x-admin.ui.input type="number" name="patients_count" :value="old('patients_count', 8)" min="1" max="12" />
                    </div>
                    <div>
                        <x-admin.ui.label for="appointments_per_day">{{ $ar ? 'مواعيد في اليوم' : 'Appointments per day' }}</x-admin.ui.label>
                        <x-admin.ui.input type="number" name="appointments_per_day" :value="old('appointments_per_day', 6)" min="1" max="20" />
                    </div>
                    <div>
                        <x-admin.ui.label for="days_ahead">{{ $ar ? 'عدد الأيام القادمة' : 'Days ahead' }}</x-admin.ui.label>
                        <x-admin.ui.input type="number" name="days_ahead" :value="old('days_ahead', 3)" min="0" max="14" />
                        <p class="mt-1 text-xs text-slate-500">{{ $ar ? 'اليوم + هذا العدد من الأيام' : 'Today plus this many days' }}</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="flex items-start gap-3">
                            <input type="checkbox" name="seed_history" value="1" id="seed_history"
                                   class="mt-0.5 rounded border-slate-300 text-primary focus:ring-primary"
                                   {{ old('seed_history', true) ? 'checked' : '' }}>
                            <span>
                                <span class="block font-medium text-slate-800">{{ $ar ? 'إضافة زيارات سابقة' : 'Add past visits' }}</span>
                                <span class="block text-sm text-slate-500">{{ $ar
                                    ? 'يعطي كل مريض تاريخاً مرضياً يمكن تصفحه داخل ملفه.'
                                    : 'Gives each patient a browsable history inside their file.' }}</span>
                            </span>
                        </label>
                    </div>
                    <div>
                        <x-admin.ui.label for="history_visits">{{ $ar ? 'عدد الزيارات السابقة' : 'Past visits' }}</x-admin.ui.label>
                        <x-admin.ui.input type="number" name="history_visits" :value="old('history_visits', 6)" min="0" max="20" />
                    </div>
                </div>
            </div>
        </x-admin.ui.form-card>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.clinics.index') }}"
               class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50">
                {{ $ar ? 'إلغاء' : 'Cancel' }}
            </a>
            <x-admin.ui.button type="submit">{{ $ar ? 'إنشاء العيادة والبيانات' : 'Create clinic & data' }}</x-admin.ui.button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
$(function () {
    const isRTL = {{ $ar ? 'true' : 'false' }};
    const oldCity = {{ old('city_id', 'null') }};

    // Cities follow the selected governorate.
    $('#governorate_id').on('change', function () {
        const $city = $('#city_id');
        $city.empty().append($('<option></option>').val('').text('{{ $ar ? "اختر المدينة" : "Select" }}'));
        const gid = $(this).val();
        if (!gid) return;

        $.get('/api/cities', { governorate_id: gid }, function (r) {
            (r.data || []).forEach(function (c) {
                $city.append($('<option></option>')
                    .val(c.id)
                    .text(isRTL ? (c.name_ar || c.name) : (c.name || c.name_ar))
                    .prop('selected', c.id == oldCity));
            });
        });
    });
    if ($('#governorate_id').val()) $('#governorate_id').trigger('change');

    // Demo options only matter when the demo dataset is switched on.
    function toggleDemoOptions() {
        const on = $('#seed_demo').is(':checked');
        $('#demo-options').toggleClass('opacity-40 pointer-events-none', !on);
    }
    $('#seed_demo').on('change', toggleDemoOptions);
    toggleDemoOptions();

    // Optional clinic location: no marker until one is actually picked, so an
    // untouched map submits empty coordinates rather than a default point.
    const noLocationText = '{{ $ar ? "لم يتم تحديد موقع" : "No location selected" }}';
    const $lat = $('#latitude'), $lng = $('#longitude');
    const startLat = parseFloat($lat.val()), startLng = parseFloat($lng.val());
    const hasStart = !isNaN(startLat) && !isNaN(startLng);

    const map = L.map('locationMap').setView(
        hasStart ? [startLat, startLng] : [30.0444, 31.2357],
        hasStart ? 15 : 11
    );
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap', maxZoom: 19 }).addTo(map);

    let marker = null;

    function setLocation(la, ln, recenter) {
        if (marker) {
            marker.setLatLng([la, ln]);
        } else {
            marker = L.marker([la, ln], { draggable: true }).addTo(map);
            marker.on('dragend', function () {
                const p = marker.getLatLng();
                setLocation(p.lat, p.lng, false);
            });
        }
        if (recenter) map.setView([la, ln], Math.max(map.getZoom(), 15));
        $lat.val(la.toFixed(8));
        $lng.val(ln.toFixed(8));
        $('#location-readout').text(la.toFixed(6) + ', ' + ln.toFixed(6));
        $('#clear-location').removeClass('hidden');
    }

    function clearLocation() {
        if (marker) { map.removeLayer(marker); marker = null; }
        $lat.val('');
        $lng.val('');
        $('#location-readout').text(noLocationText);
        $('#clear-location').addClass('hidden');
    }

    if (hasStart) setLocation(startLat, startLng, false);

    map.on('click', function (e) { setLocation(e.latlng.lat, e.latlng.lng, false); });
    $('#clear-location').on('click', clearLocation);

    $('#locate-me').on('click', function () {
        if (!navigator.geolocation) return;
        const $btn = $(this).prop('disabled', true);
        navigator.geolocation.getCurrentPosition(
            function (pos) {
                setLocation(pos.coords.latitude, pos.coords.longitude, true);
                $btn.prop('disabled', false);
            },
            function () { $btn.prop('disabled', false); }
        );
    });
});
</script>
@endpush
