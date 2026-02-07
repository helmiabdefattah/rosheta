@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'تفاصيل الموعد' : 'Appointment Details')
@section('page-title', '#' . $appointment->id . ' - ' . $appointment->appointment_date->format('Y-m-d') . ' ' . $appointment->appointment_time)
@section('page-description', app()->getLocale() === 'ar' ? 'تفاصيل الموعد' : 'Appointment details')

@section('header-actions')
    <a href="{{ route('admin.appointments.edit', $appointment) }}" class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-xl hover:bg-teal-600">{{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}</a>
@endsection

@section('content')
<div class="max-w-2xl space-y-6">
    <x-admin.ui.form-card :title="app()->getLocale() === 'ar' ? 'البيانات' : 'Details'">
        <dl class="space-y-3">
            <div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'الطبيب' : 'Doctor' }}</dt><dd><a href="{{ route('admin.doctors.show', $appointment->doctor) }}" class="font-medium text-primary hover:underline">{{ $appointment->doctor->name }}</a> ({{ $appointment->doctor->specialization?->name }})</dd></div>
            <div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'العيادة' : 'Clinic' }}</dt><dd><a href="{{ route('admin.clinics.show', $appointment->clinic) }}" class="text-primary hover:underline">{{ $appointment->clinic->name }}</a></dd></div>
            <div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'التاريخ' : 'Date' }}</dt><dd>{{ $appointment->appointment_date->format('Y-m-d') }}</dd></div>
            <div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'الوقت' : 'Time' }}</dt><dd>{{ $appointment->appointment_time ? \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') : '' }}</dd></div>
            <div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'النوع' : 'Type' }}</dt><dd>{{ $appointment->type === 'medical_examination' ? (app()->getLocale() === 'ar' ? 'كشف' : 'Medical examination') : (app()->getLocale() === 'ar' ? 'متابعة' : 'Follow-up') }}</dd></div>
            <div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'السعر' : 'Price' }}</dt><dd>{{ number_format($appointment->price, 2) }}</dd></div>
            <div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</dt><dd><span class="px-2 py-1 text-sm rounded {{ $appointment->status === 'confirmed' ? 'bg-emerald-100 text-emerald-700' : ($appointment->status === 'cancelled' ? 'bg-red-100 text-red-700' : ($appointment->status === 'completed' ? 'bg-slate-100 text-slate-700' : 'bg-amber-100 text-amber-700')) }}">{{ $appointment->status }}</span></dd></div>
            <div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'المريض' : 'Patient' }}</dt><dd>{{ $appointment->user ? $appointment->user->name . ' (' . $appointment->user->email . ')' : '-' }}</dd></div>
            @if($appointment->notes)
            <div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'ملاحظات' : 'Notes' }}</dt><dd class="text-slate-700">{{ $appointment->notes }}</dd></div>
            @endif
        </dl>
    </x-admin.ui.form-card>
</div>
@endsection
