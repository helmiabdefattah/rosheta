@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'إضافة طبيب' : 'Create Doctor')
@section('page-title', app()->getLocale() === 'ar' ? 'إضافة طبيب' : 'Create Doctor')
@section('page-description', app()->getLocale() === 'ar' ? 'أدخل بيانات الطبيب' : 'Enter doctor details')

@section('content')
<div class="max-w-2xl mx-auto">
    <form action="{{ route('admin.doctors.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <x-admin.ui.form-card :title="app()->getLocale() === 'ar' ? 'بيانات الطبيب' : 'Doctor Details'">
            <div class="space-y-6">
                <div>
                    <x-admin.ui.label for="name" required>{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</x-admin.ui.label>
                    <x-admin.ui.input name="name" :value="old('name')" required placeholder="Dr. Ahmed Mohamed" />
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-admin.ui.label for="slug">{{ app()->getLocale() === 'ar' ? 'Slug (اختياري - يُولّد من الاسم إن تُرك فارغاً)' : 'Slug (optional - auto-generated from name if empty)' }}</x-admin.ui.label>
                    <x-admin.ui.input name="slug" :value="old('slug')" placeholder="dr-ahmed-mohamed" />
                    @error('slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-admin.ui.label for="brief">{{ app()->getLocale() === 'ar' ? 'نبذة' : 'Brief' }}</x-admin.ui.label>
                    <textarea name="brief" id="brief" rows="4" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="{{ app()->getLocale() === 'ar' ? 'وصف مختصر عن الطبيب' : 'Short description about the doctor' }}">{{ old('brief') }}</textarea>
                    @error('brief')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-admin.ui.label for="specialization_id" required>{{ app()->getLocale() === 'ar' ? 'التخصص' : 'Specialization' }}</x-admin.ui.label>
                    <select name="specialization_id" id="specialization_id" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">{{ app()->getLocale() === 'ar' ? 'اختر التخصص' : 'Select' }}</option>
                        @foreach($specializations as $s)
                            <option value="{{ $s->id }}" {{ old('specialization_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                    @error('specialization_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-admin.ui.label for="user_id">{{ app()->getLocale() === 'ar' ? 'ربط بحساب مستخدم (اختياري)' : 'Link to user account (optional)' }}</x-admin.ui.label>
                    <p class="text-sm text-slate-500 mb-1">{{ app()->getLocale() === 'ar' ? 'إن تركت "إنشاء حساب جديد" سيُنشأ مستخدم جديد بكلمة المرور: password' : 'If you leave "Create new account" selected, a new user will be created with password: password' }}</p>
                    <select name="user_id" id="user_id" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">{{ app()->getLocale() === 'ar' ? 'إنشاء حساب جديد (كلمة المرور: password)' : 'Create new account (password: password)' }}</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                    @error('user_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-admin.ui.label for="profile_image">{{ app()->getLocale() === 'ar' ? 'صورة الطبيب (اختياري)' : 'Doctor photo (optional)' }}</x-admin.ui.label>
                    <input type="file" name="profile_image" id="profile_image" accept="image/jpeg,image/png,image/gif,image/webp" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    @error('profile_image')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </x-admin.ui.form-card>
        <div class="mt-8 flex items-center justify-end gap-3">
            <a href="{{ route('admin.doctors.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50">{{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}</a>
            <x-admin.ui.button type="submit">{{ app()->getLocale() === 'ar' ? 'حفظ' : 'Save' }}</x-admin.ui.button>
        </div>
    </form>
</div>
@endsection
