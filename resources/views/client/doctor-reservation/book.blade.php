@extends('client.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'حجز موعد' : 'Book Appointment')
@section('page-title', app()->getLocale() === 'ar' ? 'حجز موعد' : 'Book Appointment')
@section('page-description', app()->getLocale() === 'ar' ? 'اختر التاريخ والوقت' : 'Choose date and time')

@section('content')
<div class="max-w-2xl mx-auto">
    @php
        $displayDoctor = $selectedDoctor ?? $clinic->doctor;
        $examPrice = $displayDoctor ? $clinic->getPriceForDoctor($displayDoctor, 'medical_examination') : (float) $clinic->medical_examination_price;
        $followUpPrice = $displayDoctor ? $clinic->getPriceForDoctor($displayDoctor, 'follow_up') : (float) $clinic->follow_up_price;
    @endphp
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center gap-4">
            @if($displayDoctor && $displayDoctor->getFirstMediaUrl('profile_image'))
                <img src="{{ $displayDoctor->getFirstMediaUrl('profile_image') }}" alt="" class="w-14 h-14 rounded-full object-cover">
            @else
                <div class="w-14 h-14 rounded-full bg-primary/10 flex items-center justify-center">
                    <i class="bi bi-person text-xl text-primary"></i>
                </div>
            @endif
            <div>
                <h3 class="font-bold text-gray-900">{{ $displayDoctor?->name }}</h3>
                <p class="text-sm text-primary">{{ $displayDoctor?->specialization?->name }}</p>
                <p class="text-sm text-gray-600">{{ $clinic->name }}</p>
                @if($clinic->address)
                    <p class="text-sm text-gray-500 mt-1"><i class="bi bi-geo-alt"></i> {{ $clinic->address }}</p>
                @endif
                @if($clinic->phone_number)
                    <p class="text-sm text-gray-500 mt-1"><i class="bi bi-telephone"></i> <a href="tel:{{ $clinic->phone_number }}" class="hover:text-primary">{{ $clinic->phone_number }}</a></p>
                @endif
                <p class="text-sm text-gray-600 mt-1">
                    {{ app()->getLocale() === 'ar' ? 'كشف:' : 'Examination:' }} <strong>{{ number_format($examPrice, 2) }}</strong>
                    @if((float) $followUpPrice === 0.0)
                        &nbsp;|&nbsp;<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 font-medium text-xs">{{ app()->getLocale() === 'ar' ? 'متابعة مجانية' : 'Free follow-up' }}</span>
                    @else
                        &nbsp;|&nbsp;{{ app()->getLocale() === 'ar' ? 'متابعة:' : 'Follow-up:' }} <strong>{{ number_format($followUpPrice, 2) }}</strong>
                    @endif
                </p>
            </div>
        </div>
    </div>

    <form action="{{ route('client.doctor-reservation.store') }}" method="POST" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        @csrf
        <input type="hidden" name="clinic_id" value="{{ $clinic->id }}">
        @if($displayDoctor)
            <input type="hidden" name="doctor_id" value="{{ $displayDoctor->id }}">
        @endif

        <div class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="appointment_date" class="block text-sm font-medium text-gray-700 mb-1">{{ app()->getLocale() === 'ar' ? 'اليوم' : 'Day' }} <span class="text-red-500">*</span></label>
                    @if(count($availableDays) > 0)
                        <select name="appointment_date" id="appointment_date" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'اختر يوم متاح' : 'Select available day' }}</option>
                            @foreach($availableDays as $day)
                                @php
                                    $dayLabel = \Carbon\Carbon::parse($day['date'])->locale(app()->getLocale())->translatedFormat(app()->getLocale() === 'ar' ? 'l، j F' : 'D, M j');
                                @endphp
                                <option value="{{ $day['date'] }}" {{ old('appointment_date') === $day['date'] ? 'selected' : '' }}>{{ $dayLabel }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="date" name="appointment_date" id="appointment_date" value="{{ old('appointment_date') }}" min="{{ date('Y-m-d') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:border-primary">
                        <p class="mt-1 text-sm text-gray-500">{{ app()->getLocale() === 'ar' ? 'لا توجد أيام متاحة في الأسبوعين القادمين؛ اختر تاريخاً يدوياً' : 'No available days in the next 2 weeks; pick a date manually.' }}</p>
                    @endif
                    @error('appointment_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="appointment_time" class="block text-sm font-medium text-gray-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الوقت' : 'Time' }} <span class="text-red-500">*</span></label>
                    <select name="appointment_time" id="appointment_time" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:border-primary">
                        <option value="">{{ app()->getLocale() === 'ar' ? 'اختر التاريخ أولاً' : 'Select date first' }}</option>
                    </select>
                    @error('appointment_time')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">{{ app()->getLocale() === 'ar' ? 'نوع الزيارة' : 'Visit type' }}</label>
                <input type="hidden" name="type" value="medical_examination">
                <div class="w-full border border-gray-300 rounded-lg px-3 py-2.5 bg-gray-50 text-gray-700">
                    {{ app()->getLocale() === 'ar' ? 'كشف' : 'Medical examination' }} ({{ number_format($examPrice, 2) }})
                </div>
                @error('type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">{{ app()->getLocale() === 'ar' ? 'ملاحظات' : 'Notes' }}</label>
                <textarea name="notes" id="notes" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:border-primary" placeholder="{{ app()->getLocale() === 'ar' ? 'اختياري' : 'Optional' }}">{{ old('notes') }}</textarea>
                @error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <a href="{{ route('client.doctor-reservation.index') }}" class="px-5 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">
                {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
            </a>
            <button type="submit" class="px-5 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                {{ app()->getLocale() === 'ar' ? 'تأكيد الحجز' : 'Confirm Booking' }}
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var clinicId = {{ $clinic->id }};
    var doctorId = {{ $displayDoctor ? $displayDoctor->id : 'null' }};
    var dateInput = document.getElementById('appointment_date');
    var timeSelect = document.getElementById('appointment_time');
    var slotsUrl = '{{ route("client.doctor-reservation.available-slots") }}';

    function loadSlots() {
        var date = dateInput.value;
        timeSelect.innerHTML = '<option value="">{{ app()->getLocale() === "ar" ? "جاري التحميل..." : "Loading..." }}</option>';
        if (!date) {
            timeSelect.innerHTML = '<option value="">{{ app()->getLocale() === "ar" ? "اختر التاريخ" : "Select date" }}</option>';
            return;
        }
        var url = slotsUrl + '?clinic_id=' + clinicId + '&date=' + date;
        if (doctorId) url += '&doctor_id=' + doctorId;
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                timeSelect.innerHTML = '<option value="">{{ app()->getLocale() === "ar" ? "اختر الوقت" : "Select time" }}</option>';
                var slots = (data.slots || []).filter(function(s) { return s.available; });
                slots.forEach(function(s) { timeSelect.add(new Option(s.time, s.time)); });
                if (slots.length === 0) timeSelect.add(new Option('{{ app()->getLocale() === "ar" ? "لا توجد أوقات متاحة" : "No slots available" }}', '', true, true)).disabled = true;
            })
            .catch(function() { timeSelect.innerHTML = '<option value="">{{ app()->getLocale() === "ar" ? "خطأ في التحميل" : "Error loading" }}</option>'; });
    }
    dateInput.addEventListener('change', loadSlots);
    if (dateInput.value) loadSlots();
});
</script>
@endsection
