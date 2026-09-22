@php $isAr = app()->getLocale() === 'ar'; @endphp
            @if(request()->has('provider_type'))
                {{-- Map Section (any results that carry lat/lng: clinics, centers, labs, pharmacies) --}}
                @if(isset($markers) && count($markers) > 0)
                    <div class="bg-white rounded-lg shadow p-6 mt-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            {{ $isAr ? 'الخريطة' : 'Map' }}
                            <span class="text-sm text-gray-500 font-normal">
                                ({{ count($markers) }} {{ $isAr ? 'موقع' : 'location' }}{{ count($markers) !== 1 ? 's' : '' }})
                            </span>
                        </h3>
                        <div id="serviceProviderMap"></div>
                    </div>
                @endif

                {{-- Results Cards Section --}}
                @if(isset($results) && count($results) > 0)
                    <div class="bg-white rounded-lg shadow p-6 mt-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">
                            {{ $isAr ? 'نتائج البحث' : 'Search Results' }}
                            <span class="text-sm text-gray-500 font-normal">
                                ({{ count($results) }} {{ $isAr ? 'نتيجة' : 'result' }}{{ count($results) !== 1 ? 's' : '' }})
                            </span>
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($results as $item)
                                @php
                                    // Each item knows its own type (`_ptype`), so a merged
                                    // "all types" result set renders each card correctly.
                                    $ptype = $item->_ptype ?? $providerType;

                                    // A provider's action button shows only when it has an
                                    // active user account. Centers/charity have no single
                                    // action button (their actions live inside the card).
                                    if ($ptype === 'doctor') {
                                        $providerActive = (bool) ($item->doctor?->user?->is_active);
                                    } elseif (in_array($ptype, ['charity', 'medical_center'], true)) {
                                        $providerActive = true;
                                    } else {
                                        $providerActive = (bool) ($item->user?->is_active);
                                    }
                                @endphp
                                <div class="border rounded-lg p-4 hover:shadow-lg transition-shadow" data-marker-id="{{ $ptype . '-' . $item->id }}">
                                    {{-- Logo (doctor profile image for clinics) --}}
                                    @if($ptype === 'doctor' && $item->doctor && $item->doctor->getFirstMediaUrl('profile_image'))
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
                                        @if($ptype === 'doctor')
                                            {{ $item->doctor?->name ?? $item->name }}
                                            @if($item->name && $item->doctor) <span class="text-sm font-normal text-gray-600">— {{ $item->name }}</span> @endif
                                        @elseif($ptype === 'nursing')
                                            {{ $item->user?->name ?? ($isAr ? 'ممرض/ة' : 'Nurse') }}
                                        @else
                                            {{ $item->name }}
                                        @endif
                                    </h4>

                                    {{-- Type Badge --}}
                                    <div class="mb-2">
                                        @php
                                            $badgeClass = match ($ptype) {
                                                'radiology_lab', 'test_lab', 'laboratory' => 'bg-blue-100 text-blue-800',
                                                'pharmacy' => 'bg-green-100 text-green-800',
                                                'doctor' => 'bg-teal-100 text-teal-800',
                                                'medical_center' => 'bg-indigo-100 text-indigo-800',
                                                'nursing' => 'bg-violet-100 text-violet-800',
                                                default => 'bg-rose-100 text-rose-800',
                                            };
                                            $badgeLabel = match ($ptype) {
                                                'radiology_lab' => $isAr ? 'معمل أشعة' : 'Radiology lab',
                                                'test_lab' => $isAr ? 'معمل تحاليل' : 'Test lab',
                                                'laboratory' => $isAr ? 'مختبر' : 'Laboratory',
                                                'pharmacy' => $isAr ? 'صيدلية' : 'Pharmacy',
                                                'doctor' => $isAr ? 'عيادة / طبيب' : 'Clinic / Doctor',
                                                'medical_center' => $isAr ? 'مركز طبي' : 'Medical Center',
                                                'nursing' => $isAr ? 'تمريض منزلي' : 'Home nursing',
                                                default => $isAr ? 'منظمة خيرية' : 'Charitable Organization',
                                            };
                                        @endphp
                                        <span class="px-2 py-1 text-xs rounded-full {{ $badgeClass }}">
                                            {{ $badgeLabel }}
                                        </span>
                                    </div>
                                    @if($ptype === 'doctor' && $item->doctor?->specialization)
                                        <p class="text-sm text-primary font-medium mb-2">{{ $item->doctor->specialization->name }}</p>
                                    @endif

                                    {{-- Location --}}
                                    @if($ptype === 'charity')
                                        @if($item->city || $item->area)
                                            <div class="text-sm text-gray-600 mb-2">
                                                <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                @if($item->area){{ $isAr ? ($item->area->name_ar ?? $item->area->name) : ($item->area->name ?? $item->area->name_ar) }}@endif
                                                @if($item->city)@if($item->area), @endif{{ $isAr ? ($item->city->name_ar ?? $item->city->name) : ($item->city->name ?? $item->city->name_ar) }}@endif
                                            </div>
                                        @endif
                                    @elseif($ptype === 'nursing')
                                        @if($item->address)
                                            <div class="text-sm text-gray-600 mb-2">
                                                <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                {{ Str::limit($item->address, 100) }}
                                            </div>
                                        @endif
                                    @elseif(in_array($ptype, ['doctor', 'medical_center'], true))
                                        @if($item->area || $item->governorate)
                                            <div class="text-sm text-gray-600 mb-2">
                                                <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                @if($item->area){{ $isAr ? ($item->area->name_ar ?? $item->area->name) : ($item->area->name ?? $item->area->name_ar) }}@endif
                                                @if($item->governorate)@if($item->area), @endif{{ $isAr ? ($item->governorate->name_ar ?? $item->governorate->name) : ($item->governorate->name ?? $item->governorate->name_ar) }}@endif
                                            </div>
                                        @endif
                                    @else
                                        @if($item->area)
                                            <div class="text-sm text-gray-600 mb-2">
                                                <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                {{ $isAr ? $item->area->name_ar : $item->area->name }}
                                                @if($item->area->city), {{ $isAr ? $item->area->city->name_ar : $item->area->city->name }}@endif
                                            </div>
                                        @endif
                                    @endif

                                    {{-- Contact Info --}}
                                    <div class="space-y-1 text-sm text-gray-600">
                                        @if($ptype === 'doctor')
                                            @if($item->address)
                                                <div>
                                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                                    {{ Str::limit($item->address, 80) }}
                                                </div>
                                            @endif
                                            @if($item->city)
                                                <div>
                                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                    {{ $isAr ? ($item->city->name_ar ?? $item->city->name) : ($item->city->name ?? $item->city->name_ar) }}
                                                </div>
                                            @endif
                                            @if(((float) ($item->medical_examination_price ?? 0)) > 0)
                                                <div class="text-gray-700 mt-1">
                                                    {{ $isAr ? 'كشف:' : 'Examination:' }} <strong>{{ number_format($item->medical_examination_price, 2) }}</strong>
                                                </div>
                                            @endif
                                        @elseif($ptype === 'medical_center')
                                            @if($item->phone_number)
                                                <div>
                                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                                    {{ $item->phone_number }}
                                                </div>
                                            @endif
                                            @if($item->getOpeningHoursSummary())
                                                <div>
                                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    {{ $item->getOpeningHoursSummary() }}
                                                </div>
                                            @endif
                                            @if($item->address)
                                                <div>
                                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                                    {{ Str::limit($item->address, 80) }}
                                                </div>
                                            @endif
                                        @elseif($ptype === 'charity')
                                            @if($item->phone_numbers && count($item->phone_numbers) > 0)
                                                @foreach($item->phone_numbers as $phone)
                                                    <div>
                                                        <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                                        {{ $phone }}
                                                    </div>
                                                @endforeach
                                            @endif
                                            @if($item->services && count($item->services) > 0)
                                                <div class="mt-2">
                                                    <span class="font-semibold">{{ $isAr ? 'الخدمات:' : 'Services:' }}</span>
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
                                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                                    {{ $item->phone }}
                                                </div>
                                            @endif
                                            @if($item->email)
                                                <div>
                                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                    {{ $item->email }}
                                                </div>
                                            @endif
                                            @if($item->opening_time && $item->closing_time)
                                                <div>
                                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    {{ \Carbon\Carbon::parse($item->opening_time)->format('H:i') }} -
                                                    {{ \Carbon\Carbon::parse($item->closing_time)->format('H:i') }}
                                                </div>
                                            @endif
                                        @endif
                                    </div>

                                    {{-- Clinics inside a medical center (doctor name shown when assigned) --}}
                                    @if($ptype === 'medical_center' && $item->clinics && $item->clinics->count() > 0)
                                        <div class="mt-3 border-t pt-3">
                                            <p class="text-sm font-semibold text-gray-700 mb-2">
                                                {{ $isAr ? 'العيادات داخل المركز' : 'Clinics in this center' }}
                                                <span class="text-gray-400">({{ $item->clinics->count() }})</span>
                                            </p>
                                            <ul class="space-y-2">
                                                @foreach($item->clinics as $clinic)
                                                    <li class="bg-gray-50 rounded-md p-2 text-sm">
                                                        <div class="font-medium text-gray-800">{{ $clinic->name }}</div>
                                                        @if($clinic->doctor)
                                                            <div class="text-gray-600">
                                                                {{ $isAr ? 'د. ' : 'Dr. ' }}{{ $clinic->doctor->name }}
                                                                @if($clinic->doctor->specialization)
                                                                    — <span class="text-primary">{{ $clinic->doctor->specialization->name }}</span>
                                                                @endif
                                                            </div>
                                                            @if($clinic->doctor->user?->is_active)
                                                                <a href="{{ route('client.doctor-reservation.book', $clinic) }}"
                                                                   class="inline-block mt-1 text-xs px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">
                                                                    {{ $isAr ? 'حجز موعد' : 'Book' }}
                                                                </a>
                                                            @endif
                                                        @else
                                                            <div class="text-gray-400 italic">{{ $isAr ? 'بدون طبيب محدد' : 'No doctor assigned' }}</div>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    {{-- Address (labs/pharmacies) --}}
                                    @if(!in_array($ptype, ['doctor', 'nursing', 'medical_center'], true) && $item->address)
                                        <div class="mt-2 text-sm text-gray-500">
                                            {{ Str::limit($item->address, 100) }}
                                        </div>
                                    @endif

                                    {{-- Action Buttons --}}
                                    <div class="mt-4 space-y-2">
                                        @if(in_array($ptype, ['charity', 'medical_center'], true))
                                            {{-- No single action button (info / per-clinic actions above). --}}
                                        @elseif(! $providerActive)
                                            {{-- Inactive / directory-only provider: no action button --}}
                                        @elseif($ptype === 'doctor')
                                            <a href="{{ route('client.doctor-reservation.book', $item) }}"
                                               class="block w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors text-center">
                                                <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                {{ $isAr ? 'حجز موعد' : 'Book Appointment' }}
                                            </a>
                                        @elseif(in_array($ptype, ['radiology_lab', 'test_lab', 'laboratory'], true))
                                            @if($ptype === 'test_lab' || ($ptype === 'laboratory' && in_array($item->type, ['test', 'both'], true)))
                                                <a href="{{ route('client.test-requests.create', ['type' => 'test', 'laboratory_id' => $item->id]) }}"
                                                   class="block w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors text-center mb-2">
                                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    {{ $isAr ? 'طلب تحاليل طبية' : 'Request Medical Tests' }}
                                                </a>
                                            @endif
                                            @if($ptype === 'radiology_lab' || ($ptype === 'laboratory' && in_array($item->type, ['radiology', 'both'], true)))
                                                <a href="{{ route('client.test-requests.create', ['type' => 'radiology', 'laboratory_id' => $item->id]) }}"
                                                   class="block w-full px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors text-center">
                                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    {{ $isAr ? 'طلب أشعة' : 'Request Radiology' }}
                                                </a>
                                            @endif
                                        @elseif($ptype === 'nursing')
                                            <a href="{{ route('client.nurse-requests.create') }}"
                                               class="block w-full px-4 py-2 bg-violet-600 text-white rounded-md hover:bg-violet-700 transition-colors text-center">
                                                <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                {{ $isAr ? 'طلب تمريض منزلي' : 'Request Home Nursing' }}
                                            </a>
                                        @else
                                            <a href="{{ route('client.medicine-requests.create', ['pharmacy_id' => $item->id]) }}"
                                               class="block w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors text-center">
                                                <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                                {{ $isAr ? 'طلب أدوية' : 'Request Medicines' }}
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
                            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-gray-500 text-lg">{{ $isAr ? 'لا توجد نتائج متاحة' : 'No results found' }}</p>
                        </div>
                    </div>
                @endif
            @endif
