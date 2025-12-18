@extends('admin.layouts.admin')

@section('title', 'Edit Medical Test')
@section('page-title', app()->getLocale() === 'ar' ? 'تعديل فحص طبي' : 'Edit Medical Test')

@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-3xl">
    <form action="{{ route('admin.medical-tests.update', $medicalTest) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <!-- Details -->
            <div>
                <h3 class="text-lg font-semibold text-slate-900 mb-4">{{ app()->getLocale() === 'ar' ? 'التفاصيل' : 'Details' }}</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="test_name_en" class="block text-sm font-medium text-slate-700 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'الاسم (إنجليزي)' : 'Name (English)' }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="test_name_en" id="test_name_en" value="{{ old('test_name_en', $medicalTest->test_name_en) }}" required
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        @error('test_name_en')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="test_name_ar" class="block text-sm font-medium text-slate-700 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'الاسم (عربي)' : 'Name (Arabic)' }} <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="test_name_ar" id="test_name_ar" value="{{ old('test_name_ar', $medicalTest->test_name_ar) }}" required
                            class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        @error('test_name_ar')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label for="test_description" class="block text-sm font-medium text-slate-700 mb-2">
                        {{ app()->getLocale() === 'ar' ? 'الوصف' : 'Description' }}
                    </label>
                    <textarea name="test_description" id="test_description" rows="4"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('test_description', $medicalTest->test_description) }}</textarea>
                    @error('test_description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-4">
                    <label for="conditions" class="block text-sm font-medium text-slate-700 mb-2">
                        {{ app()->getLocale() === 'ar' ? 'الشروط' : 'Conditions' }}
                    </label>
                    <textarea name="conditions" id="conditions" rows="3"
                        class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('conditions', $medicalTest->conditions) }}</textarea>
                    @error('conditions')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-4">
            <a href="{{ route('admin.medical-tests.index') }}" class="px-4 py-2 text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200 transition-colors">
                {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
            </a>
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-600 transition-colors">
                {{ app()->getLocale() === 'ar' ? 'حفظ التغييرات' : 'Save Changes' }}
            </button>
        </div>
    </form>
</div>
@endsection

