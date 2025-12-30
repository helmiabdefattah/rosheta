@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'إضافة معمل' : 'Create Laboratory')
@section('page-title', app()->getLocale() === 'ar' ? 'إضافة معمل جديد' : 'Create New Laboratory')
@section('page-description', app()->getLocale() === 'ar' ? 'أدخل تفاصيل المعمل الجديد أدناه' : 'Enter the details of the new laboratory below')

@section('content')
<div class="max-w-4xl mx-auto">
    <form action="{{ route('admin.laboratories.store') }}" method="POST">
        @csrf

        <x-admin.ui.form-card :title="app()->getLocale() === 'ar' ? 'معلومات المعمل' : 'Laboratory Information'">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div class="md:col-span-2">
                     <x-admin.ui.label for="name" required>{{ app()->getLocale() === 'ar' ? 'اسم المعمل' : 'Laboratory Name' }}</x-admin.ui.label>
                     <x-admin.ui.input name="name" :value="old('name')" required placeholder="e.g. Alfa Labs" />
                </div>

                <!-- Type -->
                <div>
                    <x-admin.ui.label for="type" required>{{ app()->getLocale() === 'ar' ? 'النوع' : 'Type' }}</x-admin.ui.label>
                    <x-admin.ui.select name="type" :selected="old('type', 'test')" placeholder="{{ app()->getLocale() === 'ar' ? 'اختر النوع' : 'Select Type' }}">
                        <option value="test" {{ old('type', 'test') === 'test' ? 'selected' : '' }}>
                            {{ app()->getLocale() === 'ar' ? 'تحاليل' : 'Test Laboratory' }}
                        </option>
                        <option value="radiology" {{ old('type') === 'radiology' ? 'selected' : '' }}>
                            {{ app()->getLocale() === 'ar' ? 'أشعة' : 'Radiology Laboratory' }}
                        </option>
                    </x-admin.ui.select>
                </div>

                <!-- Owner User -->
                <div>
                    <x-admin.ui.label for="user_id">{{ app()->getLocale() === 'ar' ? 'المالك' : 'Owner' }}</x-admin.ui.label>
                    <x-admin.ui.select name="user_id" :selected="old('user_id')" placeholder="{{ app()->getLocale() === 'ar' ? 'اختر المالك (اختياري)' : 'Select Owner (Optional)' }}">
                         @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </x-admin.ui.select>
                </div>

                <!-- Area -->
                <div>
                    <x-admin.ui.label for="area_id">{{ app()->getLocale() === 'ar' ? 'المنطقة' : 'Area' }}</x-admin.ui.label>
                    <x-admin.ui.select name="area_id" :selected="old('area_id')" placeholder="{{ app()->getLocale() === 'ar' ? 'اختر المنطقة' : 'Select Area' }}">
                         @foreach($areas as $area)
                            <option value="{{ $area->id }}" {{ old('area_id') == $area->id ? 'selected' : '' }}>
                                {{ $area->name }} - {{ $area->city->name ?? '' }}
                            </option>
                        @endforeach
                    </x-admin.ui.select>
                </div>

                <!-- Phone -->
                <div>
                     <x-admin.ui.label for="phone">{{ app()->getLocale() === 'ar' ? 'الهاتف' : 'Phone' }}</x-admin.ui.label>
                     <x-admin.ui.input name="phone" :value="old('phone')" placeholder="e.g. 010xxxxxxxx" />
                </div>

                 <!-- Email -->
                <div>
                     <x-admin.ui.label for="email">{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email' }}</x-admin.ui.label>
                     <x-admin.ui.input type="email" name="email" :value="old('email')" placeholder="lab@example.com" />
                </div>

                <!-- Address -->
                <div class="md:col-span-2">
                     <x-admin.ui.label for="address">{{ app()->getLocale() === 'ar' ? 'العنوان' : 'Address' }}</x-admin.ui.label>
                     <x-admin.ui.input name="address" :value="old('address')" placeholder="Full address" />
                </div>

                <!-- Is Active -->
                <div class="flex items-center h-full pt-6">
                    <label class="inline-flex items-center cursor-pointer relative">
                         <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary-600"></div>
                        <span class="ml-3 text-sm font-medium text-slate-700">{{ app()->getLocale() === 'ar' ? 'نشط' : 'Is Active' }}</span>
                    </label>
                </div>
            </div>
        </x-admin.ui.form-card>
        
        <x-admin.ui.form-card :title="app()->getLocale() === 'ar' ? 'الموقع الجغرافي' : 'Location Coordinates'" class="mt-6">
             <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                 <div>
                     <x-admin.ui.label for="lat">{{ app()->getLocale() === 'ar' ? 'خط العرض (Latitude)' : 'Latitude' }}</x-admin.ui.label>
                     <x-admin.ui.input name="lat" :value="old('lat')" placeholder="e.g. 30.0444" />
                 </div>
                 <div>
                     <x-admin.ui.label for="lng">{{ app()->getLocale() === 'ar' ? 'خط الطول (Longitude)' : 'Longitude' }}</x-admin.ui.label>
                     <x-admin.ui.input name="lng" :value="old('lng')" placeholder="e.g. 31.2357" />
                 </div>
             </div>
        </x-admin.ui.form-card>

        <div class="mt-8 flex items-center justify-end gap-3">
             <a href="{{ route('admin.laboratories.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-all">
                {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
            </a>
            <x-admin.ui.button type="submit">
                {{ app()->getLocale() === 'ar' ? 'حفظ المعمل' : 'Save Laboratory' }}
            </x-admin.ui.button>
        </div>
    </form>
</div>
@endsection
