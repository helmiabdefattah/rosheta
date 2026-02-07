@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'تعديل تخصص' : 'Edit Specialization')
@section('page-title', app()->getLocale() === 'ar' ? 'تعديل تخصص' : 'Edit Specialization')
@section('page-description', app()->getLocale() === 'ar' ? 'تعديل بيانات التخصص' : 'Edit specialization details')

@section('content')
<div class="max-w-2xl mx-auto">
    <form action="{{ route('admin.specializations.update', $specialization) }}" method="POST">
        @csrf
        @method('PUT')
        <x-admin.ui.form-card :title="app()->getLocale() === 'ar' ? 'بيانات التخصص' : 'Specialization Details'">
            <div class="space-y-6">
                <div>
                    <x-admin.ui.label for="name" required>{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</x-admin.ui.label>
                    <x-admin.ui.input name="name" :value="old('name', $specialization->name)" required />
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-admin.ui.label for="slug">Slug</x-admin.ui.label>
                    <x-admin.ui.input name="slug" :value="old('slug', $specialization->slug)" />
                    @error('slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-admin.ui.label for="brief">{{ app()->getLocale() === 'ar' ? 'نبذة' : 'Brief' }}</x-admin.ui.label>
                    <textarea name="brief" id="brief" rows="3" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('brief', $specialization->brief) }}</textarea>
                    @error('brief')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </x-admin.ui.form-card>
        <div class="mt-8 flex items-center justify-end gap-3">
            <a href="{{ route('admin.specializations.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50">{{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}</a>
            <x-admin.ui.button type="submit">{{ app()->getLocale() === 'ar' ? 'تحديث' : 'Update' }}</x-admin.ui.button>
        </div>
    </form>
</div>
@endsection
