@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'تفاصيل المنظمة الخيرية' : 'Charitable Organization Details')
@section('page-title', app()->getLocale() === 'ar' ? 'تفاصيل المنظمة الخيرية' : 'Charitable Organization Details')

@section('header-actions')
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.charitable-organizations.edit', $charitableOrganization) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all font-medium text-sm">
            {{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}
        </a>
        <a href="{{ route('admin.charitable-organizations.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-all font-medium text-sm">
            {{ app()->getLocale() === 'ar' ? 'عودة' : 'Back' }}
        </a>
    </div>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <div class="space-y-6">
            <!-- Name -->
            <div>
                <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-1">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</h3>
                <p class="text-lg font-bold text-slate-800">{{ $charitableOrganization->name }}</p>
            </div>

            <!-- Location -->
            <div>
                <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-1">{{ app()->getLocale() === 'ar' ? 'الموقع' : 'Location' }}</h3>
                <div class="space-y-1">
                    @if($charitableOrganization->governorate)
                        <p class="text-lg text-slate-800">
                            <span class="font-semibold">{{ app()->getLocale() === 'ar' ? 'المحافظة:' : 'Governorate:' }}</span>
                            {{ app()->getLocale() === 'ar' ? ($charitableOrganization->governorate->name_ar ?? $charitableOrganization->governorate->name) : ($charitableOrganization->governorate->name ?? $charitableOrganization->governorate->name_ar) }}
                        </p>
                    @endif
                    @if($charitableOrganization->city)
                        <p class="text-lg text-slate-800">
                            <span class="font-semibold">{{ app()->getLocale() === 'ar' ? 'المدينة:' : 'City:' }}</span>
                            {{ app()->getLocale() === 'ar' ? ($charitableOrganization->city->name_ar ?? $charitableOrganization->city->name) : ($charitableOrganization->city->name ?? $charitableOrganization->city->name_ar) }}
                        </p>
                    @endif
                    @if($charitableOrganization->area)
                        <p class="text-lg text-slate-800">
                            <span class="font-semibold">{{ app()->getLocale() === 'ar' ? 'المنطقة:' : 'Area:' }}</span>
                            {{ app()->getLocale() === 'ar' ? ($charitableOrganization->area->name_ar ?? $charitableOrganization->area->name) : ($charitableOrganization->area->name ?? $charitableOrganization->area->name_ar) }}
                        </p>
                    @endif
                </div>
            </div>

            <!-- Address -->
            @if($charitableOrganization->address)
            <div>
                <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-1">{{ app()->getLocale() === 'ar' ? 'العنوان' : 'Address' }}</h3>
                <p class="text-lg text-slate-800">{{ $charitableOrganization->address }}</p>
            </div>
            @endif

            <!-- Phone Numbers -->
            <div>
                <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-1">{{ app()->getLocale() === 'ar' ? 'أرقام الهاتف' : 'Phone Numbers' }}</h3>
                @if($charitableOrganization->phone_numbers && count($charitableOrganization->phone_numbers) > 0)
                    <div class="space-y-2">
                        @foreach($charitableOrganization->phone_numbers as $phone)
                            <p class="text-lg text-slate-800">{{ $phone }}</p>
                        @endforeach
                    </div>
                @else
                    <p class="text-slate-400 italic">{{ app()->getLocale() === 'ar' ? 'لا توجد أرقام هواتف' : 'No phone numbers' }}</p>
                @endif
            </div>

            <!-- Services -->
            <div>
                <h3 class="text-sm font-semibold text-slate-400 uppercase tracking-wider mb-1">{{ app()->getLocale() === 'ar' ? 'الخدمات' : 'Services' }}</h3>
                @if($charitableOrganization->services && count($charitableOrganization->services) > 0)
                    <div class="space-y-2">
                        @foreach($charitableOrganization->services as $service)
                            <p class="text-lg text-slate-800">{{ $service }}</p>
                        @endforeach
                    </div>
                @else
                    <p class="text-slate-400 italic">{{ app()->getLocale() === 'ar' ? 'لا توجد خدمات' : 'No services' }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
