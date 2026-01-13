@extends('nurse.layouts.dashboard')

@section('content')
    <div class="space-y-4">
        @forelse($requests as $request)
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                {{-- Header --}}
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold">{{ $request->getTranslatedServiceType() }}</h3>
                            <p class="text-sm text-blue-100 mt-1">Request #{{ $request->id }}</p>
                        </div>
                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-white text-blue-600 uppercase">
                            {{ ucfirst($request->status) }}
                        </span>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Request Details Section --}}
                        <div class="space-y-4">
                            <h4 class="text-lg font-semibold text-gray-800 border-b pb-2">
                                {{ app()->getLocale() === 'ar' ? 'تفاصيل الطلب' : 'Request Details' }}
                            </h4>
                            
                            <div class="space-y-3">
                                <div class="flex items-start">
                                    <span class="text-sm font-medium text-gray-600 w-32 flex-shrink-0">
                                        {{ app()->getLocale() === 'ar' ? 'نوع الخدمة' : 'Service Type' }}:
                                    </span>
                                    <span class="text-sm text-gray-800">{{ $request->getTranslatedServiceType() }}</span>
                                </div>

                                <div class="flex items-start">
                                    <span class="text-sm font-medium text-gray-600 w-32 flex-shrink-0">
                                        {{ app()->getLocale() === 'ar' ? 'عدد الزيارات' : 'Visits Count' }}:
                                    </span>
                                    <span class="text-sm text-gray-800">{{ $request->visits_count }}</span>
                                </div>

                                <div class="flex items-start">
                                    <span class="text-sm font-medium text-gray-600 w-32 flex-shrink-0">
                                        {{ app()->getLocale() === 'ar' ? 'تكرار الزيارات' : 'Frequency' }}:
                                    </span>
                                    <span class="text-sm text-gray-800">
                                        @if($request->visit_frequency === 'custom' && !empty($request->custom_visit_days))
                                            @php
                                                $days = [
                                                    0 => ['en' => 'Sunday', 'ar' => 'الأحد'],
                                                    1 => ['en' => 'Monday', 'ar' => 'الإثنين'],
                                                    2 => ['en' => 'Tuesday', 'ar' => 'الثلاثاء'],
                                                    3 => ['en' => 'Wednesday', 'ar' => 'الأربعاء'],
                                                    4 => ['en' => 'Thursday', 'ar' => 'الخميس'],
                                                    5 => ['en' => 'Friday', 'ar' => 'الجمعة'],
                                                    6 => ['en' => 'Saturday', 'ar' => 'السبت'],
                                                ];
                                                $selectedDays = array_map('intval', $request->custom_visit_days);
                                                sort($selectedDays);
                                                $dayNames = array_map(function($dayNum) use ($days) {
                                                    return app()->getLocale() === 'ar' ? $days[$dayNum]['ar'] : $days[$dayNum]['en'];
                                                }, $selectedDays);
                                            @endphp
                                            <span class="font-semibold">{{ app()->getLocale() === 'ar' ? 'أيام محددة' : 'Custom' }}:</span>
                                            <span class="ml-1">{{ implode(', ', $dayNames) }}</span>
                                        @elseif($request->visit_frequency)
                                            @php
                                                $frequencyMap = [
                                                    'daily' => app()->getLocale() === 'ar' ? 'يومياً' : 'Daily',
                                                    'every_two_days' => app()->getLocale() === 'ar' ? 'كل يومين' : 'Every 2 days',
                                                    'weekly' => app()->getLocale() === 'ar' ? 'أسبوعياً' : 'Weekly',
                                                ];
                                            @endphp
                                            {{ $frequencyMap[$request->visit_frequency] ?? ucfirst(str_replace('_', ' ', $request->visit_frequency)) }}
                                        @else
                                            {{ app()->getLocale() === 'ar' ? 'زيارة واحدة' : 'Single visit' }}
                                        @endif
                                    </span>
                                </div>

                                <div class="flex items-start">
                                    <span class="text-sm font-medium text-gray-600 w-32 flex-shrink-0">
                                        {{ app()->getLocale() === 'ar' ? 'تاريخ البدء' : 'Start Date' }}:
                                    </span>
                                    <span class="text-sm text-gray-800">
                                        {{ $request->visit_start_date->format('Y-m-d') }}
                                    </span>
                                </div>

                                <div class="flex items-start">
                                    <span class="text-sm font-medium text-gray-600 w-32 flex-shrink-0">
                                        {{ app()->getLocale() === 'ar' ? 'الوقت' : 'Time' }}:
                                    </span>
                                    <span class="text-sm text-gray-800">{{ $request->visit_time }}</span>
                                </div>

                                @if($request->preferred_gender)
                                <div class="flex items-start">
                                    <span class="text-sm font-medium text-gray-600 w-32 flex-shrink-0">
                                        {{ app()->getLocale() === 'ar' ? 'تفضيل النوع' : 'Preferred Gender' }}:
                                    </span>
                                    <span class="text-sm text-gray-800 capitalize">{{ $request->preferred_gender }}</span>
                                </div>
                                @endif

                                @if($request->patient_age)
                                <div class="flex items-start">
                                    <span class="text-sm font-medium text-gray-600 w-32 flex-shrink-0">
                                        {{ app()->getLocale() === 'ar' ? 'عمر المريض' : 'Patient Age' }}:
                                    </span>
                                    <span class="text-sm text-gray-800">{{ $request->patient_age }} {{ app()->getLocale() === 'ar' ? 'سنة' : 'years' }}</span>
                                </div>
                                @endif

                                @if($request->medical_condition)
                                <div class="flex items-start">
                                    <span class="text-sm font-medium text-gray-600 w-32 flex-shrink-0">
                                        {{ app()->getLocale() === 'ar' ? 'الحالة الطبية' : 'Medical Condition' }}:
                                    </span>
                                    <span class="text-sm text-gray-800">{{ $request->medical_condition }}</span>
                                </div>
                                @endif

                                @if($request->medical_notes)
                                <div class="flex items-start">
                                    <span class="text-sm font-medium text-gray-600 w-32 flex-shrink-0">
                                        {{ app()->getLocale() === 'ar' ? 'ملاحظات طبية' : 'Medical Notes' }}:
                                    </span>
                                    <span class="text-sm text-gray-800 flex-1">{{ $request->medical_notes }}</span>
                                </div>
                                @endif

                                @if($request->needs_overnight)
                                <div class="flex items-start">
                                    <span class="text-sm font-medium text-gray-600 w-32 flex-shrink-0">
                                        {{ app()->getLocale() === 'ar' ? 'يتطلب مبيت' : 'Overnight Stay' }}:
                                    </span>
                                    <span class="text-sm text-gray-800">
                                        {{ app()->getLocale() === 'ar' ? 'نعم' : 'Yes' }}
                                        @if($request->overnight_days)
                                            ({{ $request->overnight_days }} {{ app()->getLocale() === 'ar' ? 'أيام' : 'days' }})
                                        @endif
                                    </span>
                                </div>
                                @endif

                                @if($request->total_price)
                                <div class="flex items-start">
                                    <span class="text-sm font-medium text-gray-600 w-32 flex-shrink-0">
                                        {{ app()->getLocale() === 'ar' ? 'الميزانية' : 'Budget' }}:
                                    </span>
                                    <span class="text-sm text-gray-800 font-semibold">
                                        {{ number_format($request->total_price, 2) }} {{ app()->getLocale() === 'ar' ? 'ج.م' : 'EGP' }}
                                    </span>
                                </div>
                                @endif

                                @if($request->address)
                                <div class="flex items-start">
                                    <span class="text-sm font-medium text-gray-600 w-32 flex-shrink-0">
                                        {{ app()->getLocale() === 'ar' ? 'العنوان' : 'Address' }}:
                                    </span>
                                    <span class="text-sm text-gray-800 flex-1">
                                        {{ $request->address->address }}
                                        @if($request->address->area)
                                            - {{ $request->address->area->name }}
                                            @if($request->address->area->city)
                                                - {{ $request->address->area->city->name }}
                                                @if($request->address->area->city->governorate)
                                                    - {{ $request->address->area->city->governorate->name }}
                                                @endif
                                            @endif
                                        @endif
                                    </span>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Client Information Section --}}
                        <div class="space-y-4">
                            <h4 class="text-lg font-semibold text-gray-800 border-b pb-2">
                                {{ app()->getLocale() === 'ar' ? 'معلومات العميل' : 'Client Information' }}
                            </h4>
                            
                            <div class="space-y-3">
                                <div class="flex items-start">
                                    <span class="text-sm font-medium text-gray-600 w-32 flex-shrink-0">
                                        {{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}:
                                    </span>
                                    <span class="text-sm text-gray-800 font-semibold">{{ $request->client->name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Action Button --}}
                    <div class="mt-6 pt-6 border-t flex justify-end">
                        <a href="{{ route('nurse.offers.create', ['request_id' => $request->id]) }}"
                           class="px-6 py-2 text-sm font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors shadow-md">
                            {{ app()->getLocale() === 'ar' ? 'تقديم عرض' : 'Make Offer' }}
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-10 text-center">
                <p class="text-gray-500 text-lg">
                    {{ app()->getLocale() === 'ar' ? 'لا توجد طلبات متاحة حالياً' : 'No requests available at the moment' }}
                </p>
            </div>
        @endforelse

        <div class="pt-4">
            {{ $requests->links() }}
        </div>
    </div>
@endsection
