@extends('laboratories.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'إضافة مستخدم' : 'Add User')

@section('page-description', app()->getLocale() === 'ar' ? 'إضافة مستخدم جديد للمعمل' : 'Add a new user to the laboratory')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
@endpush

@section('content')
    <div class="bg-white rounded-lg shadow-sm p-6">
        <form method="POST" action="{{ route('laboratories.users.store') }}">
            @csrf
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }} <span class="text-red-500">*</span></label>
                <input type="text" name="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" 
                       value="{{ old('name') }}" required>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email' }} <span class="text-red-500">*</span></label>
                <input type="email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" 
                       value="{{ old('email') }}" required>
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ app()->getLocale() === 'ar' ? 'كلمة المرور' : 'Password' }} <span class="text-red-500">*</span></label>
                <input type="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" required>
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">{{ app()->getLocale() === 'ar' ? 'تأكيد كلمة المرور' : 'Confirm Password' }} <span class="text-red-500">*</span></label>
                <input type="password" name="password_confirmation" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" required>
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('laboratories.users.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                    {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                </a>
                <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                    {{ app()->getLocale() === 'ar' ? 'إنشاء' : 'Create' }}
                </button>
            </div>
        </form>
    </div>
@endsection

