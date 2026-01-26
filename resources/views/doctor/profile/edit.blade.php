@extends('doctor.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'تعديل الملف الشخصي' : 'Edit Profile')
@section('page-title', app()->getLocale() === 'ar' ? 'الملف الشخصي' : 'My Profile')

@section('content')
<div class="max-w-2xl">
    <form action="{{ route('doctor.profile.update') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }} <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $doctor->name) }}" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'Slug (اختياري)' : 'Slug (optional)' }}</label>
            <input type="text" name="slug" value="{{ old('slug', $doctor->slug) }}" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500">
            @error('slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'التخصص' : 'Specialization' }} <span class="text-red-500">*</span></label>
            <select name="specialization_id" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                @foreach($specializations as $s)
                    <option value="{{ $s->id }}" {{ old('specialization_id', $doctor->specialization_id) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
            @error('specialization_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'نبذة' : 'Brief' }}</label>
            <textarea name="brief" rows="4" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500">{{ old('brief', $doctor->brief) }}</textarea>
            @error('brief')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'صورة الطبيب' : 'Profile image' }}</label>
            @if($doctor->getFirstMediaUrl('profile_image'))
                <img src="{{ $doctor->getFirstMediaUrl('profile_image') }}" alt="" class="w-20 h-20 rounded-full object-cover mb-2 border-2 border-gray-200">
            @endif
            <input type="file" name="profile_image" accept="image/jpeg,image/png,image/gif,image/webp" class="w-full px-4 py-2 border border-slate-300 rounded-lg">
            @error('profile_image')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <hr class="border-gray-200">
        <h4 class="font-semibold text-slate-800">{{ app()->getLocale() === 'ar' ? 'إعدادات الحساب (المستخدم)' : 'Account (User) settings' }}</h4>
        @if($doctor->user)
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'البريد' : 'Email' }}</label>
                <input type="email" name="email" value="{{ old('email', $doctor->user->email) }}" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'رقم الهاتف' : 'Phone' }}</label>
                <input type="text" name="phone_number" value="{{ old('phone_number', $doctor->user->phone_number) }}" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500">
                @error('phone_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'كلمة المرور الجديدة' : 'New password' }}</label>
                <input type="password" name="password" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500" placeholder="••••••••">
                @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'تأكيد كلمة المرور' : 'Confirm password' }}</label>
                <input type="password" name="password_confirmation" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500" placeholder="••••••••">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">{{ app()->getLocale() === 'ar' ? 'كلمة المرور الحالية (مطلوبة لتغيير كلمة المرور)' : 'Current password (required to change password)' }}</label>
                <input type="password" name="current_password" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500" placeholder="••••••••">
                @error('current_password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        @endif
        <div class="flex justify-end">
            <button type="submit" class="px-5 py-2.5 bg-teal-600 text-white rounded-lg hover:bg-teal-700 font-medium">{{ app()->getLocale() === 'ar' ? 'حفظ' : 'Save' }}</button>
        </div>
    </form>
</div>
@endsection
