@extends('admin.layouts.admin')

@section('title', 'Edit City')
@section('page-title', app()->getLocale() === 'ar' ? 'تعديل مدينة' : 'Edit City')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-3xl">
    <form action="{{ route('admin.cities.update', $city) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <!-- Basic Information -->
            <div>
                <h3 class="text-lg font-semibold text-slate-900 mb-4">{{ app()->getLocale() === 'ar' ? 'المعلومات الأساسية' : 'Basic Information' }}</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'الاسم (إنجليزي)' : 'Name (English)' }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name', $city->name) }}" required
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="name_ar" class="block text-sm font-medium text-slate-700 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'الاسم (عربي)' : 'Name (Arabic)' }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name_ar" id="name_ar" value="{{ old('name_ar', $city->name_ar) }}" required
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        @error('name_ar')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label for="governorate_id" class="block text-sm font-medium text-slate-700 mb-2">
                        {{ app()->getLocale() === 'ar' ? 'المحافظة' : 'Governorate' }} <span class="text-red-500">*</span>
                    </label>
                    <select name="governorate_id" id="governorate_id" required
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">{{ app()->getLocale() === 'ar' ? 'اختر المحافظة' : 'Select Governorate' }}</option>
                        @foreach($governorates as $governorate)
                            <option value="{{ $governorate->id }}" {{ old('governorate_id', $city->governorate_id) == $governorate->id ? 'selected' : '' }}>
                                {{ $governorate->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('governorate_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-slate-700 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'ترتيب' : 'Sort Order' }}
                        </label>
                        <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $city->sort_order) }}" min="0"
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        @error('sort_order')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center mt-6">
                        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $city->is_active) ? 'checked' : '' }}
                            class="w-4 h-4 text-primary border-slate-300 rounded focus:ring-primary">
                        <label for="is_active" class="ml-2 text-sm font-medium text-slate-700">
                            {{ app()->getLocale() === 'ar' ? 'نشط' : 'Is Active' }}
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-4">
            <a href="{{ route('admin.cities.index') }}" class="px-4 py-2 text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200 transition-colors">
                {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
            </a>
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-600 transition-colors">
                {{ app()->getLocale() === 'ar' ? 'حفظ التغييرات' : 'Save Changes' }}
            </button>
        </div>
    </form>
</div>
@endsection

