@extends('admin.layouts.admin')

@section('title', $doctor->name)
@section('page-title', $doctor->name)
@section('page-description', app()->getLocale() === 'ar' ? 'تفاصيل الطبيب' : 'Doctor details')

@section('header-actions')
    <a href="{{ route('admin.doctors.edit', $doctor) }}" class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-xl hover:bg-teal-600">{{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}</a>
    <a href="{{ route('admin.clinics.create') }}?doctor_id={{ $doctor->id }}" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-xl hover:bg-green-700">{{ app()->getLocale() === 'ar' ? 'إضافة عيادة' : 'Add Clinic' }}</a>
@endsection

@section('content')
<div class="max-w-4xl space-y-6">
    <x-admin.ui.form-card :title="app()->getLocale() === 'ar' ? 'البيانات' : 'Details'">
        @if($doctor->getFirstMediaUrl('profile_image'))
            <div class="mb-4">
                <img src="{{ $doctor->getFirstMediaUrl('profile_image') }}" alt="{{ $doctor->name }}" class="w-24 h-24 rounded-full object-cover border-2 border-slate-200">
            </div>
        @endif
        <dl class="space-y-3">
            <div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</dt><dd class="font-medium">{{ $doctor->name }}</dd></div>
            @if($doctor->slug)<div><dt class="text-sm text-slate-500">Slug</dt><dd>{{ $doctor->slug }}</dd></div>@endif
            @if($doctor->brief)<div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'نبذة' : 'Brief' }}</dt><dd class="text-slate-700">{{ $doctor->brief }}</dd></div>@endif
            <div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'التخصص' : 'Specialization' }}</dt><dd>{{ $doctor->specialization?->name ?? '-' }}</dd></div>
            <div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'الحساب' : 'User account' }}</dt><dd>{{ $doctor->user ? $doctor->user->email : (app()->getLocale() === 'ar' ? 'غير مرتبط' : 'Not linked') }}</dd></div>
        </dl>
    </x-admin.ui.form-card>

    <x-admin.ui.form-card :title="app()->getLocale() === 'ar' ? 'العيادات' : 'Clinics'">
        @if($doctor->clinics->isEmpty())
            <p class="text-slate-500">{{ app()->getLocale() === 'ar' ? 'لا توجد عيادات' : 'No clinics.' }}</p>
            <a href="{{ route('admin.clinics.create') }}?doctor_id={{ $doctor->id }}" class="mt-2 inline-block text-green-600 font-medium hover:text-green-700">{{ app()->getLocale() === 'ar' ? 'إضافة عيادة' : 'Add clinic' }}</a>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach($doctor->clinics as $c)
                    <li class="py-2 flex justify-between items-center">
                        <a href="{{ route('admin.clinics.show', $c) }}" class="font-medium text-slate-800 hover:text-primary">{{ $c->name }}</a>
                        <span class="text-sm text-slate-500">{{ $c->address ? \Illuminate\Support\Str::limit($c->address, 40) : '-' }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-admin.ui.form-card>
</div>
@endsection
