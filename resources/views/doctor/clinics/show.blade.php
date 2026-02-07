@extends('doctor.layouts.dashboard')

@section('title', $clinic->name)
@section('page-title', $clinic->name)
@section('page-description', $clinic->governorate?->name ?? $clinic->governorate?->name_ar)

@section('content')
<div class="max-w-3xl space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <dl class="space-y-3">
            <div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</dt><dd class="font-medium">{{ $clinic->name }}</dd></div>
            @if($clinic->address)<div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'العنوان' : 'Address' }}</dt><dd>{{ $clinic->address }}</dd></div>@endif
            @if($clinic->phone_number)<div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'الهاتف' : 'Phone' }}</dt><dd>{{ $clinic->phone_number }}</dd></div>@endif
            <div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'الموقع' : 'Location' }}</dt><dd>{{ $clinic->governorate?->name ?? $clinic->governorate?->name_ar }} / {{ $clinic->city?->name ?? $clinic->city?->name_ar }} / {{ $clinic->area?->name ?? $clinic->area?->name_ar }}</dd></div>
            <div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'سعر الكشف' : 'Examination price' }}</dt><dd>{{ number_format($clinic->medical_examination_price, 2) }}</dd></div>
            <div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'سعر المتابعة' : 'Follow-up price' }}</dt><dd>{{ number_format($clinic->follow_up_price, 2) }}</dd></div>
            <div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'حجوزات لكل 30 دقيقة' : 'Reservations per 30-min slot' }}</dt><dd>{{ $clinic->getSlotsPerInterval() }}</dd></div>
        </dl>
        <div class="mt-4 flex gap-2">
            <a href="{{ route('doctor.clinics.edit', $clinic) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 font-medium">{{ app()->getLocale() === 'ar' ? 'تعديل العيادة' : 'Edit clinic' }}</a>
            <a href="{{ route('doctor.appointments.index') }}?clinic_id={{ $clinic->id }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50 font-medium">{{ app()->getLocale() === 'ar' ? 'إدارة المواعيد' : 'Manage appointments' }}</a>
        </div>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="font-semibold text-slate-800 mb-3">{{ app()->getLocale() === 'ar' ? 'مواعيد العمل' : 'Working Hours' }}</h3>
        @php $dayLabels = ['saturday'=>'السبت','sunday'=>'الأحد','monday'=>'الإثنين','tuesday'=>'الثلاثاء','wednesday'=>'الأربعاء','thursday'=>'الخميس','friday'=>'الجمعة']; if (app()->getLocale() !== 'ar') $dayLabels = ['saturday'=>'Sat','sunday'=>'Sun','monday'=>'Mon','tuesday'=>'Tue','wednesday'=>'Wed','thursday'=>'Thu','friday'=>'Fri']; @endphp
        <ul class="space-y-2">
            @foreach($clinic->workingHours as $wh)
                <li class="flex justify-between py-1 border-b border-slate-100 last:border-0">
                    <span class="font-medium">{{ $dayLabels[$wh->day] ?? $wh->day }}</span>
                    <span>{{ $wh->is_closed ? (app()->getLocale() === 'ar' ? 'مغلق' : 'Closed') : ($wh->from?->format('H:i') . ' – ' . $wh->to?->format('H:i')) }}</span>
                </li>
            @endforeach
            @if($clinic->workingHours->isEmpty())
                <li class="text-slate-500">{{ app()->getLocale() === 'ar' ? 'لم يتم تحديد مواعيد' : 'No working hours set.' }}</li>
            @endif
        </ul>
    </div>
</div>
@endsection
