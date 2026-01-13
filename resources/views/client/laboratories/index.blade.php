@extends('client.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'المعامل' : 'Laboratories')
@section('page-title', app()->getLocale() === 'ar' ? 'المعامل' : 'Laboratories')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
</style>
@endpush

@section('content')
    <div class="space-y-6">
        {{-- Filters Section --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                {{ app()->getLocale() === 'ar' ? 'فلترة البحث' : 'Filter Search' }}
            </h3>
            <form method="GET" action="{{ route('client.laboratories.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    {{-- Type Filter --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ app()->getLocale() === 'ar' ? 'النوع' : 'Type' }}
                        </label>
                        <select name="type" id="type" class="w-full border rounded-md p-2">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'جميع الأنواع' : 'All Types' }}</option>
                            <option value="test" @selected(request('type') === 'test')>
                                {{ app()->getLocale() === 'ar' ? 'تحاليل طبية' : 'Medical Tests' }}
                            </option>
                            <option value="radiology" @selected(request('type') === 'radiology')>
                                {{ app()->getLocale() === 'ar' ? 'أشعة' : 'Radiology' }}
                            </option>
                        </select>
                    </div>

                    {{-- Governorate Filter --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ app()->getLocale() === 'ar' ? 'المحافظة' : 'Governorate' }}
                        </label>
                        <select name="governorate_id" id="governorate_id" class="w-full border rounded-md p-2">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'جميع المحافظات' : 'All Governorates' }}</option>
                            @foreach($governorates as $governorate)
                                <option value="{{ $governorate->id }}" @selected(request('governorate_id') == $governorate->id)>
                                    {{ app()->getLocale() === 'ar' ? $governorate->name_ar : $governorate->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- City Filter --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ app()->getLocale() === 'ar' ? 'المدينة' : 'City' }}
                        </label>
                        <select name="city_id" id="city_id" class="w-full border rounded-md p-2">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'جميع المدن' : 'All Cities' }}</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" @selected(request('city_id') == $city->id)>
                                    {{ app()->getLocale() === 'ar' ? $city->name_ar : $city->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Area Filter --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ app()->getLocale() === 'ar' ? 'المنطقة' : 'Area' }}
                        </label>
                        <select name="area_id" id="area_id" class="w-full border rounded-md p-2">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'جميع المناطق' : 'All Areas' }}</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}" @selected(request('area_id') == $area->id)>
                                    {{ app()->getLocale() === 'ar' ? $area->name_ar : $area->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        {{ app()->getLocale() === 'ar' ? 'بحث' : 'Search' }}
                    </button>
                    <a href="{{ route('client.laboratories.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                        {{ app()->getLocale() === 'ar' ? 'إعادة تعيين' : 'Reset' }}
                    </a>
                </div>
            </form>
        </div>

        {{-- Results Section --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                {{ app()->getLocale() === 'ar' ? 'نتائج البحث' : 'Search Results' }}
                <span class="text-sm text-gray-500 font-normal">
                    ({{ $laboratories->total() }} {{ app()->getLocale() === 'ar' ? 'مختبر' : 'laboratory' }}{{ $laboratories->total() !== 1 ? (app()->getLocale() === 'ar' ? 'ات' : 'ies') : '' }})
                </span>
            </h3>

            @if($laboratories->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($laboratories as $laboratory)
                        <div class="border rounded-lg p-4 hover:shadow-lg transition-shadow">
                            {{-- Logo --}}
                            @if($laboratory->getFirstMediaUrl('logo'))
                                <div class="mb-3">
                                    <img src="{{ $laboratory->getFirstMediaUrl('logo', 'thumb') }}" 
                                         alt="{{ $laboratory->name }}" 
                                         class="w-full h-32 object-contain rounded">
                                </div>
                            @endif

                            {{-- Name --}}
                            <h4 class="text-lg font-semibold text-gray-800 mb-2">{{ $laboratory->name }}</h4>

                            {{-- Type Badge --}}
                            <div class="mb-2">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    {{ $laboratory->type === 'test' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                    {{ $laboratory->type === 'test' 
                                        ? (app()->getLocale() === 'ar' ? 'تحاليل طبية' : 'Medical Tests')
                                        : (app()->getLocale() === 'ar' ? 'أشعة' : 'Radiology') }}
                                </span>
                            </div>

                            {{-- Location --}}
                            @if($laboratory->area)
                                <div class="text-sm text-gray-600 mb-2">
                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ app()->getLocale() === 'ar' ? $laboratory->area->name_ar : $laboratory->area->name }}
                                    @if($laboratory->area->city)
                                        , {{ app()->getLocale() === 'ar' ? $laboratory->area->city->name_ar : $laboratory->area->city->name }}
                                    @endif
                                </div>
                            @endif

                            {{-- Contact Info --}}
                            <div class="space-y-1 text-sm text-gray-600">
                                @if($laboratory->phone)
                                    <div>
                                        <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        {{ $laboratory->phone }}
                                    </div>
                                @endif
                                @if($laboratory->email)
                                    <div>
                                        <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        {{ $laboratory->email }}
                                    </div>
                                @endif
                                @if($laboratory->opening_time && $laboratory->closing_time)
                                    <div>
                                        <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ \Carbon\Carbon::parse($laboratory->opening_time)->format('H:i') }} - 
                                        {{ \Carbon\Carbon::parse($laboratory->closing_time)->format('H:i') }}
                                    </div>
                                @endif
                            </div>

                            {{-- Address --}}
                            @if($laboratory->address)
                                <div class="mt-2 text-sm text-gray-500">
                                    {{ Str::limit($laboratory->address, 100) }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-6">
                    {{ $laboratories->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-gray-500 text-lg">
                        {{ app()->getLocale() === 'ar' ? 'لا توجد معامل متاحة' : 'No laboratories found' }}
                    </p>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @if(app()->getLocale() === 'ar')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/i18n/ar.min.js"></script>
    @endif
    <script>
        $(document).ready(function() {
            const isRTL = {{ app()->getLocale() === 'ar' ? 'true' : 'false' }};
            const isArabic = {{ app()->getLocale() === 'ar' ? 'true' : 'false' }};

            // Initialize Select2 for Type filter
            $('#type').select2({
                placeholder: isArabic ? 'جميع الأنواع' : 'All Types',
                allowClear: true,
                width: '100%',
                language: isRTL ? 'ar' : 'en',
                dir: isRTL ? 'rtl' : 'ltr',
                minimumResultsForSearch: Infinity // Disable search for type (only 2 options)
            });

            // Initialize Select2 for Governorate filter
            $('#governorate_id').select2({
                placeholder: isArabic ? 'جميع المحافظات' : 'All Governorates',
                allowClear: true,
                width: '100%',
                language: isRTL ? 'ar' : 'en',
                dir: isRTL ? 'rtl' : 'ltr'
            });

            // Initialize Select2 for City filter
            $('#city_id').select2({
                placeholder: isArabic ? 'جميع المدن' : 'All Cities',
                allowClear: true,
                width: '100%',
                language: isRTL ? 'ar' : 'en',
                dir: isRTL ? 'rtl' : 'ltr'
            });

            // Initialize Select2 for Area filter
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
                
                // Destroy Select2 before updating options
                citySelect.select2('destroy');
                areaSelect.select2('destroy');
                
                // Reset city and area options
                citySelect.empty().append('<option value="">' + (isArabic ? 'جميع المدن' : 'All Cities') + '</option>');
                areaSelect.empty().append('<option value="">' + (isArabic ? 'جميع المناطق' : 'All Areas') + '</option>');

                if (governorateId) {
                    fetch(`/api/cities?governorate_id=${governorateId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.data) {
                                data.data.forEach(city => {
                                    const option = new Option(
                                        isArabic ? (city.name_ar || city.name) : (city.name || city.name_ar),
                                        city.id,
                                        false,
                                        false
                                    );
                                    citySelect.append(option);
                                });
                            }
                        })
                        .catch(error => console.error('Error:', error))
                        .finally(() => {
                            // Reinitialize Select2 after updating options
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
                    // Reinitialize Select2 even if no governorate selected
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
                
                // Destroy Select2 before updating options
                areaSelect.select2('destroy');
                
                // Reset area options
                areaSelect.empty().append('<option value="">' + (isArabic ? 'جميع المناطق' : 'All Areas') + '</option>');

                if (cityId) {
                    fetch(`/api/areas?city_id=${cityId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.data) {
                                data.data.forEach(area => {
                                    const option = new Option(
                                        isArabic ? (area.name_ar || area.name) : (area.name || area.name_ar),
                                        area.id,
                                        false,
                                        false
                                    );
                                    areaSelect.append(option);
                                });
                            }
                        })
                        .catch(error => console.error('Error:', error))
                        .finally(() => {
                            // Reinitialize Select2 after updating options
                            areaSelect.select2({
                                placeholder: isArabic ? 'جميع المناطق' : 'All Areas',
                                allowClear: true,
                                width: '100%',
                                language: isRTL ? 'ar' : 'en',
                                dir: isRTL ? 'rtl' : 'ltr'
                            });
                        });
                } else {
                    // Reinitialize Select2 even if no city selected
                    areaSelect.select2({
                        placeholder: isArabic ? 'جميع المناطق' : 'All Areas',
                        allowClear: true,
                        width: '100%',
                        language: isRTL ? 'ar' : 'en',
                        dir: isRTL ? 'rtl' : 'ltr'
                    });
                }
            });
        });
    </script>
    @endpush
@endsection
