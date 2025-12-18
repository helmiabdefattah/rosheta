@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'إضافة دواء' : 'Create Medicine')
@section('page-title', app()->getLocale() === 'ar' ? 'إضافة دواء جديد' : 'Create New Medicine')
@section('page-description', app()->getLocale() === 'ar' ? 'أدخل تفاصيل الدواء الجديد أدناه' : 'Enter the details of the new medicine below')

@section('content')
<div class="max-w-4xl mx-auto">
    <form action="{{ route('admin.medicines.store') }}" method="POST">
        @csrf

        <x-admin.ui.form-card :title="app()->getLocale() === 'ar' ? 'معلومات الدواء' : 'Medicine Information'">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <x-admin.ui.label for="name" required>{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</x-admin.ui.label>
                    <x-admin.ui.input name="name" :value="old('name')" required placeholder="e.g. Panadol" />
                </div>

                <!-- Arabic Name -->
                <div>
                    <x-admin.ui.label for="arabic">{{ app()->getLocale() === 'ar' ? 'الاسم العربي' : 'Arabic Name' }}</x-admin.ui.label>
                    <x-admin.ui.input name="arabic" :value="old('arabic')" placeholder="مثال: بانادول" />
                </div>

                <!-- Price -->
                <div>
                    <x-admin.ui.label for="price">{{ app()->getLocale() === 'ar' ? 'السعر' : 'Price' }}</x-admin.ui.label>
                    <x-admin.ui.input type="number" step="0.01" name="price" :value="old('price')" placeholder="0.00" />
                </div>

                <!-- Company -->
                <div>
                     <x-admin.ui.label for="company">{{ app()->getLocale() === 'ar' ? 'الشركة' : 'Company' }}</x-admin.ui.label>
                     <x-admin.ui.input name="company" :value="old('company')" placeholder="e.g. Pfizer" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6 pt-6 border-t border-slate-100">
                <!-- Active Ingredient -->
                <div>
                    <x-admin.ui.label for="active_ingredient">{{ app()->getLocale() === 'ar' ? 'المادة الفعالة' : 'Active Ingredient' }}</x-admin.ui.label>
                    <x-admin.ui.input name="active_ingredient" :value="old('active_ingredient')" placeholder="e.g. Paracetamol" />
                </div>

                <!-- Dosage Form -->
                <div>
                    <x-admin.ui.label for="dosage_form">{{ app()->getLocale() === 'ar' ? 'شكل الجرعة' : 'Dosage Form' }}</x-admin.ui.label>
                    <x-admin.ui.input name="dosage_form" :value="old('dosage_form')" placeholder="e.g. Tablet" />
                </div>

                <!-- Route -->
                <div>
                    <x-admin.ui.label for="route">{{ app()->getLocale() === 'ar' ? 'الطريق' : 'Route' }}</x-admin.ui.label>
                     <x-admin.ui.input name="route" :value="old('route')" placeholder="e.g. Oral" />
                </div>
            </div>
        </x-admin.ui.form-card>

        <div class="mt-8 flex items-center justify-end gap-3">
            <a href="{{ route('admin.medicines.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-all">
                {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
            </a>
            <x-admin.ui.button type="submit">
                {{ app()->getLocale() === 'ar' ? 'حفظ الدواء' : 'Save Medicine' }}
            </x-admin.ui.button>
        </div>
    </form>
</div>
@endsection
