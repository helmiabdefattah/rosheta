@extends('admin.layouts.admin')

@php $ar = app()->getLocale() === 'ar'; @endphp

@section('title', $ar ? 'إضافة معمل' : 'Create Laboratory')
@section('page-title', $ar ? 'إضافة معمل جديد' : 'Create New Laboratory')
@section('page-description', $ar ? 'أدخل تفاصيل المعمل الجديد أدناه' : 'Enter the details of the new laboratory below')

@section('content')
<div class="max-w-4xl mx-auto pb-6 max-lg:pb-28">
    <form id="laboratory-form" action="{{ route('admin.laboratories.store') }}" method="POST">
        @csrf

        <x-admin.ui.form-card :title="$ar ? 'معلومات المعمل' : 'Laboratory Information'">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <x-admin.ui.label for="name" required>{{ $ar ? 'اسم المعمل' : 'Laboratory name' }}</x-admin.ui.label>
                    <x-admin.ui.input name="name" :value="old('name')" required :placeholder="$ar ? 'مثال: معمل الألفا' : 'e.g. Alfa Labs'" />
                </div>

                <div>
                    <x-admin.ui.label for="type" required>{{ $ar ? 'النوع' : 'Type' }}</x-admin.ui.label>
                    <x-admin.ui.select name="type" :selected="old('type', 'test')" :placeholder="$ar ? 'اختر النوع' : 'Select type'">
                        <option value="test" {{ old('type', 'test') === 'test' ? 'selected' : '' }}>
                            {{ $ar ? 'معمل تحاليل' : 'Test laboratory' }}
                        </option>
                        <option value="radiology" {{ old('type') === 'radiology' ? 'selected' : '' }}>
                            {{ $ar ? 'معمل أشعة' : 'Radiology laboratory' }}
                        </option>
                    </x-admin.ui.select>
                </div>

                <div>
                    <x-admin.ui.label for="user_id">{{ $ar ? 'المالك (مستخدم موجود)' : 'Owner (existing user)' }}</x-admin.ui.label>
                    <x-admin.ui.select name="user_id" :selected="old('user_id')" :placeholder="$ar ? 'اختر المالك (اختياري)' : 'Select owner (optional)'">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}@if($user->email) ({{ $user->email }})@endif
                            </option>
                        @endforeach
                    </x-admin.ui.select>
                </div>

                <div>
                    <x-admin.ui.label for="area_id">{{ $ar ? 'المنطقة' : 'Area' }}</x-admin.ui.label>
                    <x-admin.ui.select name="area_id" :selected="old('area_id')" :placeholder="$ar ? 'اختر المنطقة' : 'Select area'">
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}" {{ old('area_id') == $area->id ? 'selected' : '' }}>
                                @if($ar)
                                    {{ $area->name_ar ?? $area->name }}
                                    @if($area->city)
                                        — {{ $area->city->name_ar ?? $area->city->name }}
                                        @if($area->city->governorate)
                                            — {{ $area->city->governorate->name_ar ?? $area->city->governorate->name }}
                                        @endif
                                    @endif
                                @else
                                    {{ $area->name }}
                                    @if($area->city)
                                        — {{ $area->city->name }}
                                        @if($area->city->governorate)
                                            — {{ $area->city->governorate->name }}
                                        @endif
                                    @endif
                                @endif
                            </option>
                        @endforeach
                    </x-admin.ui.select>
                </div>

                <div>
                    <x-admin.ui.label for="phone">{{ $ar ? 'هاتف المعمل' : 'Laboratory phone' }}</x-admin.ui.label>
                    <x-admin.ui.input name="phone" :value="old('phone')" placeholder="01xxxxxxxxx" />
                </div>

                <div>
                    <x-admin.ui.label for="email">{{ $ar ? 'بريد المعمل' : 'Laboratory email' }}</x-admin.ui.label>
                    <x-admin.ui.input type="email" name="email" :value="old('email')" placeholder="lab@example.com" />
                </div>

                <div class="md:col-span-2">
                    <x-admin.ui.label for="address">{{ $ar ? 'العنوان' : 'Address' }}</x-admin.ui.label>
                    <x-admin.ui.input name="address" :value="old('address')" :placeholder="$ar ? 'العنوان الكامل' : 'Full address'" />
                </div>

                <div class="flex items-center h-full pt-2 md:pt-6">
                    <label class="inline-flex items-center cursor-pointer relative gap-3">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary/30 peer-checked:bg-emerald-500 after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border after:border-slate-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full"></div>
                        <span class="text-sm font-medium text-slate-700">{{ $ar ? 'معمل نشط' : 'Active laboratory' }}</span>
                    </label>
                </div>
            </div>
        </x-admin.ui.form-card>

        <x-admin.ui.form-card
            :title="$ar ? 'حساب تسجيل الدخول للمالك' : 'Owner login account'"
            :description="$ar ? 'إذا لم تختر مستخدمًا موجودًا كمالك، يُنشأ حساب جديد بالبريد والهاتف وكلمة المرور أدناه.' : 'If you do not pick an existing user as owner, a new account is created with the email, phone, and password below.'"
            class="mt-6"
        >
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-admin.ui.label for="account_email">{{ $ar ? 'بريد الحساب' : 'Account email' }}</x-admin.ui.label>
                    <x-admin.ui.input type="email" name="account_email" :value="old('account_email')" autocomplete="off" />
                </div>
                <div>
                    <x-admin.ui.label for="account_phone">{{ $ar ? 'هاتف الحساب' : 'Account phone' }}</x-admin.ui.label>
                    <x-admin.ui.input name="account_phone" :value="old('account_phone')" placeholder="01xxxxxxxxx" autocomplete="off" />
                </div>
                <div>
                    <x-admin.ui.label for="password">{{ $ar ? 'كلمة المرور' : 'Password' }}</x-admin.ui.label>
                    <x-admin.ui.input type="password" name="password" autocomplete="new-password" />
                </div>
                <div>
                    <x-admin.ui.label for="password_confirmation">{{ $ar ? 'تأكيد كلمة المرور' : 'Confirm password' }}</x-admin.ui.label>
                    <x-admin.ui.input type="password" name="password_confirmation" autocomplete="new-password" />
                </div>
            </div>
        </x-admin.ui.form-card>

        <x-admin.ui.form-card :title="$ar ? 'الترخيص والإدارة' : 'License & management'" class="mt-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <x-admin.ui.label for="license_number">{{ $ar ? 'رقم الترخيص' : 'License number' }}</x-admin.ui.label>
                    <x-admin.ui.input name="license_number" :value="old('license_number')" />
                </div>
                <div>
                    <x-admin.ui.label for="manager_name">{{ $ar ? 'اسم المدير' : 'Manager name' }}</x-admin.ui.label>
                    <x-admin.ui.input name="manager_name" :value="old('manager_name')" />
                </div>
                <div>
                    <x-admin.ui.label for="manager_license">{{ $ar ? 'ترخيص المدير' : 'Manager license' }}</x-admin.ui.label>
                    <x-admin.ui.input name="manager_license" :value="old('manager_license')" />
                </div>
            </div>
        </x-admin.ui.form-card>

        <x-admin.ui.form-card :title="$ar ? 'مواعيد العمل' : 'Operating hours'" class="mt-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-admin.ui.label for="opening_time">{{ $ar ? 'وقت الفتح' : 'Opening time' }}</x-admin.ui.label>
                    <x-admin.ui.input type="time" name="opening_time" :value="old('opening_time')" />
                </div>
                <div>
                    <x-admin.ui.label for="closing_time">{{ $ar ? 'وقت الإغلاق' : 'Closing time' }}</x-admin.ui.label>
                    <x-admin.ui.input type="time" name="closing_time" :value="old('closing_time')" />
                </div>
            </div>
        </x-admin.ui.form-card>

        <x-admin.ui.form-card :title="$ar ? 'الموقع الجغرافي' : 'Location coordinates'" class="mt-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-admin.ui.label for="lat">{{ $ar ? 'خط العرض' : 'Latitude' }}</x-admin.ui.label>
                    <x-admin.ui.input name="lat" :value="old('lat')" placeholder="30.0444" />
                </div>
                <div>
                    <x-admin.ui.label for="lng">{{ $ar ? 'خط الطول' : 'Longitude' }}</x-admin.ui.label>
                    <x-admin.ui.input name="lng" :value="old('lng')" placeholder="31.2357" />
                </div>
            </div>
        </x-admin.ui.form-card>

        <x-admin.ui.form-card :title="$ar ? 'ملاحظات' : 'Notes'" class="mt-6">
            <div>
                <x-admin.ui.label for="notes">{{ $ar ? 'ملاحظات إضافية' : 'Additional notes' }}</x-admin.ui.label>
                <textarea name="notes" id="notes" rows="3"
                          class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none"
                          placeholder="{{ $ar ? 'أي ملاحظات داخلية عن المعمل…' : 'Any internal notes about this laboratory…' }}">{{ old('notes') }}</textarea>
                @error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </x-admin.ui.form-card>

        <div class="mt-8 hidden lg:flex items-center justify-end gap-3">
            <a href="{{ route('admin.laboratories.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-all">
                {{ $ar ? 'إلغاء' : 'Cancel' }}
            </a>
            <x-admin.ui.button type="submit">
                {{ $ar ? 'حفظ المعمل' : 'Save laboratory' }}
            </x-admin.ui.button>
        </div>
    </form>
</div>

<div class="lg:hidden fixed bottom-0 inset-x-0 z-[45] border-t border-slate-200 bg-white/95 backdrop-blur-md shadow-[0_-4px_20px_rgba(15,23,42,0.08)] px-4 py-3 flex items-center justify-end gap-3"
     style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom, 0px));">
    <a href="{{ route('admin.laboratories.index') }}" class="px-4 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50">
        {{ $ar ? 'إلغاء' : 'Cancel' }}
    </a>
    <button type="submit" form="laboratory-form" class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-semibold text-white bg-primary rounded-xl hover:opacity-90 shadow-sm">
        {{ $ar ? 'حفظ المعمل' : 'Save laboratory' }}
    </button>
</div>
@endsection
