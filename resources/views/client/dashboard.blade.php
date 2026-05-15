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

