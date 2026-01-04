@extends('nurse.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'لوحة تحكم الممرض' : 'Nurse Dashboard')
@section('page-title', app()->getLocale() === 'ar' ? 'لوحة تحكم الممرض' : 'Nurse Dashboard')

@section('content')
    <!-- Tabs Navigation -->
    <div class="mb-6 border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
            <button id="profile-tab" data-tab="profile" class="tab-button py-2 px-1 border-b-2 border-primary text-sm font-medium text-primary">
                {{ app()->getLocale() === 'ar' ? 'الملف الشخصي' : 'Profile' }}
            </button>
            <button id="offers-tab" data-tab="offers" class="tab-button py-2 px-1 border-b-2 border-transparent text-sm font-medium text-gray-500 hover:text-gray-700">
                {{ app()->getLocale() === 'ar' ? 'العروض' : 'Offers' }}
            </button>
            <button id="visits-tab" data-tab="visits" class="tab-button py-2 px-1 border-b-2 border-transparent text-sm font-medium text-gray-500 hover:text-gray-700">
                {{ app()->getLocale() === 'ar' ? 'الزيارات' : 'Visits' }}
            </button>
        </nav>
    </div>

    <!-- Profile Tab Content -->
    <div id="profile-content" class="tab-content">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <!-- Nurse Header -->
            <div class="px-6 py-4 bg-gray-50 border-b">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        @if($nurse->client?->avatar)
                            <img src="{{ Storage::url($nurse->client->avatar) }}" alt="avatar" class="h-16 w-16 rounded-full object-cover border-2 border-white shadow">
                        @else
                            <div class="h-16 w-16 rounded-full bg-gray-200 flex items-center justify-center text-xl font-bold text-gray-600 border-2 border-white shadow">
                                {{ strtoupper(mb_substr($nurse->client->name ?? 'N', 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800">{{ $nurse->client->name ?? '-' }}</h2>
                            <div class="flex items-center space-x-3 mt-1">
                                <span class="text-sm text-gray-600">{{ $nurse->client->phone_number ?? '-' }}</span>
                                <span class="text-sm text-gray-600">•</span>
                                <span class="text-sm text-gray-600">{{ $nurse->client->email ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        @if($nurse->status === 'active')
                            <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                                {{ app()->getLocale() === 'ar' ? 'نشط' : 'Active' }}
                            </span>
                        @else
                            <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                                {{ app()->getLocale() === 'ar' ? 'غير نشط' : 'Inactive' }}
                            </span>
                        @endif
                        <a href="{{ route('admin.nurses.edit', $nurse) }}" class="px-3 py-1 bg-blue-600 text-white rounded-md text-sm">
                            {{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Nurse Details -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Basic Information -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-700 border-b pb-2">
                            {{ app()->getLocale() === 'ar' ? 'المعلومات الأساسية' : 'Basic Information' }}
                        </h3>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">{{ app()->getLocale() === 'ar' ? 'المؤهل' : 'Qualification' }}</p>
                                <p class="text-sm font-medium text-gray-800">{{ ucfirst(str_replace('_',' ',$nurse->qualification ?? '-')) }}</p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">{{ app()->getLocale() === 'ar' ? 'الجنس' : 'Gender' }}</p>
                                <p class="text-sm font-medium text-gray-800">{{ $nurse->gender ? ucfirst($nurse->gender) : '-' }}</p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">{{ app()->getLocale() === 'ar' ? 'تاريخ الميلاد' : 'Date of Birth' }}</p>
                                <p class="text-sm font-medium text-gray-800">{{ $nurse->date_of_birth ? $nurse->date_of_birth->format('Y-m-d') : '-' }}</p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-500">{{ app()->getLocale() === 'ar' ? 'سنوات الخبرة' : 'Years of Experience' }}</p>
                                <p class="text-sm font-medium text-gray-800">{{ $nurse->years_of_experience ?? '-' }}</p>
                            </div>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">{{ app()->getLocale() === 'ar' ? 'مكان التعليم' : 'Education Place' }}</p>
                            <p class="text-sm font-medium text-gray-800">{{ $nurse->education_place ?? '-' }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">{{ app()->getLocale() === 'ar' ? 'سنة التخرج' : 'Graduation Year' }}</p>
                            <p class="text-sm font-medium text-gray-800">{{ $nurse->graduation_year ?? '-' }}</p>
                        </div>

                        <div>
                            <p class="text-sm text-gray-500">{{ app()->getLocale() === 'ar' ? 'مكان العمل الحالي' : 'Current Workplace' }}</p>
                            <p class="text-sm font-medium text-gray-800">{{ $nurse->current_workplace ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Areas and Additional Info -->
                    <div class="space-y-4">
                        <!-- Areas -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-700 border-b pb-2">
                                {{ app()->getLocale() === 'ar' ? 'المناطق التي يغطيها' : 'Coverage Areas' }}
                            </h3>
                            @php
                                $ids = is_array($nurse->area_ids) ? $nurse->area_ids : [];
                                $labels = collect($ids)->map(function($id) use ($areaMap) {
                                    $area = $areaMap[$id] ?? null;
                                    if (!$area) return null;
                                    $city = $area->city->name ?? '';
                                    $gov = $area->city->governorate->name ?? '';
                                    return trim($area->name . ($city ? ' - '.$city : '') . ($gov ? ' ('.$gov.')' : ''));
                                })->filter()->values();
                            @endphp
                            @if($labels->isEmpty())
                                <p class="text-sm text-gray-500 mt-2">{{ app()->getLocale() === 'ar' ? 'لا توجد مناطق محددة' : 'No areas specified' }}</p>
                            @else
                                <div class="flex flex-wrap gap-2 mt-2">
                                    @foreach($labels as $label)
                                        <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-800 text-xs">{{ $label }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Address -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-700 border-b pb-2">
                                {{ app()->getLocale() === 'ar' ? 'العنوان' : 'Address' }}
                            </h3>
                            <p class="text-sm text-gray-800 mt-2">{{ $nurse->address ?? '-' }}</p>
                        </div>

                        <!-- Skills -->
                        @if($nurse->skills && is_array($nurse->skills) && count($nurse->skills) > 0)
                            <div>
                                <h3 class="text-lg font-medium text-gray-700 border-b pb-2">
                                    {{ app()->getLocale() === 'ar' ? 'المهارات' : 'Skills' }}
                                </h3>
                                <div class="flex flex-wrap gap-2 mt-2">
                                    @foreach($nurse->skills as $skill)
                                        <span class="px-3 py-1 rounded-full bg-green-100 text-green-800 text-xs">{{ $skill }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Certifications -->
                        @if($nurse->certifications && is_array($nurse->certifications) && count($nurse->certifications) > 0)
                            <div>
                                <h3 class="text-lg font-medium text-gray-700 border-b pb-2">
                                    {{ app()->getLocale() === 'ar' ? 'الشهادات' : 'Certifications' }}
                                </h3>
                                <div class="flex flex-wrap gap-2 mt-2">
                                    @foreach($nurse->certifications as $certification)
                                        <span class="px-3 py-1 rounded-full bg-purple-100 text-purple-800 text-xs">{{ $certification }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Notes -->
                @if($nurse->notes)
                    <div class="mt-6 pt-6 border-t">
                        <h3 class="text-lg font-medium text-gray-700 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'ملاحظات إضافية' : 'Additional Notes' }}
                        </h3>
                        <p class="text-sm text-gray-600 bg-gray-50 p-4 rounded">{{ $nurse->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Offers Tab Content -->
    <div id="offers-content" class="tab-content hidden">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
            @if($offers->count() > 0)
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'طلب التمريض المنزلي' : 'Home Nurse Request' }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'السعر' : 'Price' }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'الملاحظات' : 'Notes' }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'تاريخ الإنشاء' : 'Created At' }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'إجراءات' : 'Actions' }}</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($offers as $offer)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $offer->id }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">
{{--                                <a href="{{ route('admin.home-nurse-requests.show', $offer->home_nurse_request_id) }}" class="text-blue-600 hover:underline">--}}
{{--                                    {{ app()->getLocale() === 'ar' ? 'طلب' : 'Request' }} #{{ $offer->home_nurse_request_id }}--}}
{{--                                </a>--}}
                                @if($offer->request && $offer->request->client)
                                    <p class="text-xs text-gray-500 mt-1">{{ $offer->request->client->name }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ number_format($offer->price, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                @if($offer->notes)
                                    <span class="truncate max-w-xs inline-block" title="{{ $offer->notes }}">
                                    {{ \Illuminate\Support\Str::limit($offer->notes, 30) }}
                                </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                @if($offer->status === 'accepted')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">{{ app()->getLocale() === 'ar' ? 'مقبول' : 'Accepted' }}</span>
                                @elseif($offer->status === 'rejected')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">{{ app()->getLocale() === 'ar' ? 'مرفوض' : 'Rejected' }}</span>
                                @elseif($offer->status === 'pending')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">{{ app()->getLocale() === 'ar' ? 'قيد الانتظار' : 'Pending' }}</span>
                                @elseif($offer->status === 'cancelled')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ app()->getLocale() === 'ar' ? 'ملغى' : 'Cancelled' }}</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($offer->status) }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $offer->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3 text-sm">
{{--                                <a href="{{ route('admin.nurse-offers.show', $offer) }}" class="px-3 py-1 bg-gray-200 text-slate-800 rounded-md text-xs">--}}
{{--                                    {{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}--}}
{{--                                </a>--}}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div class="px-4 py-3 border-t">
                    {{ $offers->links() }}
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <div class="text-gray-400 mb-2">
                        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-700 mb-1">
                        {{ app()->getLocale() === 'ar' ? 'لا توجد عروض' : 'No Offers Found' }}
                    </h3>
                    <p class="text-sm text-gray-500">
                        {{ app()->getLocale() === 'ar' ? 'لم يقم هذا الممرض بتقديم أي عروض بعد.' : 'This nurse has not made any offers yet.' }}
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Visits Tab Content -->
    <div id="visits-content" class="tab-content hidden">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
            @if($visits->count() > 0)
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'طلب التمريض المنزلي' : 'Home Nurse Request' }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'تاريخ الزيارة' : 'Visit Date' }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'ملاحظات الزيارة' : 'Visit Notes' }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'حالة الزيارة' : 'Visit Status' }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'تاريخ الإنشاء' : 'Created At' }}</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'إجراءات' : 'Actions' }}</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($visits as $visit)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $visit->id }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">
{{--                                <a href="{{ route('admin.home-nurse-requests.show', $visit->home_nurse_request_id) }}" class="text-blue-600 hover:underline">--}}
{{--                                    {{ app()->getLocale() === 'ar' ? 'طلب' : 'Request' }} #{{ $visit->home_nurse_request_id }}--}}
{{--                                </a>--}}
                                @if($visit->request && $visit->request->client)
                                    <p class="text-xs text-gray-500 mt-1">{{ $visit->request->client->name }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                @if($visit->visit_datetime)
                                    <div class="font-medium">{{ $visit->visit_datetime->format('Y-m-d') }}</div>
                                    <div class="text-xs text-gray-500">{{ $visit->visit_datetime->format('H:i') }}</div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                @if($visit->notes)
                                    <span class="truncate max-w-xs inline-block" title="{{ $visit->notes }}">
                                    {{ \Illuminate\Support\Str::limit($visit->notes, 30) }}
                                </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                @if($visit->status === 'completed')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">{{ app()->getLocale() === 'ar' ? 'مكتمل' : 'Completed' }}</span>
                                @elseif($visit->status === 'scheduled')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">{{ app()->getLocale() === 'ar' ? 'مجدول' : 'Scheduled' }}</span>
                                @elseif($visit->status === 'cancelled')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">{{ app()->getLocale() === 'ar' ? 'ملغى' : 'Cancelled' }}</span>
                                @elseif($visit->status === 'pending')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">{{ app()->getLocale() === 'ar' ? 'قيد الانتظار' : 'Pending' }}</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($visit->status) }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $visit->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3 text-sm">
{{--                                <a href="{{ route('admin.nurse-visits.show', $visit) }}" class="px-3 py-1 bg-gray-200 text-slate-800 rounded-md text-xs">--}}
{{--                                    {{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}--}}
{{--                                </a>--}}
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div class="px-4 py-3 border-t">
                    {{ $visits->links() }}
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <div class="text-gray-400 mb-2">
                        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-medium text-gray-700 mb-1">
                        {{ app()->getLocale() === 'ar' ? 'لا توجد زيارات' : 'No Visits Found' }}
                    </h3>
                    <p class="text-sm text-gray-500">
                        {{ app()->getLocale() === 'ar' ? 'لم يقم هذا الممرض بأي زيارات بعد.' : 'This nurse has not made any visits yet.' }}
                    </p>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const tabs = document.querySelectorAll('.tab-button');
                const tabContents = document.querySelectorAll('.tab-content');

                function activateTab(tabName) {
                    // Update URL hash
                    window.location.hash = tabName;

                    // Update active tab button
                    tabs.forEach(tab => {
                        if (tab.dataset.tab === tabName) {
                            tab.classList.remove('border-transparent', 'text-gray-500');
                            tab.classList.add('border-primary', 'text-primary');
                        } else {
                            tab.classList.remove('border-primary', 'text-primary');
                            tab.classList.add('border-transparent', 'text-gray-500');
                        }
                    });

                    // Show active tab content
                    tabContents.forEach(content => {
                        if (content.id === tabName + '-content') {
                            content.classList.remove('hidden');
                        } else {
                            content.classList.add('hidden');
                        }
                    });
                }

                // Initialize from URL hash or default to profile
                const initialTab = window.location.hash.substring(1) || 'Offers';
                activateTab(initialTab);

                // Add click event listeners
                tabs.forEach(tab => {
                    tab.addEventListener('click', () => {
                        activateTab(tab.dataset.tab);
                    });
                });
            });
        </script>
        <style>
            .tab-button {
                transition: all 0.2s ease-in-out;
                cursor: pointer;
            }

            .tab-button:hover:not(.border-primary) {
                border-color: #d1d5db;
                color: #374151;
            }
        </style>
    @endpush
@endsection
