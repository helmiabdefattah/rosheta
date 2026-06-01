@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'إضافة فحص طبي' : 'Create Medical Test')
@section('page-title', app()->getLocale() === 'ar' ? 'إضافة فحص طبي جديد' : 'Create New Medical Test')
@section('page-description', app()->getLocale() === 'ar' ? 'أدخل تفاصيل الفحص الطبي الجديد أدناه' : 'Enter the details of the new medical test below')

@php $ar = app()->getLocale() === 'ar'; @endphp

@section('content')
<div class="max-w-4xl mx-auto">
    <form action="{{ route('admin.medical-tests.store') }}" method="POST">
        @csrf

        <x-admin.ui.form-card :title="$ar ? 'تفاصيل الفحص' : 'Test Details'">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Type -->
                <div class="md:col-span-2">
                    <x-admin.ui.label for="type" required>{{ $ar ? 'نوع الفحص' : 'Test type' }}</x-admin.ui.label>
                    <x-admin.ui.select name="type" :selected="old('type', request('type', 'test'))" required :placeholder="$ar ? 'اختر النوع' : 'Select type'">
                        <option value="test" {{ old('type', request('type', 'test')) === 'test' ? 'selected' : '' }}>
                            {{ $ar ? 'فحص تحاليل' : 'Medical test' }}
                        </option>
                        <option value="radiology" {{ old('type', request('type')) === 'radiology' ? 'selected' : '' }}>
                            {{ $ar ? 'أشعة' : 'Radiology' }}
                        </option>
                    </x-admin.ui.select>
                </div>

                <!-- Name (English) -->
                <div>
                     <x-admin.ui.label for="test_name_en" required>{{ $ar ? 'الاسم (إنجليزي)' : 'Name (English)' }}</x-admin.ui.label>
                     <x-admin.ui.input name="test_name_en" :value="old('test_name_en')" required placeholder="e.g. Complete Blood Count" />
                </div>

                <!-- Name (Arabic) -->
                <div>
                    <x-admin.ui.label for="test_name_ar" required>{{ $ar ? 'الاسم (عربي)' : 'Name (Arabic)' }}</x-admin.ui.label>
                    <x-admin.ui.input name="test_name_ar" :value="old('test_name_ar')" required placeholder="مثال: صورة دم كاملة" />
                </div>
            </div>

            <div class="space-y-6 mt-6">
                 <!-- Description -->
                <div>
                    <x-admin.ui.label for="test_description">{{ $ar ? 'الوصف' : 'Description' }}</x-admin.ui.label>
                    <textarea name="test_description" id="test_description" rows="4" 
                        class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/10 focus:outline-none transition-all duration-200">{{ old('test_description') }}</textarea>
                </div>

                <!-- Conditions -->
                <div>
                     <x-admin.ui.label for="conditions">{{ $ar ? 'الشروط' : 'Conditions' }}</x-admin.ui.label>
                     <textarea name="conditions" id="conditions" rows="3"
                        class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-900 placeholder-slate-400 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/10 focus:outline-none transition-all duration-200">{{ old('conditions') }}</textarea>
                </div>
            </div>
        </x-admin.ui.form-card>

        <div class="mt-8 flex items-center justify-end gap-3">
             <a href="{{ route('admin.medical-tests.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-all">
                {{ $ar ? 'إلغاء' : 'Cancel' }}
            </a>
            <x-admin.ui.button type="submit">
                {{ $ar ? 'حفظ الفحص' : 'Save Test' }}
            </x-admin.ui.button>
        </div>
    </form>
</div>
@endsection
