@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'إضافة عرض فحص' : 'Create Test Offer')
@section('page-title', app()->getLocale() === 'ar' ? 'إضافة عرض فحص طبي' : 'Create Medical Test Offer')

@section('content')
<div class="max-w-4xl mx-auto">
    <form action="{{ route('admin.medical-test-offers.store') }}" method="POST">
        @csrf

        <x-admin.ui.form-card :title="app()->getLocale() === 'ar' ? 'تفاصيل العرض' : 'Offer Details'">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Laboratory -->
                <div class="md:col-span-2">
                    <x-admin.ui.label for="laboratory_id" required>{{ app()->getLocale() === 'ar' ? 'المعمل' : 'Laboratory' }}</x-admin.ui.label>
                    @if($hasLaboratory && $laboratory)
                        <div class="px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700">
                            {{ $laboratory->name }}
                        </div>
                    @else
                        <x-admin.ui.select name="laboratory_id" required>
                            <option value="">{{ app()->getLocale() === 'ar' ? 'اختر المعمل' : 'Select Laboratory' }}</option>
                            @foreach($laboratories as $lab)
                                <option value="{{ $lab->id }}" @selected(old('laboratory_id') == $lab->id)>{{ $lab->name }}</option>
                            @endforeach
                        </x-admin.ui.select>
                    @endif
                </div>

                <!-- Medical Test -->
                <div class="md:col-span-2">
                    <x-admin.ui.label for="medical_test_id" required>{{ app()->getLocale() === 'ar' ? 'الفحص الطبي' : 'Medical Test' }}</x-admin.ui.label>
                    <x-admin.ui.select name="medical_test_id" required>
                        <option value="">{{ app()->getLocale() === 'ar' ? 'اختر الفحص' : 'Select Test' }}</option>
                        @foreach($medicalTests as $test)
                            <option value="{{ $test->id }}" @selected(old('medical_test_id') == $test->id)">
                                {{ app()->getLocale() === 'ar' ? ($test->test_name_ar ?: $test->test_name_en) : $test->test_name_en }}
                            </option>
                        @endforeach
                    </x-admin.ui.select>
                </div>

                <!-- Price -->
                <div>
                    <x-admin.ui.label for="price" required>{{ app()->getLocale() === 'ar' ? 'السعر' : 'Price' }}</x-admin.ui.label>
                    <x-admin.ui.input type="number" step="0.01" name="price" :value="old('price')" required placeholder="0.00" />
                </div>

                <!-- Offer Price -->
                <div>
                    <x-admin.ui.label for="offer_price">{{ app()->getLocale() === 'ar' ? 'سعر العرض' : 'Offer Price' }}</x-admin.ui.label>
                    <x-admin.ui.input type="number" step="0.01" name="offer_price" :value="old('offer_price')" placeholder="0.00" />
                </div>

                <!-- Discount -->
                <div class="md:col-span-2">
                    <x-admin.ui.label for="discount">{{ app()->getLocale() === 'ar' ? 'الخصم (%)' : 'Discount (%)' }}</x-admin.ui.label>
                    <x-admin.ui.input type="number" step="0.01" min="0" max="100" name="discount" :value="old('discount', 0)" />
                </div>
            </div>
        </x-admin.ui.form-card>

        <div class="mt-8 flex items-center justify-end gap-3">
            <a href="{{ route('admin.medical-test-offers.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-all">
                {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
            </a>
            <x-admin.ui.button type="submit" color="green">
                {{ app()->getLocale() === 'ar' ? 'حفظ العرض' : 'Save Offer' }}
            </x-admin.ui.button>
        </div>
    </form>
</div>
@endsection


