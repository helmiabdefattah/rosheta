@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'تفاصيل المحافظة' : 'Governorate Details')
@section('page-title', app()->getLocale() === 'ar' ? 'تفاصيل المحافظة' : 'Governorate Details')

@section('header-actions')
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.governorates.edit', $governorate) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all font-medium text-sm">
            {{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}
        </a>
        <a href="{{ route('admin.governorates.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-all font-medium text-sm">
            {{ app()->getLocale() === 'ar' ? 'عودة' : 'Back' }}
        </a>
    </div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
                <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-1">{{ app()->getLocale() === 'ar' ? 'الاسم (إنجليزي)' : 'Name (English)' }}</h3>
                <p class="text-lg font-bold text-slate-800">{{ $governorate->name }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-1">{{ app()->getLocale() === 'ar' ? 'الاسم (عربي)' : 'Name (Arabic)' }}</h3>
                <p class="text-lg font-bold text-slate-800">{{ $governorate->name_ar }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-1">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</h3>
                @if($governorate->is_active)
                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-emerald-100 text-emerald-700">
                        {{ app()->getLocale() === 'ar' ? 'نشط' : 'Active' }}
                    </span>
                @else
                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-red-100 text-red-700">
                        {{ app()->getLocale() === 'ar' ? 'غير نشط' : 'Inactive' }}
                    </span>
                @endif
            </div>
            <div>
                <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-1">{{ app()->getLocale() === 'ar' ? 'ترتيب العرض' : 'Sort Order' }}</h3>
                <p class="text-lg font-bold text-slate-800">{{ $governorate->sort_order }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-slate-800">{{ app()->getLocale() === 'ar' ? 'المدن' : 'Cities' }}</h3>
            <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-bold">{{ $governorate->cities->count() }}</span>
        </div>
        
        <div class="space-y-3">
            @forelse($governorate->cities as $city)
                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl hover:bg-slate-100 transition-colors">
                    <div class="font-medium text-slate-700">{{ $city->name }} ({{ $city->name_ar }})</div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.cities.show', $city) }}" class="text-slate-500 hover:text-slate-800 text-sm font-bold">
                            {{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}
                        </a>
                        <span class="text-slate-300">|</span>
                        <a href="{{ route('admin.cities.edit', $city) }}" class="text-primary hover:underline text-sm font-bold">
                            {{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-slate-400 italic">
                    {{ app()->getLocale() === 'ar' ? 'لا توجد مدن لهذه المحافظة' : 'No cities found for this governorate.' }}
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
