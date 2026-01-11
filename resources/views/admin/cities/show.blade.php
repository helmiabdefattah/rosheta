@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'تفاصيل المدينة' : 'City Details')
@section('page-title', app()->getLocale() === 'ar' ? 'تفاصيل المدينة' : 'City Details')

@section('header-actions')
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.cities.edit', $city) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all font-medium text-sm">
            {{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}
        </a>
        <a href="{{ route('admin.cities.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-all font-medium text-sm">
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
                <p class="text-lg font-bold text-slate-800">{{ $city->name }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-1">{{ app()->getLocale() === 'ar' ? 'الاسم (عربي)' : 'Name (Arabic)' }}</h3>
                <p class="text-lg font-bold text-slate-800">{{ $city->name_ar }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-1">{{ app()->getLocale() === 'ar' ? 'المحافظة' : 'Governorate' }}</h3>
                <p class="text-lg font-bold text-slate-800">{{ $city->governorate->name ?? 'N/A' }}</p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-1">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</h3>
                @if($city->is_active)
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
                <p class="text-lg font-bold text-slate-800">{{ $city->sort_order }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-slate-800">{{ app()->getLocale() === 'ar' ? 'المناطق' : 'Areas' }}</h3>
            <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-full text-xs font-bold">{{ $city->areas->count() }}</span>
        </div>
        
        <div class="space-y-3">
            @forelse($city->areas as $area)
                <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl hover:bg-slate-100 transition-colors">
                    <div class="font-medium text-slate-700">{{ $area->name }} ({{ $area->name_ar }})</div>
                    <a href="{{ route('admin.areas.edit', $area) }}" class="text-primary hover:underline text-sm font-bold">
                        {{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}
                    </a>
                </div>
            @empty
                <div class="text-center py-8 text-slate-400 italic">
                    {{ app()->getLocale() === 'ar' ? 'لا توجد مناطق لهذه المدينة' : 'No areas found for this city.' }}
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
