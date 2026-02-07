@extends('doctor.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'أيام العمل والإجازة' : 'Working & Off Days')
@section('page-title', app()->getLocale() === 'ar' ? 'أيام العمل والإجازة' : 'Working & Off Days')
@section('page-description', app()->getLocale() === 'ar' ? 'اضغط على يوم لتعيينه إجازة أو يوم عمل (الشهر الحالي والقادم)' : 'Click a day to set it as off or working (this month and next)')

@section('content')
<div class="mb-6 flex items-center justify-between flex-wrap gap-4">
    <div class="flex items-center gap-2">
        @if($canGoPrev)
            <a href="{{ route('doctor.calendar.index', ['month' => $prevMonth->format('Y-m')]) }}" class="px-4 py-2 bg-white border border-slate-300 rounded-lg hover:bg-gray-50 text-sm font-medium">{{ app()->getLocale() === 'ar' ? '← الشهر السابق' : '← Previous' }}</a>
        @endif
        <span class="text-lg font-bold text-slate-800">{{ $start->translatedFormat(app()->getLocale() === 'ar' ? 'F Y' : 'F Y') }}</span>
        <a href="{{ route('doctor.calendar.index', ['month' => $nextMonth->format('Y-m')]) }}" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 text-sm font-medium">{{ app()->getLocale() === 'ar' ? 'الشهر القادم →' : 'Next →' }}</a>
    </div>
    <p class="text-sm text-slate-500">
        {{ app()->getLocale() === 'ar' ? 'كل يوم يعرض اسم العيادة ومواعيد العمل. أخضر = العيادة مفتوحة | رمادي = العيادة مغلقة هذا اليوم | أحمر = إجازة (اضغط للتبديل).' : 'Each day shows clinic name and hours. Green = clinic open | Gray = clinic closed this day | Red = off (click to toggle).' }}
    </p>
</div>
<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="grid grid-cols-7 gap-2 text-center mb-2">
        @php $weekdays = app()->getLocale() === 'ar' ? ['السبت','الأحد','الإثنين','الثلاثاء','الأربعاء','الخميس','الجمعة'] : ['Sat','Sun','Mon','Tue','Wed','Thu','Fri']; @endphp
        @foreach($weekdays as $w)<div class="text-xs font-semibold text-slate-500 py-1">{{ $w }}</div>@endforeach
    </div>
    @php
        $pad = $start->dayOfWeek === 6 ? 0 : $start->dayOfWeek + 1;
    @endphp
    <div class="grid grid-cols-7 gap-2">
        @for($i = 0; $i < $pad; $i++)<div class="min-h-[100px]"></div>@endfor
        @foreach($days as $d)
            <div
                class="min-h-[100px] rounded-lg flex flex-col p-2 border-2 transition-colors overflow-hidden
                    @if($d['is_past']) bg-gray-100 border-gray-200 cursor-default
                    @elseif($d['is_off']) bg-red-50 border-red-300 hover:bg-red-100 calendar-day cursor-pointer
                    @elseif($d['has_clinic_open']) bg-teal-50/80 border-teal-200 hover:bg-teal-100 calendar-day cursor-pointer
                    @else bg-slate-100 border-slate-200 calendar-day cursor-pointer
                    @endif
                    @if($d['is_today']) ring-2 ring-teal-500 ring-offset-1 @endif
                "
                data-date="{{ $d['date'] }}"
                data-past="{{ $d['is_past'] ? '1' : '0' }}"
                data-has-clinic-open="{{ $d['has_clinic_open'] ? '1' : '0' }}"
            >
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm font-bold text-slate-800">{{ $d['day'] }}</span>
                    @if($d['is_off'] && !$d['is_past'])
                        <span class="text-[10px] font-semibold uppercase px-1.5 py-0.5 rounded bg-red-200 text-red-800">{{ app()->getLocale() === 'ar' ? 'إجازة' : 'Off' }}</span>
                    @endif
                </div>
                <div class="flex-1 overflow-y-auto space-y-1.5 text-left">
                    @forelse($d['schedules'] as $sch)
                        <div class="text-[10px] leading-tight">
                            <div class="font-semibold text-slate-700 truncate" title="{{ $sch['clinic_name'] }}">{{ $sch['clinic_name'] }}</div>
                            @if($sch['is_closed'] || !$sch['from_to'])
                                <div class="text-slate-500 italic">{{ app()->getLocale() === 'ar' ? 'مغلق' : 'Closed' }}</div>
                            @else
                                <div class="text-teal-700">{{ $sch['from_to'] }}</div>
                            @endif
                        </div>
                    @empty
                        @if(!$d['is_past'])
                            <div class="text-[10px] text-slate-400 italic">{{ app()->getLocale() === 'ar' ? 'لا مواعيد' : 'No hours' }}</div>
                        @endif
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
(function() {
    document.querySelectorAll('.calendar-day[data-past="0"]').forEach(function(el) {
        el.addEventListener('click', function() {
            var date = this.getAttribute('data-date');
            if (!date) return;
            var isOff = this.classList.contains('bg-red-100');
            var btn = this;
            fetch('{{ route("doctor.calendar.toggle-off") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ date: date })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.is_off) {
                    btn.classList.remove('bg-teal-50/80', 'bg-slate-100', 'border-teal-200', 'border-slate-200', 'hover:bg-teal-100');
                    btn.classList.add('bg-red-50', 'border-red-300', 'hover:bg-red-100');
                    var header = btn.querySelector('.flex.items-center.justify-between.mb-1');
                    if (header && !header.querySelector('span.rounded.bg-red-200')) {
                        var sp = document.createElement('span');
                        sp.className = 'text-[10px] font-semibold uppercase px-1.5 py-0.5 rounded bg-red-200 text-red-800';
                        sp.textContent = '{{ app()->getLocale() === "ar" ? "إجازة" : "Off" }}';
                        header.appendChild(sp);
                    }
                } else {
                    btn.classList.remove('bg-red-50', 'bg-red-100', 'text-red-800', 'border-red-300', 'hover:bg-red-100', 'hover:bg-red-200');
                    var open = btn.getAttribute('data-has-clinic-open') === '1';
                    if (open) {
                        btn.classList.add('bg-teal-50/80', 'border-teal-200', 'hover:bg-teal-100');
                    } else {
                        btn.classList.add('bg-slate-100', 'border-slate-200');
                    }
                    var header = btn.querySelector('.flex.items-center.justify-between.mb-1');
                    if (header) {
                        var badge = header.querySelector('span.rounded.bg-red-200');
                        if (badge) badge.remove();
                    }
                }
            })
            .catch(function() {});
        });
    });
})();
</script>
@endpush
@endsection
