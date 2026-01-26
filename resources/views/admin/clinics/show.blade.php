@extends('admin.layouts.admin')

@section('title', $clinic->name)
@section('page-title', $clinic->name)
@section('page-description', app()->getLocale() === 'ar' ? 'تفاصيل العيادة' : 'Clinic details')

@section('header-actions')
    <a href="{{ route('admin.clinics.edit', $clinic) }}" class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-xl hover:bg-teal-600">{{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}</a>
    <a href="{{ route('admin.appointments.create') }}?clinic_id={{ $clinic->id }}" class="px-4 py-2 text-sm font-medium text-white bg-slate-600 rounded-xl hover:bg-slate-700">{{ app()->getLocale() === 'ar' ? 'حجز موعد' : 'Book Appointment' }}</a>
@endsection

@section('content')
<div class="max-w-4xl space-y-6">
    <x-admin.ui.form-card :title="app()->getLocale() === 'ar' ? 'البيانات' : 'Details'">
        <dl class="space-y-3">
            <div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</dt><dd class="font-medium">{{ $clinic->name }}</dd></div>
            <div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'الأطباء' : 'Doctors' }}</dt><dd class="space-y-1">@php $clinicDoctors = $clinic->doctors->isNotEmpty() ? $clinic->doctors : collect([$clinic->doctor])->filter(); @endphp @forelse($clinicDoctors as $doc)<a href="{{ route('admin.doctors.show', $doc) }}" class="text-primary hover:underline block">{{ $doc->name }} ({{ $doc->specialization?->name ?? '-' }})</a>@empty<span>{{ '-' }}</span>@endforelse</dd></div>
            @if($clinic->address)<div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'العنوان' : 'Address' }}</dt><dd>{{ $clinic->address }}</dd></div>@endif
            @if($clinic->phone_number)<div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'رقم الهاتف' : 'Phone' }}</dt><dd>{{ $clinic->phone_number }}</dd></div>@endif
            <div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'الموقع' : 'Location' }}</dt><dd>{{ $clinic->governorate?->name ?? $clinic->governorate?->name_ar }} / {{ $clinic->city?->name ?? $clinic->city?->name_ar }} / {{ $clinic->area?->name ?? $clinic->area?->name_ar }}</dd></div>
            <div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'سعر الكشف' : 'Examination price' }}</dt><dd>{{ number_format($clinic->medical_examination_price, 2) }}</dd></div>
            <div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'سعر المتابعة' : 'Follow-up price' }}</dt><dd>{{ number_format($clinic->follow_up_price, 2) }}</dd></div>
        </dl>
    </x-admin.ui.form-card>

    <x-admin.ui.form-card :title="app()->getLocale() === 'ar' ? 'مواعيد العمل' : 'Working Hours'">
        @php
            $dayLabels = ['saturday'=>'السبت','sunday'=>'الأحد','monday'=>'الإثنين','tuesday'=>'الثلاثاء','wednesday'=>'الأربعاء','thursday'=>'الخميس','friday'=>'الجمعة'];
            if (app()->getLocale() !== 'ar') $dayLabels = ['saturday'=>'Sat','sunday'=>'Sun','monday'=>'Mon','tuesday'=>'Tue','wednesday'=>'Wed','thursday'=>'Thu','friday'=>'Fri'];
        @endphp
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
    </x-admin.ui.form-card>
</div>
@endsection
