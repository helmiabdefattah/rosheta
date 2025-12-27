@extends('client.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'الملف الشخصي' : 'My Profile')

@section('page-title', app()->getLocale() === 'ar' ? 'الملف الشخصي' : 'My Profile')
@section('page-description', app()->getLocale() === 'ar' ? 'إدارة معلوماتك الشخصية' : 'Manage your personal information')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 lg:p-8">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-slate-900">
                {{ app()->getLocale() === 'ar' ? 'معلومات الملف الشخصي' : 'Profile Information' }}
            </h2>
            <p class="text-sm text-gray-600 mt-2">
                {{ app()->getLocale() === 'ar' 
                    ? 'قم بتحديث معلومات ملفك الشخصي. يمكنك تغيير الاسم ورقم الهاتف فقط.' 
                    : 'Update your profile information. You can only change your name and phone number.' }}
            </p>
        </div>

        <form method="POST" action="{{ route('client.profile.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'الاسم الكامل' : 'Full Name' }}
                </label>
                <input 
                    id="name" 
                    type="text" 
                    name="name" 
                    value="{{ old('name', $client->name) }}" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition duration-200 outline-none"
                    placeholder="{{ app()->getLocale() === 'ar' ? 'أدخل اسمك الكامل' : 'Enter your full name' }}"
                >
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'رقم الهاتف' : 'Phone Number' }}
                </label>
                <input 
                    id="phone_number" 
                    type="text" 
                    name="phone_number" 
                    value="{{ old('phone_number', $client->phone_number) }}" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition duration-200 outline-none"
                    placeholder="{{ app()->getLocale() === 'ar' ? 'أدخل رقم هاتفك' : 'Enter your phone number' }}"
                >
                <p class="mt-1 text-xs text-gray-500">
                    {{ app()->getLocale() === 'ar' 
                        ? 'يمكنك استخدام رقم الهاتف أو البريد الإلكتروني لتسجيل الدخول' 
                        : 'You can use phone number or email to login' }}
                </p>
                @error('phone_number')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email Address' }}
                    <span class="text-gray-500 text-xs">({{ app()->getLocale() === 'ar' ? 'اختياري' : 'Optional' }})</span>
                </label>
                <input 
                    id="email" 
                    type="email" 
                    name="email" 
                    value="{{ old('email', $client->email) }}" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition duration-200 outline-none"
                    placeholder="{{ app()->getLocale() === 'ar' ? 'أدخل بريدك الإلكتروني' : 'Enter your email address' }}"
                >
                <p class="mt-1 text-xs text-gray-500">
                    {{ app()->getLocale() === 'ar' 
                        ? 'يمكنك استخدام رقم الهاتف أو البريد الإلكتروني لتسجيل الدخول' 
                        : 'You can use phone number or email to login' }}
                </p>
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>


            <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                <a href="{{ route('client.dashboard') }}" class="px-6 py-3 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200">
                    {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                </a>
                <button 
                    type="submit" 
                    class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-teal-700 transition duration-200 font-medium"
                >
                    {{ app()->getLocale() === 'ar' ? 'حفظ التغييرات' : 'Save Changes' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

