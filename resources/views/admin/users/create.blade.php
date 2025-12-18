@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'إضافة مستخدم' : 'Create User')
@section('page-title', app()->getLocale() === 'ar' ? 'إضافة مستخدم جديد' : 'Create New User')
@section('page-description', app()->getLocale() === 'ar' ? 'أدخل تفاصيل المستخدم الجديد أدناه' : 'Enter the details of the new user below')

@section('content')
<div class="max-w-4xl mx-auto">
    <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf

        <x-admin.ui.form-card :title="app()->getLocale() === 'ar' ? 'معلومات المستخدم' : 'User Information'">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                     <x-admin.ui.label for="name" required>{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</x-admin.ui.label>
                     <x-admin.ui.input name="name" :value="old('name')" required placeholder="e.g. John Doe" />
                </div>

                <!-- Email -->
                <div>
                    <x-admin.ui.label for="email" required>{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email' }}</x-admin.ui.label>
                    <x-admin.ui.input type="email" name="email" :value="old('email')" required placeholder="john@example.com" />
                </div>

                <!-- Password -->
                <div>
                    <x-admin.ui.label for="password" required>{{ app()->getLocale() === 'ar' ? 'كلمة المرور' : 'Password' }}</x-admin.ui.label>
                    <x-admin.ui.input type="password" name="password" required placeholder="••••••••" />
                </div>

                <!-- Confirm Password -->
                <div>
                    <x-admin.ui.label for="password_confirmation" required>{{ app()->getLocale() === 'ar' ? 'تأكيد كلمة المرور' : 'Confirm Password' }}</x-admin.ui.label>
                    <x-admin.ui.input type="password" name="password_confirmation" required placeholder="••••••••" />
                </div>
            </div>
        </x-admin.ui.form-card>

        <div class="mt-8 flex items-center justify-end gap-3">
             <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-all">
                {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
            </a>
            <x-admin.ui.button type="submit">
                {{ app()->getLocale() === 'ar' ? 'حفظ المستخدم' : 'Save User' }}
            </x-admin.ui.button>
        </div>
    </form>
</div>
@endsection
