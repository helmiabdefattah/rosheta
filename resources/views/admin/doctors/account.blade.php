@extends('admin.layouts.admin')

@php
    $l = app()->getLocale() === 'ar';
@endphp

@section('title', $l ? 'إضافة حساب دخول' : 'Add login account')
@section('page-title', $l ? 'إضافة حساب دخول' : 'Add login account')
@section('page-description', $l ? 'إنشاء حساب دخول للطبيب: ' . $doctor->name : 'Create a login account for ' . $doctor->name)

@section('content')
<div class="max-w-2xl mx-auto">
    <form action="{{ route('admin.doctors.account.store', $doctor) }}" method="POST">
        @csrf
        <x-admin.ui.form-card
            :title="$l ? 'بيانات الحساب الجديد' : 'New account'"
            :description="$l
                ? 'هذا الطبيب مسجّل بدون حساب دخول. أدخل البيانات التالية لإنشاء حساب يستطيع الدخول به.'
                : 'This doctor has no login account. Fill in the fields below to create one they can sign in with.'">
            <div class="space-y-6">
                <div>
                    <x-admin.ui.label for="account_name" required>{{ $l ? 'اسم صاحب الحساب' : 'Account name' }}</x-admin.ui.label>
                    <x-admin.ui.input name="account_name" :value="old('account_name', $doctor->name)" required />
                </div>
                <div>
                    <x-admin.ui.label for="account_email" required>{{ $l ? 'البريد الإلكتروني' : 'Email' }}</x-admin.ui.label>
                    <x-admin.ui.input type="email" name="account_email" :value="old('account_email')" autocomplete="off" required />
                </div>
                <div>
                    <x-admin.ui.label for="account_phone" required>{{ $l ? 'رقم الهاتف' : 'Phone' }}</x-admin.ui.label>
                    <x-admin.ui.input name="account_phone" :value="old('account_phone')" placeholder="01xxxxxxxxx" autocomplete="off" required />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-admin.ui.label for="password" required>{{ $l ? 'كلمة المرور' : 'Password' }}</x-admin.ui.label>
                        <x-admin.ui.input type="password" name="password" autocomplete="new-password" required />
                    </div>
                    <div>
                        <x-admin.ui.label for="password_confirmation" required>{{ $l ? 'تأكيد كلمة المرور' : 'Confirm password' }}</x-admin.ui.label>
                        <x-admin.ui.input type="password" name="password_confirmation" autocomplete="new-password" required />
                    </div>
                </div>
                <label class="flex items-start gap-3 cursor-pointer rounded-lg border border-slate-200 p-4">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                           class="mt-1 w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary">
                    <span>
                        <span class="block text-sm font-medium text-slate-700">{{ $l ? 'الحساب مفعّل' : 'Account active' }}</span>
                        <span class="block text-sm text-slate-500">{{ $l ? 'إذا كان غير مفعّل، سيظهر للطبيب عند تسجيل الدخول أن حسابه غير مفعّل ولن يتمكن من الدخول.' : 'When inactive, the doctor is told at login that their account is not active and cannot sign in.' }}</span>
                    </span>
                </label>
                <p class="text-sm text-slate-500">
                    {{ $l ? 'لربط الطبيب بحساب مستخدم موجود بالفعل بدلاً من إنشاء حساب جديد، استخدم' : 'To link this doctor to a user account that already exists instead of creating a new one, use' }}
                    <a href="{{ route('admin.doctors.edit', $doctor) }}" class="font-medium text-primary hover:underline">{{ $l ? 'صفحة التعديل' : 'the edit page' }}</a>.
                </p>
            </div>
        </x-admin.ui.form-card>
        <div class="mt-8 flex items-center justify-end gap-3">
            <a href="{{ route('admin.doctors.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50">{{ $l ? 'إلغاء' : 'Cancel' }}</a>
            <x-admin.ui.button type="submit">{{ $l ? 'إنشاء الحساب' : 'Create account' }}</x-admin.ui.button>
        </div>
    </form>
</div>
@endsection
