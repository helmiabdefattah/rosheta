@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'حجز موعد' : 'Book Appointment')
@section('page-title', app()->getLocale() === 'ar' ? 'حجز موعد جديد' : 'Book Appointment')
@section('page-description', app()->getLocale() === 'ar' ? 'اختر العيادة والتاريخ والوقت' : 'Select clinic, date and time')

@section('content')
<div class="max-w-2xl mx-auto">
    <form action="{{ route('admin.appointments.store') }}" method="POST">
        @csrf

        <x-admin.ui.form-card :title="app()->getLocale() === 'ar' ? 'تفاصيل الموعد' : 'Appointment Details'">
            <div class="space-y-6">
                <div>
                    <x-admin.ui.label for="doctor_id" required>{{ app()->getLocale() === 'ar' ? 'الطبيب' : 'Doctor' }}</x-admin.ui.label>
                    <select name="doctor_id" id="doctor_id" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">{{ app()->getLocale() === 'ar' ? 'اختر الطبيب' : 'Select Doctor' }}</option>
                        @foreach($doctors as $d)
                            <option value="{{ $d->id }}" data-name="{{ $d->name }}" {{ old('doctor_id') == $d->id ? 'selected' : '' }}>{{ $d->name }} ({{ $d->specialization?->name }})</option>
                        @endforeach
                    </select>
                    @error('doctor_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <x-admin.ui.label for="clinic_id" required>{{ app()->getLocale() === 'ar' ? 'العيادة' : 'Clinic' }}</x-admin.ui.label>
                    <select name="clinic_id" id="clinic_id" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">{{ app()->getLocale() === 'ar' ? 'اختر العيادة' : 'Select Clinic' }}</option>
                        @foreach($clinics as $c)
                            <option value="{{ $c->id }}" data-doctor-id="{{ $c->doctor_id }}" data-exam-price="{{ $c->medical_examination_price }}" data-followup-price="{{ $c->follow_up_price }}" {{ old('clinic_id', request('clinic_id')) == $c->id ? 'selected' : '' }}>{{ $c->name }} ({{ $c->doctor?->name }})</option>
                        @endforeach
                    </select>
                    @error('clinic_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-admin.ui.label for="appointment_date" required>{{ app()->getLocale() === 'ar' ? 'التاريخ' : 'Date' }}</x-admin.ui.label>
                        <input type="date" name="appointment_date" id="appointment_date" value="{{ old('appointment_date') }}" min="{{ date('Y-m-d') }}" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        @error('appointment_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <x-admin.ui.label for="appointment_time" required>{{ app()->getLocale() === 'ar' ? 'الوقت' : 'Time' }}</x-admin.ui.label>
                        <select name="appointment_time" id="appointment_time" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'اختر التاريخ أولاً' : 'Select date first' }}</option>
                        </select>
                        @error('appointment_time')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <x-admin.ui.label for="type" required>{{ app()->getLocale() === 'ar' ? 'نوع الزيارة' : 'Visit type' }}</x-admin.ui.label>
                    <select name="type" id="type" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="medical_examination" {{ old('type') === 'medical_examination' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'كشف' : 'Medical examination' }}</option>
                        <option value="follow_up" {{ old('type') === 'follow_up' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'متابعة' : 'Follow-up' }}</option>
                    </select>
                    <p id="price-display" class="mt-1 text-sm text-slate-600"></p>
                    @error('type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <x-admin.ui.label for="user_id">{{ app()->getLocale() === 'ar' ? 'المريض (حساب مستخدم)' : 'Patient (user account)' }}</x-admin.ui.label>
                    <select name="user_id" id="user_id" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">{{ app()->getLocale() === 'ar' ? 'بدون' : 'None' }}</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                    @error('user_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <x-admin.ui.label for="notes">{{ app()->getLocale() === 'ar' ? 'ملاحظات' : 'Notes' }}</x-admin.ui.label>
                    <textarea name="notes" id="notes" rows="2" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('notes') }}</textarea>
                    @error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </x-admin.ui.form-card>

        <div class="mt-8 flex items-center justify-end gap-3">
            <a href="{{ route('admin.appointments.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50">{{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}</a>
            <x-admin.ui.button type="submit">{{ app()->getLocale() === 'ar' ? 'حجز الموعد' : 'Book Appointment' }}</x-admin.ui.button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const clinicSelect = document.getElementById('clinic_id');
    const doctorSelect = document.getElementById('doctor_id');
    const dateInput = document.getElementById('appointment_date');
    const timeSelect = document.getElementById('appointment_time');
    const typeSelect = document.getElementById('type');
    const priceDisplay = document.getElementById('price-display');
    const slotsUrl = '{{ route("admin.appointments.available-slots") }}';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    function filterClinicsByDoctor() {
        const doctorId = doctorSelect.value;
        Array.from(clinicSelect.options).forEach(function(opt) {
            if (opt.value === '') { opt.style.display = 'block'; return; }
            opt.style.display = opt.dataset.doctorId === doctorId ? 'block' : 'none';
            if (opt.value && opt.dataset.doctorId !== doctorId) opt.selected = false;
        });
        updatePrice();
    }

    function updatePrice() {
        const opt = clinicSelect.selectedOptions[0];
        if (!opt || !opt.value) { priceDisplay.textContent = ''; return; }
        const type = typeSelect.value;
        const price = type === 'medical_examination' ? (opt.dataset.examPrice || 0) : (opt.dataset.followupPrice || 0);
        priceDisplay.textContent = '{{ app()->getLocale() === "ar" ? "السعر: " : "Price: " }}' + parseFloat(price).toFixed(2);
    }

    function loadSlots() {
        const clinicId = clinicSelect.value;
        const date = dateInput.value;
        timeSelect.innerHTML = '<option value="">{{ app()->getLocale() === "ar" ? "جاري التحميل..." : "Loading..." }}</option>';
        if (!clinicId || !date) {
            timeSelect.innerHTML = '<option value="">{{ app()->getLocale() === "ar" ? "اختر العيادة والتاريخ" : "Select clinic and date" }}</option>';
            return;
        }
        fetch(slotsUrl + '?clinic_id=' + clinicId + '&date=' + date, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => {
                timeSelect.innerHTML = '<option value="">{{ app()->getLocale() === "ar" ? "اختر الوقت" : "Select time" }}</option>';
                const slots = (data.slots || []).filter(function(s) { return s.available; });
                slots.forEach(function(s) { timeSelect.add(new Option(s.time, s.time)); });
                if (slots.length === 0) timeSelect.add(new Option('{{ app()->getLocale() === "ar" ? "لا توجد أوقات متاحة" : "No slots available" }}', '', true, true)).disabled = true;
            })
            .catch(() => { timeSelect.innerHTML = '<option value="">{{ app()->getLocale() === "ar" ? "خطأ في التحميل" : "Error loading" }}</option>'; });
    }

    doctorSelect.addEventListener('change', filterClinicsByDoctor);
    clinicSelect.addEventListener('change', function() { updatePrice(); if (dateInput.value) loadSlots(); });
    dateInput.addEventListener('change', loadSlots);
    typeSelect.addEventListener('change', updatePrice);

    filterClinicsByDoctor();
    updatePrice();
    if (dateInput.value && clinicSelect.value) loadSlots();
});
</script>
@endsection
