@extends('doctor.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'عياداتي' : 'My Clinics')
@section('page-title', app()->getLocale() === 'ar' ? 'عياداتي' : 'My Clinics')

@section('content')
<div class="mb-6 flex justify-end">
    <a href="{{ route('doctor.clinics.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-teal-600 text-white rounded-lg hover:bg-teal-700 font-medium">
        <i class="bi bi-plus-lg"></i>
        {{ app()->getLocale() === 'ar' ? 'إضافة عيادة' : 'Add Clinic' }}
    </a>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($clinics as $clinic)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow flex flex-col">
            <a href="{{ route('doctor.clinics.show', $clinic) }}" class="block p-5 flex-1">
                <h3 class="font-bold text-slate-800 text-lg">{{ $clinic->name }}</h3>
                <p class="text-sm text-teal-600 mt-1">{{ $clinic->governorate?->name ?? $clinic->governorate?->name_ar }} / {{ $clinic->city?->name ?? $clinic->city?->name_ar }}</p>
                @if($clinic->address)<p class="text-sm text-slate-500 mt-2 line-clamp-2">{{ $clinic->address }}</p>@endif
                @if($clinic->phone_number)<p class="text-sm text-slate-600 mt-1"><i class="bi bi-telephone me-1"></i>{{ $clinic->phone_number }}</p>@endif
                <p class="text-xs text-slate-400 mt-2">{{ app()->getLocale() === 'ar' ? 'سعر الكشف:' : 'Examination:' }} {{ number_format($clinic->medical_examination_price, 2) }}</p>
            </a>
            <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between gap-2 flex-wrap">
                <a href="{{ route('doctor.clinics.show', $clinic) }}" class="text-sm text-teal-600 font-medium hover:underline">{{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}</a>
                <div class="flex items-center gap-2">
                    <a href="{{ route('doctor.clinics.edit', $clinic) }}" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50">{{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}</a>
                    <a href="{{ route('doctor.appointments.index') }}?clinic_id={{ $clinic->id }}" class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-teal-700 bg-teal-50 border border-teal-200 rounded-lg hover:bg-teal-100">{{ app()->getLocale() === 'ar' ? 'المواعيد' : 'Appointments' }}</a>
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center text-slate-500">
            {{ app()->getLocale() === 'ar' ? 'لا توجد عيادات مرتبطة بحسابك.' : 'No clinics linked to your account.' }}
        </div>
    @endforelse
</div>
@endsection
