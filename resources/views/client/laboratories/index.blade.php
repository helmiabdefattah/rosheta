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
                            @if($laboratory->logo)
                                <div class="mb-3">
                                    <img src="{{ asset('storage/' . $laboratory->logo) }}" 
                                         alt="{{ $laboratory->name }}" 
                                         class="w-full h-32 object-contain rounded"
                                         onerror="this.style.display='none'">
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

                            {{-- Action Buttons --}}
                            <div class="mt-4 space-y-2">
                                <a href="{{ route('client.laboratories.offers', $laboratory->id) }}" 
                                   class="block w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors text-center">
                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 011 12V7a4 4 0 014-4z"/>
                                    </svg>
                                    {{ app()->getLocale() === 'ar' ? 'عرض العروض' : 'View Offers' }}
                                </a>
                                <button type="button" 
                                        class="w-full px-4 py-2 bg-primary text-white rounded-md hover:bg-teal-700 transition-colors quote-btn"
                                        data-laboratory-id="{{ $laboratory->id }}"
                                        data-laboratory-name="{{ $laboratory->name }}">
                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                    </svg>
                                    {{ app()->getLocale() === 'ar' ? 'إرسال استفسار' : 'Send Quote' }}
                                </button>
                            </div>
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

    {{-- Quote Modal --}}
    <div id="quoteModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold text-gray-800">
                        {{ app()->getLocale() === 'ar' ? 'إرسال استفسار' : 'Send Quote' }}
                    </h3>
                    <button type="button" id="closeQuoteModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="mb-4">
                    <p class="text-sm text-gray-600">
                        <strong>{{ app()->getLocale() === 'ar' ? 'المختبر:' : 'Laboratory:' }}</strong>
                        <span id="modalLaboratoryName" class="text-gray-800"></span>
                    </p>
                </div>

                <form id="quoteForm">
                    <input type="hidden" name="model_type" value="App\Models\Laboratory">
                    <input type="hidden" name="model_id" id="modalLaboratoryId">

                    <div class="mb-4">
                        <label for="quote" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'الاستفسار' : 'Quote' }}
                            <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            name="quote" 
                            id="quote" 
                            rows="6" 
                            class="w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-primary focus:border-primary"
                            placeholder="{{ app()->getLocale() === 'ar' ? 'اكتب استفسارك هنا...' : 'Write your quote here...' }}"
                            required></textarea>
                        <p class="mt-1 text-xs text-gray-500">
                            {{ app()->getLocale() === 'ar' ? 'الحد الأقصى 5000 حرف' : 'Maximum 5000 characters' }}
                        </p>
                    </div>

                    <div id="quoteError" class="mb-4 hidden">
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                            <p id="quoteErrorText"></p>
                        </div>
                    </div>

                    <div id="quoteSuccess" class="mb-4 hidden">
                        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                            <p id="quoteSuccessText"></p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" id="cancelQuoteBtn" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                        </button>
                        <button type="submit" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-teal-700">
                            {{ app()->getLocale() === 'ar' ? 'إرسال' : 'Send' }}
                        </button>
                    </div>
                </form>
            </div>
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

            // Quote Modal functionality
            const quoteModal = document.getElementById('quoteModal');
            const quoteForm = document.getElementById('quoteForm');
            const closeQuoteModal = document.getElementById('closeQuoteModal');
            const cancelQuoteBtn = document.getElementById('cancelQuoteBtn');
            const quoteError = document.getElementById('quoteError');
            const quoteSuccess = document.getElementById('quoteSuccess');
            const quoteErrorText = document.getElementById('quoteErrorText');
            const quoteSuccessText = document.getElementById('quoteSuccessText');

            // Open modal when quote button is clicked
            $(document).on('click', '.quote-btn', function() {
                const laboratoryId = $(this).data('laboratory-id');
                const laboratoryName = $(this).data('laboratory-name');
                
                $('#modalLaboratoryId').val(laboratoryId);
                $('#modalLaboratoryName').text(laboratoryName);
                
                // Reset form
                quoteForm.reset();
                quoteError.classList.add('hidden');
                quoteSuccess.classList.add('hidden');
                
                // Show modal
                quoteModal.classList.remove('hidden');
            });

            // Close modal
            function closeModal() {
                quoteModal.classList.add('hidden');
                quoteForm.reset();
                quoteError.classList.add('hidden');
                quoteSuccess.classList.add('hidden');
            }

            closeQuoteModal.addEventListener('click', closeModal);
            cancelQuoteBtn.addEventListener('click', closeModal);

            // Close modal when clicking outside
            quoteModal.addEventListener('click', function(e) {
                if (e.target === quoteModal) {
                    closeModal();
                }
            });

            // Handle form submission
            quoteForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Hide previous messages
                quoteError.classList.add('hidden');
                quoteSuccess.classList.add('hidden');

                const formData = new FormData(quoteForm);
                const submitBtn = quoteForm.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                
                // Disable submit button
                submitBtn.disabled = true;
                submitBtn.textContent = isArabic ? 'جاري الإرسال...' : 'Sending...';

                fetch('{{ route("client.quotes.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        quoteSuccessText.textContent = data.message;
                        quoteSuccess.classList.remove('hidden');
                        quoteForm.reset();
                        
                        // Close modal after 2 seconds
                        setTimeout(() => {
                            closeModal();
                        }, 2000);
                    } else {
                        quoteErrorText.textContent = data.message || (isArabic ? 'حدث خطأ أثناء الإرسال' : 'An error occurred while sending');
                        quoteError.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    quoteErrorText.textContent = isArabic ? 'حدث خطأ أثناء الإرسال' : 'An error occurred while sending';
                    quoteError.classList.remove('hidden');
                })
                .finally(() => {
                    // Re-enable submit button
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                });
            });
        });
    </script>
    @endpush
@endsection
