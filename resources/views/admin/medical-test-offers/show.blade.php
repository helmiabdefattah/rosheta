@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'تفاصيل عرض الفحص' : 'Test Offer Details')
@section('page-title', app()->getLocale() === 'ar' ? 'تفاصيل عرض الفحص الطبي' : 'Medical Test Offer Details')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <div class="text-slate-500 text-sm">{{ app()->getLocale() === 'ar' ? 'المعرف' : 'ID' }}</div>
                <div class="font-semibold text-slate-900">{{ $medicalTestOffer->id }}</div>
            </div>
            <div>
                <div class="text-slate-500 text-sm">{{ app()->getLocale() === 'ar' ? 'المعمل' : 'Laboratory' }}</div>
                <div class="font-semibold text-slate-900">{{ $medicalTestOffer->laboratory?->name ?? '-' }}</div>
            </div>
            <div class="md:col-span-2">
                <div class="text-slate-500 text-sm">{{ app()->getLocale() === 'ar' ? 'الفحص الطبي' : 'Medical Test' }}</div>
                <div class="font-semibold text-slate-900">
                    {{ app()->getLocale() === 'ar' ? ($medicalTestOffer->medicalTest?->test_name_ar ?: $medicalTestOffer->medicalTest?->test_name_en) : $medicalTestOffer->medicalTest?->test_name_en }}
                </div>
            </div>
            <div>
                <div class="text-slate-500 text-sm">{{ app()->getLocale() === 'ar' ? 'السعر' : 'Price' }}</div>
                <div class="font-semibold text-slate-900">EGP {{ number_format($medicalTestOffer->price, 2) }}</div>
            </div>
            <div>
                <div class="text-slate-500 text-sm">{{ app()->getLocale() === 'ar' ? 'سعر العرض' : 'Offer Price' }}</div>
                <div class="font-semibold text-slate-900">
                    {{ $medicalTestOffer->offer_price !== null ? 'EGP ' . number_format($medicalTestOffer->offer_price, 2) : '-' }}
                </div>
            </div>
            <div class="md:col-span-2">
                <div class="text-slate-500 text-sm">{{ app()->getLocale() === 'ar' ? 'الخصم (%)' : 'Discount (%)' }}</div>
                <div class="font-semibold text-slate-900">{{ number_format($medicalTestOffer->discount, 2) }}%</div>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('admin.medical-test-offers.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-all">
            {{ app()->getLocale() === 'ar' ? 'رجوع' : 'Back' }}
        </a>
        <a href="{{ route('admin.medical-test-offers.edit', $medicalTestOffer) }}" class="px-5 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-xl hover:bg-blue-700 transition-all">
            {{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}
        </a>
    </div>
</div>
@endsection


