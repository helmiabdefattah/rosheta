@extends('client.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'حجز موعد طبيب' : 'Doctor Reservation')
@section('page-title', app()->getLocale() === 'ar' ? 'حجز موعد طبيب' : 'Doctor Reservation')
@section('page-description', app()->getLocale() === 'ar' ? 'اختر العيادة واحجز موعدك' : 'Choose a clinic and book your appointment')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single { height: 38px; border-radius: 0.375rem; }
    .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 38px; padding-left: 12px; padding-right: 20px; }
    [dir="rtl"] .select2-container--default .select2-selection--single .select2-selection__rendered { padding-right: 12px; padding-left: 20px; }
    .doctor-card { transition: box-shadow 0.2s, transform 0.2s; }
    .doctor-card:hover { box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1); }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    @if(session('success'))
        <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-100">
            <h3 class="text-lg font-semibold text-gray-800">
                {{ app()->getLocale() === 'ar' ? 'فلترة العيادات' : 'Filter Clinics' }}
            </h3>
        </div>
        <form method="GET" action="{{ route('client.doctor-reservation.index') }}" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ app()->getLocale() === 'ar' ? 'المحافظة' : 'Governorate' }}</label>
                    <select name="governorate_id" id="governorate_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:border-primary">
                        <option value="">{{ app()->getLocale() === 'ar' ? 'جميع المحافظات' : 'All' }}</option>
                        @foreach($governorates as $g)
                            <option value="{{ $g->id }}" @selected(request('governorate_id') == $g->id)>{{ app()->getLocale() === 'ar' ? ($g->name_ar ?? $g->name) : ($g->name ?? $g->name_ar) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ app()->getLocale() === 'ar' ? 'المدينة' : 'City' }}</label>
                    <select name="city_id" id="city_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:border-primary">
                        <option value="">{{ app()->getLocale() === 'ar' ? 'جميع المدن' : 'All' }}</option>
                        @foreach($cities as $c)
                            <option value="{{ $c->id }}" @selected(request('city_id') == $c->id)>{{ app()->getLocale() === 'ar' ? ($c->name_ar ?? $c->name) : ($c->name ?? $c->name_ar) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ app()->getLocale() === 'ar' ? 'المنطقة' : 'Area' }}</label>
                    <select name="area_id" id="area_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:border-primary">
                        <option value="">{{ app()->getLocale() === 'ar' ? 'جميع المناطق' : 'All' }}</option>
                        @foreach($areas as $a)
                            <option value="{{ $a->id }}" @selected(request('area_id') == $a->id)>{{ app()->getLocale() === 'ar' ? ($a->name_ar ?? $a->name) : ($a->name ?? $a->name_ar) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ app()->getLocale() === 'ar' ? 'التخصص' : 'Specialization' }}</label>
                    <select name="specialization_id" id="specialization_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-primary focus:border-primary">
                        <option value="">{{ app()->getLocale() === 'ar' ? 'جميع التخصصات' : 'All' }}</option>
                        @foreach($specializations as $s)
                            <option value="{{ $s->id }}" @selected(request('specialization_id') == $s->id)>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex gap-2 mt-4">
                <button type="submit" class="px-5 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                    {{ app()->getLocale() === 'ar' ? 'بحث' : 'Search' }}
                </button>
                <a href="{{ route('client.doctor-reservation.index') }}" class="px-5 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">
                    {{ app()->getLocale() === 'ar' ? 'مسح' : 'Clear' }}
                </a>
            </div>
        </form>
    </div>

    {{-- Results --}}
    <div>
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            {{ app()->getLocale() === 'ar' ? 'العيادات والأطباء' : 'Clinics & Doctors' }}
            @if($clinics->total())
                <span class="text-sm font-normal text-gray-500">({{ $clinics->total() }})</span>
            @endif
        </h3>

        @if($clinics->isEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center text-gray-500">
                {{ app()->getLocale() === 'ar' ? 'لا توجد عيادات مطابقة للبحث. جرّب تغيير الفلاتر.' : 'No clinics match your search. Try changing the filters.' }}
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($clinics as $clinic)
                    @php
                        $clinicDoctors = $clinic->doctors->isNotEmpty() ? $clinic->doctors : ($clinic->doctor ? collect([$clinic->doctor]) : collect());
                    @endphp
                    @foreach($clinicDoctors as $doctor)
                        @php
                            $examPrice = $clinic->getPriceForDoctor($doctor, 'medical_examination');
                            $followUpPrice = $clinic->getPriceForDoctor($doctor, 'follow_up');
                            $openingSummary = $clinic->getOpeningHoursSummaryForDoctor($doctor);
                            $firstSlot = $clinic->getFirstAvailableSlotForDoctor($doctor);
                            // Bookable only when the doctor has an active account. Directory
                            // listings (no login) hide booking + their default hours/availability.
                            $isBookable = (bool) ($doctor->user && $doctor->user->is_active);
                        @endphp
                        <div class="doctor-card bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col">
                            <div class="p-5 flex-1">
                                <div class="flex items-start gap-4">
                                    @if($doctor->getFirstMediaUrl('profile_image'))
                                        <img src="{{ $doctor->getFirstMediaUrl('profile_image') }}" alt="{{ $doctor->name }}" class="w-16 h-16 rounded-full object-cover border-2 border-gray-100 flex-shrink-0">
                                    @else
                                        <div class="w-16 h-16 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0">
                                            <i class="bi bi-person text-2xl text-primary"></i>
                                        </div>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <h4 class="font-bold text-gray-900">{{ $doctor->name }}</h4>
                                        <p class="text-sm text-primary font-medium">{{ $doctor->specialization?->name ?? '-' }}</p>
                                        @if($clinicDoctors->count() > 1)
                                            <p class="text-xs text-gray-500 mt-0.5">{{ $clinic->name }}</p>
                                        @endif
                                        @if($doctor->slug)
                                            <p class="text-xs text-gray-600 mt-0.5">{{ $doctor->slug }}</p>
                                        @endif
                                        @if($doctor->brief)
                                            <p class="text-sm text-gray-600 mt-1 line-clamp-2">{{ $doctor->brief }}</p>
                                        @endif
                                    </div>
                                </div>
                                @if($clinic->address)
                                    <div class="mt-3 flex items-start gap-2 text-sm text-gray-600">
                                        <i class="bi bi-geo-alt text-gray-400 mt-0.5 flex-shrink-0"></i>
                                        <span>{{ $clinic->address }}</span>
                                    </div>
                                @endif
                                {{-- City shown after the address --}}
                                @if($clinic->city)
                                    <div class="mt-2 flex items-center gap-2 text-sm text-gray-600">
                                        <i class="bi bi-building text-gray-400 flex-shrink-0"></i>
                                        <span>{{ app()->getLocale() === 'ar' ? ($clinic->city->name_ar ?? $clinic->city->name) : ($clinic->city->name ?? $clinic->city->name_ar) }}@if($clinic->governorate)، {{ app()->getLocale() === 'ar' ? ($clinic->governorate->name_ar ?? $clinic->governorate->name) : ($clinic->governorate->name ?? $clinic->governorate->name_ar) }}@endif</span>
                                    </div>
                                @endif
                                @if($clinic->phone_number)
                                    <div class="mt-2 flex items-center gap-2 text-sm text-gray-600">
                                        <i class="bi bi-telephone text-gray-400 flex-shrink-0"></i>
                                        <a href="tel:{{ $clinic->phone_number }}" class="hover:text-primary">{{ $clinic->phone_number }}</a>
                                    </div>
                                @endif
                                {{-- Opening hours / first available: only for bookable (active) doctors --}}
                                @if($isBookable && $openingSummary)
                                    <div class="mt-2 flex items-center gap-2 text-sm text-gray-600">
                                        <i class="bi bi-clock text-gray-400 flex-shrink-0"></i>
                                        <span>{{ app()->getLocale() === 'ar' ? 'مواعيد العمل:' : 'Opening:' }} <strong>{{ $openingSummary }}</strong></span>
                                    </div>
                                @endif
                                @if($isBookable && $firstSlot)
                                    <div class="mt-2 flex items-center gap-2 text-sm text-emerald-700">
                                        <i class="bi bi-calendar-check flex-shrink-0"></i>
                                        <span>{{ app()->getLocale() === 'ar' ? 'أول موعد متاح:' : 'First available:' }} <strong>{{ \Carbon\Carbon::parse($firstSlot['date'].' '.$firstSlot['time'])->locale(app()->getLocale())->translatedFormat(app()->getLocale() === 'ar' ? 'd M، g:i a' : 'M j, g:i A') }}</strong></span>
                                    </div>
                                @endif
                                {{-- Prices only when an examination price is set (> 0) --}}
                                @if((float) $examPrice > 0)
                                    <div class="mt-3 flex flex-wrap gap-3 text-sm">
                                        <span class="inline-flex items-center gap-1 text-gray-700">
                                            <i class="bi bi-cash text-gray-500"></i>
                                            {{ app()->getLocale() === 'ar' ? 'كشف:' : 'Examination:' }} <strong>{{ number_format($examPrice, 2) }}</strong>
                                        </span>
                                        @if((float) $followUpPrice === 0.0)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 font-medium">
                                                <i class="bi bi-gift text-emerald-600"></i>
                                                {{ app()->getLocale() === 'ar' ? 'متابعة مجانية' : 'Free follow-up' }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-gray-700">
                                                <i class="bi bi-arrow-repeat text-gray-500"></i>
                                                {{ app()->getLocale() === 'ar' ? 'متابعة:' : 'Follow-up:' }} <strong>{{ number_format($followUpPrice, 2) }}</strong>
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            {{-- Book only when the doctor has an active account --}}
                            @if($isBookable)
                                <div class="p-4 bg-gray-50 border-t border-gray-100">
                                    <a href="{{ route('client.doctor-reservation.book', $clinic) }}?doctor_id={{ $doctor->id }}" class="block w-full text-center px-4 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                                        {{ app()->getLocale() === 'ar' ? 'حجز موعد' : 'Reserve' }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endforeach
                @endforeach
            </div>
            @if($clinics->hasPages())
                <div class="mt-6">
                    {{ $clinics->links() }}
                </div>
            @endif
        @endif
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(function() {
    var isRTL = {{ app()->getLocale() === 'ar' ? 'true' : 'false' }};
    $('#governorate_id').on('change', function() {
        var gid = $(this).val();
        var $city = $('#city_id');
        var $area = $('#area_id');
        $city.empty().append('<option value="">' + (isRTL ? 'جميع المدن' : 'All') + '</option>');
        $area.empty().append('<option value="">' + (isRTL ? 'جميع المناطق' : 'All') + '</option>');
        if (gid) {
            $.get('/api/cities', { governorate_id: gid }, function(r) {
                if (r.data) r.data.forEach(function(c) {
                    $city.append($('<option></option>').attr('value', c.id).text(isRTL ? (c.name_ar || c.name) : (c.name || c.name_ar)));
                });
            });
        }
    });
    $('#city_id').on('change', function() {
        var cid = $(this).val();
        var $area = $('#area_id');
        $area.empty().append('<option value="">' + (isRTL ? 'جميع المناطق' : 'All') + '</option>');
        if (cid) {
            $.get('/api/areas', { city_id: cid }, function(r) {
                if (r.success && r.data) r.data.forEach(function(a) {
                    $area.append($('<option></option>').attr('value', a.id).text(isRTL ? (a.name_ar || a.name) : (a.name || a.name_ar)));
                });
            });
        }
    });
});
</script>
@endpush
@endsection
