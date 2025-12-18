@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'إضافة مدينة' : 'Create City')
@section('page-title', app()->getLocale() === 'ar' ? 'إضافة مدينة جديدة' : 'Create New City')
@section('page-description', app()->getLocale() === 'ar' ? 'أدخل تفاصيل المدينة الجديدة أدناه' : 'Enter the details of the new city below')

@section('content')
<div class="max-w-4xl mx-auto">
    <form action="{{ route('admin.cities.store') }}" method="POST">
        @csrf

        <x-admin.ui.form-card :title="app()->getLocale() === 'ar' ? 'معلومات المدينة' : 'City Information'">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name (English) -->
                <div>
                     <x-admin.ui.label for="name" required>{{ app()->getLocale() === 'ar' ? 'الاسم (إنجليزي)' : 'Name (English)' }}</x-admin.ui.label>
                     <x-admin.ui.input name="name" :value="old('name')" required placeholder="e.g. Nasr City" />
                </div>

                <!-- Name (Arabic) -->
                <div>
                    <x-admin.ui.label for="name_ar" required>{{ app()->getLocale() === 'ar' ? 'الاسم (عربي)' : 'Name (Arabic)' }}</x-admin.ui.label>
                    <x-admin.ui.input name="name_ar" :value="old('name_ar')" required placeholder="مثال: مدينة نصر" />
                </div>

                 <!-- Governorate -->
                <div class="md:col-span-2">
                    <x-admin.ui.label for="governorate_id" required>{{ app()->getLocale() === 'ar' ? 'المحافظة' : 'Governorate' }}</x-admin.ui.label>
                    <x-admin.ui.select name="governorate_id" :options="$governorates->pluck('name', 'id')" :selected="old('governorate_id')" required placeholder="{{ app()->getLocale() === 'ar' ? 'اختر المحافظة' : 'Select Governorate' }}" />
                </div>

                <!-- Sort Order -->
                <div>
                     <x-admin.ui.label for="sort_order">{{ app()->getLocale() === 'ar' ? 'ترتيب' : 'Sort Order' }}</x-admin.ui.label>
                     <x-admin.ui.input type="number" name="sort_order" :value="old('sort_order', 0)" min="0" placeholder="0" />
                </div>

                <!-- Is Active -->
                <div class="flex items-center h-full pt-6">
                    <label class="inline-flex items-center cursor-pointer relative">
                         <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                        <span class="ml-3 text-sm font-medium text-slate-700">{{ app()->getLocale() === 'ar' ? 'نشط' : 'Is Active' }}</span>
                    </label>
                </div>
            </div>
        </x-admin.ui.form-card>

        <div class="mt-8 flex items-center justify-end gap-3">
             <a href="{{ route('admin.cities.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-all">
                {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
            </a>
            <x-admin.ui.button type="submit">
                {{ app()->getLocale() === 'ar' ? 'حفظ المدينة' : 'Save City' }}
            </x-admin.ui.button>
        </div>
    </form>
</div>
@endsection
