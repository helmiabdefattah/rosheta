@extends('admin.layouts.admin')

@section('title', $specialization->name)
@section('page-title', $specialization->name)
@section('page-description', app()->getLocale() === 'ar' ? 'تفاصيل التخصص' : 'Specialization details')

@section('header-actions')
    <a href="{{ route('admin.specializations.edit', $specialization) }}" class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-xl hover:bg-teal-600">{{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}</a>
@endsection

@section('content')
<div class="max-w-2xl space-y-6">
    <x-admin.ui.form-card :title="app()->getLocale() === 'ar' ? 'البيانات' : 'Details'">
        <dl class="space-y-3">
            <div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</dt><dd class="font-medium">{{ $specialization->name }}</dd></div>
            <div><dt class="text-sm text-slate-500">Slug</dt><dd>{{ $specialization->slug }}</dd></div>
            @if($specialization->brief)
            <div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'نبذة' : 'Brief' }}</dt><dd class="text-slate-700">{{ $specialization->brief }}</dd></div>
            @endif
            <div><dt class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'عدد الأطباء' : 'Doctors count' }}</dt><dd>{{ $specialization->doctors_count }}</dd></div>
        </dl>
    </x-admin.ui.form-card>
</div>
@endsection
