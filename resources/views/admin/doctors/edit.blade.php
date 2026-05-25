@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'تعديل طبيب' : 'Edit Doctor')
@section('page-title', app()->getLocale() === 'ar' ? 'تعديل طبيب' : 'Edit Doctor')
@section('page-description', app()->getLocale() === 'ar' ? 'تعديل بيانات الطبيب' : 'Edit doctor details')

@section('content')
<div class="max-w-2xl mx-auto">
    <form action="{{ route('admin.doctors.update', $doctor) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <x-admin.ui.form-card :title="app()->getLocale() === 'ar' ? 'بيانات الطبيب' : 'Doctor Details'">
            <div class="space-y-6">
                <div>
                    <x-admin.ui.label for="name" required>{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</x-admin.ui.label>
                    <x-admin.ui.input name="name" :value="old('name', $doctor->name)" required />
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-admin.ui.label for="slug">{{ app()->getLocale() === 'ar' ? 'Slug (اختياري)' : 'Slug (optional)' }}</x-admin.ui.label>
                    <x-admin.ui.input name="slug" :value="old('slug', $doctor->slug)" placeholder="dr-ahmed-mohamed" />
                    @error('slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-admin.ui.label for="brief">{{ app()->getLocale() === 'ar' ? 'نبذة' : 'Brief' }}</x-admin.ui.label>
                    <textarea name="brief" id="brief" rows="4" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">{{ old('brief', $doctor->brief) }}</textarea>
                    @error('brief')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-admin.ui.label for="specialization_id" required>{{ app()->getLocale() === 'ar' ? 'التخصص' : 'Specialization' }}</x-admin.ui.label>
                    <select name="specialization_id" id="specialization_id" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        @foreach($specializations as $s)
                            <option value="{{ $s->id }}" {{ old('specialization_id', $doctor->specialization_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                    @error('specialization_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-admin.ui.label for="user_id">{{ app()->getLocale() === 'ar' ? 'ربط بحساب مستخدم (اختياري - يمكن ترك الطبيب بدون حساب)' : 'Link to user account (optional - doctor may have no account)' }}</x-admin.ui.label>
                    <select name="user_id" id="user_id" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">{{ app()->getLocale() === 'ar' ? 'لا يوجد / دون ربط' : 'None / Do not link' }}</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ old('user_id', $doctor->user_id) == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                    @error('user_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                @if($doctor->user_id)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-admin.ui.label for="password">{{ app()->getLocale() === 'ar' ? 'كلمة مرور الحساب المرتبط (اختياري)' : 'Linked account password (optional)' }}</x-admin.ui.label>
                        <x-admin.ui.input type="password" name="password" autocomplete="new-password" />
                        @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <x-admin.ui.label for="password_confirmation">{{ app()->getLocale() === 'ar' ? 'تأكيد كلمة المرور' : 'Confirm password' }}</x-admin.ui.label>
                        <x-admin.ui.input type="password" name="password_confirmation" autocomplete="new-password" />
                    </div>
                </div>
                @endif
                <div>
                    <x-admin.ui.label for="profile_image">{{ app()->getLocale() === 'ar' ? 'صورة الطبيب (اختياري)' : 'Doctor photo (optional)' }}</x-admin.ui.label>
                    @if($doctor->getFirstMediaUrl('profile_image'))
                        <div class="mb-2">
                            <img src="{{ $doctor->getFirstMediaUrl('profile_image') }}" alt="" class="w-20 h-20 rounded-full object-cover border-2 border-slate-200">
                            <p class="text-sm text-slate-500 mt-1">{{ app()->getLocale() === 'ar' ? 'صورة حالية - اختر ملفاً جديداً لاستبدالها' : 'Current photo - choose a new file to replace' }}</p>
                        </div>
                    @endif
                    <input type="file" name="profile_image" id="profile_image" accept="image/jpeg,image/png,image/gif,image/webp" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                    @error('profile_image')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </x-admin.ui.form-card>
        <div class="mt-8 flex items-center justify-end gap-3">
            <a href="{{ route('admin.doctors.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50">{{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}</a>
            <x-admin.ui.button type="submit">{{ app()->getLocale() === 'ar' ? 'تحديث' : 'Update' }}</x-admin.ui.button>
        </div>
    </form>
</div>
@endsection
