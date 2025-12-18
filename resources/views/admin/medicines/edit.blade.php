@extends('admin.layouts.admin')

@section('title', 'Edit Medicine')
@section('page-title', app()->getLocale() === 'ar' ? 'تعديل دواء' : 'Edit Medicine')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-4xl">
    <form action="{{ route('admin.medicines.update', $medicine) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <div>
                <h3 class="text-lg font-semibold text-slate-900 mb-4">{{ app()->getLocale() === 'ar' ? 'المعلومات الأساسية' : 'Basic Information' }}</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name', $medicine->name) }}" required
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="arabic" class="block text-sm font-medium text-slate-700 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'الاسم العربي' : 'Arabic Name' }}
                        </label>
                        <input type="text" name="arabic" id="arabic" value="{{ old('arabic', $medicine->arabic) }}"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        @error('arabic')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label for="price" class="block text-sm font-medium text-slate-700 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'السعر' : 'Price' }}
                        </label>
                        <input type="number" step="0.01" name="price" id="price" value="{{ old('price', $medicine->price) }}"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        @error('price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="company" class="block text-sm font-medium text-slate-700 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'الشركة' : 'Company' }}
                        </label>
                        <input type="text" name="company" id="company" value="{{ old('company', $medicine->company) }}"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        @error('company')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div>
                        <label for="active_ingredient" class="block text-sm font-medium text-slate-700 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'المادة الفعالة' : 'Active Ingredient' }}
                        </label>
                        <input type="text" name="active_ingredient" id="active_ingredient" value="{{ old('active_ingredient', $medicine->active_ingredient) }}"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div>
                        <label for="dosage_form" class="block text-sm font-medium text-slate-700 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'شكل الجرعة' : 'Dosage Form' }}
                        </label>
                        <input type="text" name="dosage_form" id="dosage_form" value="{{ old('dosage_form', $medicine->dosage_form) }}"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>

                    <div>
                        <label for="route" class="block text-sm font-medium text-slate-700 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'الطريق' : 'Route' }}
                        </label>
                        <input type="text" name="route" id="route" value="{{ old('route', $medicine->route) }}"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-4">
            <a href="{{ route('admin.medicines.index') }}" class="px-4 py-2 text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200 transition-colors">
                {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
            </a>
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-600 transition-colors">
                {{ app()->getLocale() === 'ar' ? 'حفظ التغييرات' : 'Save Changes' }}
            </button>
        </div>
    </form>
</div>
@endsection

