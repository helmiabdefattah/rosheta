@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'تعديل موعد' : 'Edit Appointment')
@section('page-title', app()->getLocale() === 'ar' ? 'تعديل موعد' : 'Edit Appointment')
@section('page-description', app()->getLocale() === 'ar' ? 'تحديث حالة الموعد' : 'Update appointment status')

@section('content')
<div class="max-w-2xl mx-auto">
    <form action="{{ route('admin.appointments.update', $appointment) }}" method="POST">
        @csrf
        @method('PUT')

        <x-admin.ui.form-card :title="app()->getLocale() === 'ar' ? 'تفاصيل الموعد' : 'Appointment Details'">
            <dl class="space-y-2 mb-6">
                <div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'الطبيب' : 'Doctor' }}</dt><dd>{{ $appointment->doctor->name }} ({{ $appointment->doctor->specialization?->name }})</dd></div>
                <div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'العيادة' : 'Clinic' }}</dt><dd>{{ $appointment->clinic->name }}</dd></div>
                <div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'التاريخ والوقت' : 'Date & Time' }}</dt><dd>{{ $appointment->appointment_date->format('Y-m-d') }} {{ $appointment->appointment_time ? \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') : '' }}</dd></div>
                <div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'النوع' : 'Type' }}</dt><dd>{{ $appointment->type === 'medical_examination' ? (app()->getLocale() === 'ar' ? 'كشف' : 'Examination') : (app()->getLocale() === 'ar' ? 'متابعة' : 'Follow-up') }}</dd></div>
                <div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'السعر' : 'Price' }}</dt><dd>{{ number_format($appointment->price, 2) }}</dd></div>
            </dl>

            <div class="space-y-6">
                <div>
                    <x-admin.ui.label for="status" required>{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</x-admin.ui.label>
                    <select name="status" id="status" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="pending" {{ old('status', $appointment->status) === 'pending' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'قيد الانتظار' : 'Pending' }}</option>
                        <option value="confirmed" {{ old('status', $appointment->status) === 'confirmed' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'مؤكد' : 'Confirmed' }}</option>
                        <option value="completed" {{ old('status', $appointment->status) === 'completed' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'منتهي' : 'Completed' }}</option>
                        <option value="cancelled" {{ old('status', $appointment->status) === 'cancelled' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'ملغي' : 'Cancelled' }}</option>
                    </select>
                    @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-admin.ui.label for="notes">{{ app()->getLocale() === 'ar' ? 'ملاحظات' : 'Notes' }}</x-admin.ui.label>
                    <textarea name="notes" id="notes" rows="3" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('notes', $appointment->notes) }}</textarea>
                    @error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </x-admin.ui.form-card>

        <div class="mt-8 flex items-center justify-end gap-3">
            <a href="{{ route('admin.appointments.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50">{{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}</a>
            <x-admin.ui.button type="submit">{{ app()->getLocale() === 'ar' ? 'تحديث' : 'Update' }}</x-admin.ui.button>
        </div>
    </form>
</div>
@endsection
