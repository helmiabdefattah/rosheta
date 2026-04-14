@extends('client.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'الرئيسية' : 'Home')

@section('page-title', app()->getLocale() === 'ar' ? 'الرئيسية' : 'Home')
@section('page-description', app()->getLocale() === 'ar' ? 'ابحث، احجز، وتتبع طلباتك' : 'Search, book, and track your requests')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
        padding-left: 12px;
        padding-right: 20px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
        right: 8px;
    }
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #3b82f6;
        outline: 0;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    [dir="rtl"] .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding-right: 12px;
        padding-left: 20px;
    }
    [dir="rtl"] .select2-container--default .select2-selection--single .select2-selection__arrow {
        left: 8px;
        right: auto;
    }
    #serviceProviderMap {
        height: 500px;
        width: 100%;
        border-radius: 0.5rem;
        border: 1px solid #e5e7eb;
    }
    .filter-toggle {
        cursor: pointer;
        user-select: none;
    }
    .filter-content {
        transition: max-height 0.3s ease-out;
        overflow: hidden;
    }
    .filter-content.collapsed {
        max-height: 0;
    }
    .filter-content.expanded {
        max-height: 1000px;
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto">

        @include('client.partials.dashboard-landing')

            @if(request()->has('governorate_id') && request()->has('provider_type'))
                {{-- Map Section (for laboratories, pharmacies, and doctors/clinics; not charity) --}}
                @if(!in_array(request('provider_type'), ['charity']) && isset($markers) && count($markers) > 0)
                    <div class="bg-white rounded-lg shadow p-6 mt-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            {{ app()->getLocale() === 'ar' ? 'الخريطة' : 'Map' }}
                            <span class="text-sm text-gray-500 font-normal">
                                ({{ count($markers) }} {{ app()->getLocale() === 'ar' ? 'موقع' : 'location' }}{{ count($markers) !== 1 ? 's' : '' }})
                            </span>
                        </h3>
                        <div id="serviceProviderMap"></div>
                    </div>
                @endif

                {{-- Results Cards Section (for all provider types) --}}
                @if(isset($results) && count($results) > 0)
                    <div class="bg-white rounded-lg shadow p-6 mt-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            {{ app()->getLocale() === 'ar' ? 'نتائج البحث' : 'Search Results' }}
                            <span class="text-sm text-gray-500 font-normal">
                                ({{ count($results) }} {{ app()->getLocale() === 'ar' ? 'نتيجة' : 'result' }}{{ count($results) !== 1 ? 's' : '' }})
                            </span>
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($results as $item)
                                <div class="border rounded-lg p-4 hover:shadow-lg transition-shadow" data-marker-id="{{ $item->id }}">
                                    {{-- Logo (doctor profile image for clinics) --}}
                                    @if($providerType === 'doctor' && $item->doctor && $item->doctor->getFirstMediaUrl('profile_image'))
                                        <div class="mb-3">
                                            <img src="{{ $item->doctor->getFirstMediaUrl('profile_image') }}"
                                                 alt="{{ $item->doctor->name }}"
                                                 class="w-20 h-20 rounded-full object-cover"
                                                 onerror="this.style.display='none'">
                                        </div>
                                    @elseif(isset($item->logo) && $item->logo)
                                        <div class="mb-3">
                                            <img src="{{ asset('storage/' . $item->logo) }}"
                                                 alt="{{ $item->name }}"
                                                 class="w-full h-32 object-contain rounded"
                                                 onerror="this.style.display='none'">
                                        </div>
                                    @endif

                                    {{-- Name --}}
                                    <h4 class="text-lg font-semibold text-gray-800 mb-2">
                                        @if($providerType === 'doctor')
                                            {{ $item->doctor?->name ?? $item->name }}
                                            @if($item->name && $item->doctor) <span class="text-sm font-normal text-gray-600">— {{ $item->name }}</span> @endif
                                        @else
                                            {{ $item->name }}
                                        @endif
                                    </h4>

                                    {{-- Type Badge --}}
                                    <div class="mb-2">
                                        <span class="px-2 py-1 text-xs rounded-full
                                            {{ $providerType === 'laboratory' ? 'bg-blue-100 text-blue-800' : ($providerType === 'pharmacy' ? 'bg-green-100 text-green-800' : ($providerType === 'doctor' ? 'bg-teal-100 text-teal-800' : 'bg-rose-100 text-rose-800')) }}">
                                            @if($providerType === 'laboratory')
                                                {{ app()->getLocale() === 'ar' ? 'مختبر' : 'Laboratory' }}
                                            @elseif($providerType === 'pharmacy')
                                                {{ app()->getLocale() === 'ar' ? 'صيدلية' : 'Pharmacy' }}
                                            @elseif($providerType === 'doctor')
                                                {{ app()->getLocale() === 'ar' ? 'عيادة / طبيب' : 'Clinic / Doctor' }}
                                            @else
                                                {{ app()->getLocale() === 'ar' ? 'منظمة خيرية' : 'Charitable Organization' }}
                                            @endif
                                        </span>
                                    </div>
                                    @if($providerType === 'doctor' && $item->doctor?->specialization)
                                        <p class="text-sm text-primary font-medium mb-2">{{ $item->doctor->specialization->name }}</p>
                                    @endif

                                    {{-- Location --}}
                                    @if($providerType === 'charity')
                                        @if($item->city || $item->area)
                                            <div class="text-sm text-gray-600 mb-2">
                                                <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                </svg>
                                                @if($item->area)
                                                    {{ app()->getLocale() === 'ar' ? ($item->area->name_ar ?? $item->area->name) : ($item->area->name ?? $item->area->name_ar) }}
                                                @endif
                                                @if($item->city)
                                                    @if($item->area), @endif
                                                    {{ app()->getLocale() === 'ar' ? ($item->city->name_ar ?? $item->city->name) : ($item->city->name ?? $item->city->name_ar) }}
                                                @endif
                                            </div>
                                        @endif
                                    @elseif($providerType === 'doctor')
                                        @if($item->governorate || $item->city || $item->area)
                                            <div class="text-sm text-gray-600 mb-2">
                                                <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                </svg>
                                                @if($item->area)
                                                    {{ app()->getLocale() === 'ar' ? ($item->area->name_ar ?? $item->area->name) : ($item->area->name ?? $item->area->name_ar) }}
                                                @endif
                                                @if($item->city)
                                                    @if($item->area), @endif
                                                    {{ app()->getLocale() === 'ar' ? ($item->city->name_ar ?? $item->city->name) : ($item->city->name ?? $item->city->name_ar) }}
                                                @endif
                                                @if($item->governorate)
                                                    @if($item->city || $item->area), @endif
                                                    {{ app()->getLocale() === 'ar' ? ($item->governorate->name_ar ?? $item->governorate->name) : ($item->governorate->name ?? $item->governorate->name_ar) }}
                                                @endif
                                            </div>
                                        @endif
                                    @else
                                        @if($item->area)
                                            <div class="text-sm text-gray-600 mb-2">
                                                <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                </svg>
                                                {{ app()->getLocale() === 'ar' ? $item->area->name_ar : $item->area->name }}
                                                @if($item->area->city)
                                                    , {{ app()->getLocale() === 'ar' ? $item->area->city->name_ar : $item->area->city->name }}
                                                @endif
                                            </div>
                                        @endif
                                    @endif

                                    {{-- Contact Info --}}
                                    <div class="space-y-1 text-sm text-gray-600">
                                        @if($providerType === 'doctor')
                                            @if($item->address)
                                                <div>
                                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                    </svg>
                                                    {{ Str::limit($item->address, 80) }}
                                                </div>
                                            @endif
                                            <div class="text-gray-700 mt-1">
                                                {{ app()->getLocale() === 'ar' ? 'كشف:' : 'Examination:' }} <strong>{{ number_format($item->medical_examination_price ?? 0, 2) }}</strong>
                                            </div>
                                        @elseif($providerType === 'charity')
                                            @if($item->phone_numbers && count($item->phone_numbers) > 0)
                                                @foreach($item->phone_numbers as $phone)
                                                    <div>
                                                        <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                        </svg>
                                                        {{ $phone }}
                                                    </div>
                                                @endforeach
                                            @endif
                                            @if($item->services && count($item->services) > 0)
                                                <div class="mt-2">
                                                    <span class="font-semibold">{{ app()->getLocale() === 'ar' ? 'الخدمات:' : 'Services:' }}</span>
                                                    <ul class="list-disc list-inside mt-1">
                                                        @foreach($item->services as $service)
                                                            <li class="text-xs">{{ $service }}</li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                        @else
                                            @if($item->phone)
                                                <div>
                                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                    </svg>
                                                    {{ $item->phone }}
                                                </div>
                                            @endif
                                            @if($item->email)
                                                <div>
                                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                    </svg>
                                                    {{ $item->email }}
                                                </div>
                                            @endif
                                            @if($item->opening_time && $item->closing_time)
                                                <div>
                                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    {{ \Carbon\Carbon::parse($item->opening_time)->format('H:i') }} -
                                                    {{ \Carbon\Carbon::parse($item->closing_time)->format('H:i') }}
                                                </div>
                                            @endif
                                        @endif
                                    </div>

                                    {{-- Address (for non-doctor: lab/pharmacy) --}}
                                    @if($providerType !== 'doctor' && $item->address)
                                        <div class="mt-2 text-sm text-gray-500">
                                            {{ Str::limit($item->address, 100) }}
                                        </div>
                                    @endif

                                    {{-- Action Buttons --}}
                                    <div class="mt-4 space-y-2">
                                        @if($providerType === 'charity')
                                            {{-- Charity organizations don't have action buttons, just display info --}}
                                        @elseif($providerType === 'doctor')
                                            <a href="{{ route('client.doctor-reservation.book', $item) }}"
                                               class="block w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors text-center">
                                                <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                {{ app()->getLocale() === 'ar' ? 'حجز موعد' : 'Book Appointment' }}
                                            </a>
                                        @elseif($providerType === 'laboratory')
{{--                                            <a href="{{ route('client.laboratories.offers', $item->id) }}" --}}
{{--                                               class="block w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors text-center mb-2">--}}
{{--                                                <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">--}}
{{--                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 011 12V7a4 4 0 014-4z"/>--}}
{{--                                                </svg>--}}
{{--                                                {{ app()->getLocale() === 'ar' ? 'عرض العروض' : 'View Offers' }}--}}
{{--                                            </a> -->--}}
                                            @if($item->type === 'test' || $item->type === 'both')
                                                <a href="{{ route('client.test-requests.create', ['type' => 'test', 'laboratory_id' => $item->id]) }}"
                                                   class="block w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors text-center mb-2">
                                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    {{ app()->getLocale() === 'ar' ? 'طلب تحاليل طبية' : 'Request Medical Tests' }}
                                                </a>
                                            @endif
                                            @if($item->type === 'radiology' || $item->type === 'both')
                                                <a href="{{ route('client.test-requests.create', ['type' => 'radiology', 'laboratory_id' => $item->id]) }}"
                                                   class="block w-full px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors text-center">
                                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    {{ app()->getLocale() === 'ar' ? 'طلب أشعة' : 'Request Radiology' }}
                                                </a>
                                            @endif
                                        @else
                                            <a href="{{ route('client.medicine-requests.create', ['pharmacy_id' => $item->id]) }}"
                                               class="block w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors text-center">
                                                <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                                </svg>
                                                {{ app()->getLocale() === 'ar' ? 'طلب أدوية' : 'Request Medicines' }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="bg-white rounded-lg shadow p-6 mt-6">
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-gray-500 text-lg">
                                {{ app()->getLocale() === 'ar' ? 'لا توجد نتائج متاحة' : 'No results found' }}
                            </p>
                        </div>
                    </div>
                @endif
            @endif

        @php $isAr = app()->getLocale() === 'ar'; @endphp
        <!-- Recent Requests -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200/90 overflow-hidden">
            <div class="p-5 sm:p-6 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">
                        {{ $isAr ? 'الطلبات الأخيرة' : 'Recent Requests' }}
                    </h3>
                    <p class="text-sm text-gray-500 mt-0.5">{{ $isAr ? 'آخر الطلبات مع التفاصيل والحالة' : 'Latest requests with details and status' }}</p>
                </div>
                <a href="{{ route('client.requests.index') }}"
                   class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium text-primary border border-primary/30 bg-primary/5 hover:bg-primary/10 transition-colors shrink-0">
                    {{ $isAr ? 'عرض الكل' : 'View all' }}
                    <svg class="w-4 h-4 {{ $isAr ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            <div class="p-4 sm:p-6">
                @if($recentRequests->count() > 0)
                    <ul class="space-y-3" role="list">
                        @foreach($recentRequests as $request)
                            @php
                                $typeLabel = match ($request->type) {
                                    'medicine' => $isAr ? 'أدوية' : 'Medicine',
                                    'test' => $isAr ? 'تحاليل' : 'Tests',
                                    'radiology' => $isAr ? 'أشعة' : 'Radiology',
                                    default => $request->type ?? '—',
                                };
                                $cardAccent = match ($request->type) {
                                    'medicine' => 'from-teal-500 to-emerald-600',
                                    'test' => 'from-sky-500 to-blue-600',
                                    'radiology' => 'from-violet-500 to-purple-600',
                                    default => 'from-slate-400 to-slate-600',
                                };
                                $statusClass = match ($request->status) {
                                    'pending' => 'bg-amber-100 text-amber-800 ring-1 ring-amber-200/60',
                                    'approved', 'accepted', 'confirmed' => 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-200/60',
                                    'rejected' => 'bg-red-100 text-red-800 ring-1 ring-red-200/60',
                                    default => 'bg-gray-100 text-gray-800 ring-1 ring-gray-200/60',
                                };
                                $statusLabel = match ($request->status) {
                                    'pending' => $isAr ? 'قيد الانتظار' : 'Pending',
                                    'approved' => $isAr ? 'مقبول' : 'Approved',
                                    'accepted' => $isAr ? 'مقبول' : 'Accepted',
                                    'confirmed' => $isAr ? 'مؤكد' : 'Confirmed',
                                    'rejected' => $isAr ? 'مرفوض' : 'Rejected',
                                    default => ucfirst(str_replace('_', ' ', (string) $request->status)),
                                };
                                $providerName = $request->provider?->name;
                                $when = $request->created_at->timezone(config('app.timezone'));
                            @endphp
                            <li>
                                <a href="{{ route('client.requests.pharmacy-lab.show', $request) }}"
                                   class="group flex flex-col sm:flex-row sm:items-stretch rounded-xl border border-gray-200 bg-gray-50/40 hover:bg-white hover:border-gray-300 hover:shadow-md transition-all duration-200 overflow-hidden focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
                                    <div class="h-1.5 sm:h-auto sm:w-1.5 shrink-0 bg-gradient-to-r sm:bg-gradient-to-b {{ $cardAccent }} sm:min-h-[4.5rem]"></div>
                                    <div class="flex-1 min-w-0 p-4 flex flex-col gap-3">
                                        <div class="flex flex-wrap items-start justify-between gap-2">
                                            <div class="min-w-0 space-y-1">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="text-sm font-bold text-slate-900 tabular-nums">#{{ $request->id }}</span>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-semibold bg-white text-slate-700 border border-gray-200 shadow-sm">{{ $typeLabel }}</span>
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                                                </div>
                                                <p class="text-xs text-gray-500 flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    {{ $when->format($isAr ? 'Y-m-d H:i' : 'M d, Y · g:i A') }}
                                                </p>
                                            </div>
                                            <span class="hidden sm:inline-flex items-center justify-center w-9 h-9 rounded-full border border-gray-200 bg-white text-gray-400 group-hover:text-primary group-hover:border-primary/30 transition-colors shrink-0">
                                                <svg class="w-5 h-5 {{ $isAr ? 'rotate-180' : '' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </span>
                                        </div>
                                        @if($request->lines->isNotEmpty())
                                            <div class="rounded-lg bg-white border border-gray-100 px-3 py-2.5 shadow-sm">
                                                <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                                                    {{ $isAr ? 'محتويات الطلب' : 'Request contents' }}
                                                </p>
                                                <ul class="space-y-1.5 text-xs text-slate-800 leading-snug" role="list">
                                                    @foreach($request->lines as $line)
                                                        @php
                                                            if ($line->item_type === 'medicine') {
                                                                $m = $line->medicine;
                                                                $lineLabel = $m
                                                                    ? ($isAr ? ($m->arabic ?: $m->name) : $m->name)
                                                                    : '—';
                                                            } else {
                                                                $t = $line->medicalTest;
                                                                $lineLabel = $t
                                                                    ? ($isAr ? ($t->test_name_ar ?: $t->test_name_en) : ($t->test_name_en ?: $t->test_name_ar))
                                                                    : '—';
                                                            }
                                                        @endphp
                                                        <li class="flex gap-2 min-w-0">
                                                            <span class="text-primary/70 shrink-0 mt-0.5" aria-hidden="true">•</span>
                                                            <span class="min-w-0 break-words flex-1">
                                                                {{ $lineLabel }}
                                                                @if($line->item_type === 'medicine' && $line->quantity !== null && $line->quantity !== '')
                                                                    <span class="text-gray-500 font-normal"> × {{ $line->quantity }}@if($line->unit) {{ $line->unit }}@endif</span>
                                                                @endif
                                                            </span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                        <dl class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs sm:text-sm">
                                            <div class="flex items-baseline gap-1.5 text-gray-600">
                                                <dt class="font-medium text-gray-500 shrink-0">{{ $isAr ? 'البنود' : 'Items' }}</dt>
                                                <dd class="font-semibold text-slate-800">{{ number_format($request->lines->count()) }}</dd>
                                            </div>
                                            <div class="flex items-baseline gap-1.5 text-gray-600">
                                                <dt class="font-medium text-gray-500 shrink-0">{{ $isAr ? 'العروض' : 'Offers' }}</dt>
                                                <dd class="font-semibold text-slate-800">{{ number_format($request->offers_count) }}</dd>
                                            </div>
                                            <div class="flex items-start gap-1.5 text-gray-600 min-w-0 sm:col-span-1">
                                                <dt class="font-medium text-gray-500 shrink-0">{{ $isAr ? 'المزوّد' : 'Provider' }}</dt>
                                                <dd class="font-medium text-slate-800 truncate" title="{{ $providerName ?? '' }}">{{ $providerName ?: ($isAr ? 'غير محدد' : 'Not set') }}</dd>
                                            </div>
                                        </dl>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-12 px-4 rounded-xl border border-dashed border-gray-200 bg-gray-50/50">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p class="text-gray-500 font-medium">{{ $isAr ? 'لا توجد طلبات بعد' : 'No requests yet' }}</p>
                        <p class="text-sm text-gray-400 mt-1">{{ $isAr ? 'ابدأ بطلب أدوية أو تحاليل من الصفحة الرئيسية' : 'Start with a medicine or lab request from the home page' }}</p>
                    </div>
                @endif
            </div>
        </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@if(app()->getLocale() === 'ar')
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/i18n/ar.min.js"></script>
@endif
<script>
    $(document).ready(function() {
        const isRTL = {{ app()->getLocale() === 'ar' ? 'true' : 'false' }};
        const isArabic = {{ app()->getLocale() === 'ar' ? 'true' : 'false' }};

        // Collapsible filter functionality
        const filterToggle = document.getElementById('filterToggle');
        const filterContent = document.getElementById('filterContent');
        const filterIcon = document.getElementById('filterIcon');
        let isExpanded = !!(filterContent && filterContent.classList.contains('expanded'));

        if (filterToggle && filterContent && filterIcon) {
            filterToggle.addEventListener('click', function() {
                isExpanded = !isExpanded;
                filterToggle.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
                if (isExpanded) {
                    filterContent.classList.remove('collapsed');
                    filterContent.classList.add('expanded');
                    filterIcon.classList.add('rotate-180');
                } else {
                    filterContent.classList.remove('expanded');
                    filterContent.classList.add('collapsed');
                    filterIcon.classList.remove('rotate-180');
                }
            });
        }

        // Initialize Select2
        $('#provider_type').select2({
            placeholder: isArabic ? 'اختر النوع' : 'Select Type',
            allowClear: false,
            width: '100%',
            language: isRTL ? 'ar' : 'en',
            dir: isRTL ? 'rtl' : 'ltr',
            minimumResultsForSearch: Infinity
        });

        $('#governorate_id').select2({
            placeholder: isArabic ? 'اختر المحافظة' : 'Select Governorate',
            allowClear: false,
            width: '100%',
            language: isRTL ? 'ar' : 'en',
            dir: isRTL ? 'rtl' : 'ltr'
        });

        $('#city_id').select2({
            placeholder: isArabic ? 'جميع المدن' : 'All Cities',
            allowClear: true,
            width: '100%',
            language: isRTL ? 'ar' : 'en',
            dir: isRTL ? 'rtl' : 'ltr'
        });

        $('#area_id').select2({
            placeholder: isArabic ? 'جميع المناطق' : 'All Areas',
            allowClear: true,
            width: '100%',
            language: isRTL ? 'ar' : 'en',
            dir: isRTL ? 'rtl' : 'ltr'
        });

        // Update cities when governorate changes
        $('#governorate_id').on('change', function() {
            const governorateId = $(this).val();
            const citySelect = $('#city_id');
            const areaSelect = $('#area_id');

            citySelect.select2('destroy');
            areaSelect.select2('destroy');

            citySelect.empty().append('<option value="">' + (isArabic ? 'جميع المدن' : 'All Cities') + '</option>');
            areaSelect.empty().append('<option value="">' + (isArabic ? 'جميع المناطق' : 'All Areas') + '</option>');

            if (governorateId) {
                fetch(`/api/cities?governorate_id=${governorateId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.data) {
                            data.data.forEach(city => {
                                citySelect.append(new Option(
                                    isArabic ? (city.name_ar || city.name) : (city.name || city.name_ar),
                                    city.id,
                                    false,
                                    false
                                ));
                            });
                        }
                    })
                    .catch(error => console.error('Error:', error))
                    .finally(() => {
                        citySelect.select2({
                            placeholder: isArabic ? 'جميع المدن' : 'All Cities',
                            allowClear: true,
                            width: '100%',
                            language: isRTL ? 'ar' : 'en',
                            dir: isRTL ? 'rtl' : 'ltr'
                        });
                        areaSelect.select2({
                            placeholder: isArabic ? 'جميع المناطق' : 'All Areas',
                            allowClear: true,
                            width: '100%',
                            language: isRTL ? 'ar' : 'en',
                            dir: isRTL ? 'rtl' : 'ltr'
                        });
                    });
            } else {
                citySelect.select2({
                    placeholder: isArabic ? 'جميع المدن' : 'All Cities',
                    allowClear: true,
                    width: '100%',
                    language: isRTL ? 'ar' : 'en',
                    dir: isRTL ? 'rtl' : 'ltr'
                });
                areaSelect.select2({
                    placeholder: isArabic ? 'جميع المناطق' : 'All Areas',
                    allowClear: true,
                    width: '100%',
                    language: isRTL ? 'ar' : 'en',
                    dir: isRTL ? 'rtl' : 'ltr'
                });
            }
        });

        // Update areas when city changes
        $('#city_id').on('change', function() {
            const cityId = $(this).val();
            const areaSelect = $('#area_id');

            areaSelect.select2('destroy');
            areaSelect.empty().append('<option value="">' + (isArabic ? 'جميع المناطق' : 'All Areas') + '</option>');

            if (cityId) {
                fetch(`/api/areas?city_id=${cityId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.data) {
                            data.data.forEach(area => {
                                areaSelect.append(new Option(
                                    isArabic ? (area.name_ar || area.name) : (area.name || area.name_ar),
                                    area.id,
                                    false,
                                    false
                                ));
                            });
                        }
                    })
                    .catch(error => console.error('Error:', error))
                    .finally(() => {
                        areaSelect.select2({
                            placeholder: isArabic ? 'جميع المناطق' : 'All Areas',
                            allowClear: true,
                            width: '100%',
                            language: isRTL ? 'ar' : 'en',
                            dir: isRTL ? 'rtl' : 'ltr'
                        });
                    });
            } else {
                areaSelect.select2({
                    placeholder: isArabic ? 'جميع المناطق' : 'All Areas',
                    allowClear: true,
                    width: '100%',
                    language: isRTL ? 'ar' : 'en',
                    dir: isRTL ? 'rtl' : 'ltr'
                });
            }
        });

        // Initialize map if markers exist
        @if(isset($markers) && count($markers) > 0)
        const mapCenter = {!! json_encode($mapCenter) !!};
        const markers = {!! json_encode($markers) !!};

        const map = L.map('serviceProviderMap').setView([mapCenter.lat, mapCenter.lng], 12);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);

        const mapMarkers = [];
        const iconColors = { laboratory: 'blue', pharmacy: 'green', clinic: '#0d9488' };
        markers.forEach(function(markerData) {
            const iconColor = iconColors[markerData.type] || 'gray';
            const customIcon = L.divIcon({
                className: 'custom-marker',
                html: `<div style="background-color: ${iconColor}; width: 20px; height: 20px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);"></div>`,
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            });

            let typeLabel = isArabic ? 'مختبر' : 'Laboratory';
            if (markerData.type === 'pharmacy') typeLabel = isArabic ? 'صيدلية' : 'Pharmacy';
            if (markerData.type === 'clinic') typeLabel = isArabic ? 'عيادة / طبيب' : 'Clinic / Doctor';

            let popupContent = `
                <div style="min-width: 200px;">
                    <h4 style="font-weight: bold; margin-bottom: 5px;">${markerData.name}</h4>
                    <p style="margin: 2px 0; font-size: 12px;">${typeLabel}</p>
                    ${markerData.doctor_name ? `<p style="margin: 2px 0; font-size: 12px;">${markerData.doctor_name}</p>` : ''}
                    ${markerData.specialization ? `<p style="margin: 2px 0; font-size: 12px; color: #0d9488;">${markerData.specialization}</p>` : ''}
                    ${markerData.phone ? `<p style="margin: 2px 0; font-size: 12px;">📞 ${markerData.phone}</p>` : ''}
                    ${markerData.address ? `<p style="margin: 2px 0; font-size: 12px;">📍 ${markerData.address}</p>` : ''}
                    ${markerData.book_url ? `<a href="${markerData.book_url}" style="display: inline-block; margin-top: 8px; padding: 4px 12px; background: #0d9488; color: white; border-radius: 6px; font-size: 12px; text-decoration: none;">${isArabic ? 'حجز موعد' : 'Book Appointment'}</a>` : ''}
                </div>
            `;

            const marker = L.marker([markerData.lat, markerData.lng], { icon: customIcon })
                .addTo(map)
                .bindPopup(popupContent);

            mapMarkers.push({ marker: marker, data: markerData });
        });

        // Fit map to show all markers
        if (markers.length > 0) {
            const group = new L.featureGroup(mapMarkers.map(m => m.marker));
            map.fitBounds(group.getBounds().pad(0.1));
        }

        // Highlight card when marker is clicked
        mapMarkers.forEach(function(item) {
            item.marker.on('click', function() {
                const card = document.querySelector(`[data-marker-id="${item.data.id}"]`);
                if (card) {
                    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    card.style.border = '2px solid #3b82f6';
                    setTimeout(() => {
                        card.style.border = '';
                    }, 2000);
                }
            });
        });
        @endif
    });
</script>
@endpush
@endsection

