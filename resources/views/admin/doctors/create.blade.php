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
                    <x-admin.ui.label for="assistant_limit">{{ app()->getLocale() === 'ar' ? 'عدد المساعدين المسموح به لكل عيادة' : 'Assistants allowed per clinic' }}</x-admin.ui.label>
                    <x-admin.ui.input type="number" name="assistant_limit" :value="old('assistant_limit', \App\Models\Doctor::DEFAULT_ASSISTANT_LIMIT)" min="0" max="50" />
                    <p class="mt-1 text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'الافتراضي 2 — يمكن للطبيب إنشاء هذا العدد من حسابات المساعدين في كل عيادة.' : 'Default 2 — the doctor may create this many assistant logins in each of their clinics.' }}</p>
                    @error('assistant_limit')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <x-admin.ui.label for="user_id">{{ app()->getLocale() === 'ar' ? 'ربط بحساب مستخدم (اختياري)' : 'Link to user account (optional)' }}</x-admin.ui.label>
                    <p class="text-sm text-slate-500 mb-1">{{ app()->getLocale() === 'ar' ? 'إن لم تختر مستخدمًا، أدخل بيانات الحساب أدناه لإنشاء مستخدم جديد يمكنه تسجيل الدخول.' : 'If you do not select a user, fill in the login fields below to create a new user.' }}</p>
                    <select name="user_id" id="user_id" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                        <option value="">{{ app()->getLocale() === 'ar' ? 'إنشاء حساب جديد' : 'Create new account' }}</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                    @error('user_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="rounded-lg border border-slate-200 bg-slate-50/80 p-4 space-y-4">
                    <p class="text-sm font-medium text-slate-700">{{ app()->getLocale() === 'ar' ? 'بيانات الحساب الجديد (عند عدم اختيار مستخدم)' : 'New account (when no user is selected)' }}</p>
                    <div>
                        <x-admin.ui.label for="account_email">{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email' }}</x-admin.ui.label>
                        <x-admin.ui.input type="email" name="account_email" :value="old('account_email')" autocomplete="off" />
                        @error('account_email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <x-admin.ui.label for="account_phone">{{ app()->getLocale() === 'ar' ? 'رقم الهاتف' : 'Phone' }}</x-admin.ui.label>
                        <x-admin.ui.input name="account_phone" :value="old('account_phone')" placeholder="01xxxxxxxxx" autocomplete="off" />
                        @error('account_phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-admin.ui.label for="password">{{ app()->getLocale() === 'ar' ? 'كلمة المرور' : 'Password' }}</x-admin.ui.label>
                            <x-admin.ui.input type="password" name="password" autocomplete="new-password" />
                            @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <x-admin.ui.label for="password_confirmation">{{ app()->getLocale() === 'ar' ? 'تأكيد كلمة المرور' : 'Confirm password' }}</x-admin.ui.label>
                            <x-admin.ui.input type="password" name="password_confirmation" autocomplete="new-password" />
                        </div>
                    </div>
                </div>
                <label class="flex items-start gap-3 cursor-pointer rounded-lg border border-slate-200 p-4">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}
                           class="mt-1 w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary">
                    <span>
                        <span class="block text-sm font-medium text-slate-700">{{ app()->getLocale() === 'ar' ? 'الحساب مفعّل' : 'Account active' }}</span>
                        <span class="block text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'إذا كان غير مفعّل، سيظهر للطبيب عند تسجيل الدخول أن حسابه غير مفعّل ولن يتمكن من الدخول.' : 'When inactive, the doctor is told at login that their account is not active and cannot sign in.' }}</span>
                    </span>
                </label>
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
