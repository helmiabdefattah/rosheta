@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'إضافة عميل' : 'Create Client')
@section('page-title', app()->getLocale() === 'ar' ? 'إضافة عميل جديد' : 'Create New Client')
@section('page-description', app()->getLocale() === 'ar' ? 'أدخل تفاصيل العميل الجديد أدناه' : 'Enter the details of the new client below')

@section('content')
<div class="max-w-4xl mx-auto">
    <form action="{{ route('admin.clients.store') }}" method="POST">
        @csrf

        <x-admin.ui.form-card :title="app()->getLocale() === 'ar' ? 'معلومات العميل' : 'Client Information'">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                     <x-admin.ui.label for="name" required>{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</x-admin.ui.label>
                     <x-admin.ui.input name="name" :value="old('name')" required placeholder="e.g. Jane Doe" />
                </div>

                <!-- Phone -->
                <div>
                    <x-admin.ui.label for="phone_number" required>{{ app()->getLocale() === 'ar' ? 'رقم الهاتف' : 'Phone Number' }}</x-admin.ui.label>
                    <x-admin.ui.input type="tel" name="phone_number" :value="old('phone_number')" required placeholder="+20 123 456 7890" />
                </div>

                <!-- Email -->
                <div>
                    <x-admin.ui.label for="email">{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email' }}</x-admin.ui.label>
                    <x-admin.ui.input type="email" name="email" :value="old('email')" placeholder="jane@example.com" />
                </div>

                 <!-- Password -->
                <div>
                    <x-admin.ui.label for="password" required>{{ app()->getLocale() === 'ar' ? 'كلمة المرور' : 'Password' }}</x-admin.ui.label>
                    <x-admin.ui.input type="password" name="password" required placeholder="••••••••" />
                </div>
            </div>
        </x-admin.ui.form-card>

        <div class="mt-8 flex items-center justify-end gap-3">
             <a href="{{ route('admin.clients.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-all">
                {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
            </a>
             <x-admin.ui.button type="submit">
                {{ app()->getLocale() === 'ar' ? 'حفظ العميل' : 'Save Client' }}
            </x-admin.ui.button>
        </div>
    </form>
</div>
@endsection
