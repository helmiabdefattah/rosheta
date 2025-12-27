@extends('client.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'إنشاء طلب تحاليل' : 'Create Test Request')

@section('page-title', app()->getLocale() === 'ar' ? 'إنشاء طلب تحاليل طبية' : 'Create Medical Test Request')
@section('page-description', app()->getLocale() === 'ar' ? 'أضف فحوصات طبية أو ارفع صور الروشتة' : 'Add medical tests or upload prescription images')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: 42px;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 42px;
        padding-left: 12px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
    }
    .image-preview-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 12px;
    }
    .image-preview-item {
        position: relative;
        aspect-ratio: 1;
        border-radius: 8px;
        overflow: hidden;
        border: 2px solid #e5e7eb;
    }
    .image-preview-item img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .image-preview-item .remove-btn {
        position: absolute;
        top: 4px;
        right: 4px;
        background: rgba(239, 68, 68, 0.9);
        color: white;
        border: none;
        border-radius: 50%;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 14px;
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto">
    <form method="POST" action="{{ route('client.test-requests.store') }}" enctype="multipart/form-data" id="testRequestForm">
        @csrf

        <!-- Home Visit Option -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center gap-3">
                <input 
                    type="checkbox" 
                    id="requires_home_visit" 
                    name="requires_home_visit" 
                    value="1"
                    class="w-5 h-5 text-primary border-gray-300 rounded focus:ring-primary"
                >
                <label for="requires_home_visit" class="text-lg font-semibold text-gray-800 cursor-pointer">
                    {{ app()->getLocale() === 'ar' ? 'أحتاج إلى زيارة منزلية لإجراء التحاليل' : 'I need a home visit for the tests' }}
                </label>
            </div>
            <p class="text-sm text-gray-600 mt-2 ms-8">
                {{ app()->getLocale() === 'ar' 
                    ? 'سيتم طلب عنوان التوصيل في حالة اختيار زيارة منزلية' 
                    : 'Delivery address will be required if home visit is selected' }}
            </p>
        </div>

        <!-- Address Selection (conditional) -->
        <div id="addressSection" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6" style="display: none;">
            <label for="client_address_id" class="block text-sm font-medium text-gray-700 mb-2">
                {{ app()->getLocale() === 'ar' ? 'اختر عنوان التوصيل' : 'Select Delivery Address' }}
                <span class="text-red-500">*</span>
            </label>
            @if($addresses->isEmpty())
                <div class="mb-4 p-4 bg-orange-50 border border-orange-200 rounded-lg">
                    <p class="text-sm text-orange-800 mb-3">
                        {{ app()->getLocale() === 'ar' 
                            ? 'لا توجد عناوين. يرجى إضافة عنوان جديد أولاً.' 
                            : 'No addresses found. Please add a new address first.' }}
                    </p>
                    <a href="{{ route('client.addresses.create') }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-primary text-white rounded-lg hover:bg-teal-700 transition duration-200 text-sm font-medium">
                        <i class="bi bi-plus-circle me-2"></i>
                        {{ app()->getLocale() === 'ar' ? 'إضافة عنوان جديد' : 'Add New Address' }}
                    </a>
                </div>
            @else
                <select 
                    id="client_address_id" 
                    name="client_address_id" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary mb-3"
                >
                    <option value="">{{ app()->getLocale() === 'ar' ? 'اختر عنوانًا' : 'Select an address' }}</option>
                    @foreach($addresses as $address)
                        <option value="{{ $address->id }}">
                            {{ ($address->city->name ?? '') . ', ' . ($address->area->name ?? '') . ' - ' . ($address->address ?? '') }}
                        </option>
                    @endforeach
                </select>
                <div class="text-sm text-gray-600">
                    <a href="{{ route('client.addresses.create') }}" target="_blank" class="text-primary hover:underline inline-flex items-center gap-1">
                        <i class="bi bi-plus-circle"></i>
                        {{ app()->getLocale() === 'ar' ? 'إضافة عنوان جديد' : 'Add New Address' }}
                    </a>
                </div>
            @endif
            @error('client_address_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Medical Conditions -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                {{ app()->getLocale() === 'ar' ? 'الحالات الطبية' : 'Medical Conditions' }}
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input 
                        type="checkbox" 
                        name="pregnant" 
                        value="1"
                        class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary"
                    >
                    <span class="text-sm text-gray-700">{{ app()->getLocale() === 'ar' ? 'حامل' : 'Pregnant' }}</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input 
                        type="checkbox" 
                        name="diabetic" 
                        value="1"
                        class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary"
                    >
                    <span class="text-sm text-gray-700">{{ app()->getLocale() === 'ar' ? 'مريض سكر' : 'Diabetic' }}</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input 
                        type="checkbox" 
                        name="heart_patient" 
                        value="1"
                        class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary"
                    >
                    <span class="text-sm text-gray-700">{{ app()->getLocale() === 'ar' ? 'مريض قلب' : 'Heart Patient' }}</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input 
                        type="checkbox" 
                        name="high_blood_pressure" 
                        value="1"
                        class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary"
                    >
                    <span class="text-sm text-gray-700">{{ app()->getLocale() === 'ar' ? 'ضغط مرتفع' : 'High Blood Pressure' }}</span>
                </label>
            </div>
        </div>

        <!-- Note Field -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <label for="note" class="block text-sm font-medium text-gray-700 mb-2">
                {{ app()->getLocale() === 'ar' ? 'ملاحظات إضافية' : 'Additional Notes' }}
                <span class="text-gray-500 text-xs">({{ app()->getLocale() === 'ar' ? 'اختياري' : 'Optional' }})</span>
            </label>
            <textarea 
                id="note" 
                name="note" 
                rows="3"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                placeholder="{{ app()->getLocale() === 'ar' ? 'أضف أي ملاحظات إضافية...' : 'Add any additional notes...' }}"
            >{{ old('note') }}</textarea>
        </div>

        <!-- Prescription Images -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">
                {{ app()->getLocale() === 'ar' ? 'صور الروشتة الطبية' : 'Prescription Images' }}
                <span class="text-gray-500 text-sm font-normal">({{ app()->getLocale() === 'ar' ? 'اختياري' : 'Optional' }})</span>
            </h3>
            <p class="text-sm text-gray-600 mb-4">
                {{ app()->getLocale() === 'ar' 
                    ? 'رفع صور الروشتة الطبية (اختياري إذا أضفت التحاليل يدويًا)' 
                    : 'Upload prescription images (optional if you add tests manually)' }}
            </p>
            <input 
                type="file" 
                id="images" 
                name="images[]" 
                multiple 
                accept="image/*"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
            >
            <div id="imagePreview" class="image-preview-container mt-4"></div>
            @error('images.*')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- OR Separator -->
        <div class="flex items-center gap-4 mb-6">
            <div class="flex-1 border-t border-gray-300"></div>
            <span class="text-gray-500 font-semibold">{{ app()->getLocale() === 'ar' ? 'أو' : 'OR' }}</span>
            <div class="flex-1 border-t border-gray-300"></div>
        </div>

        <!-- Medical Tests Selection -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">
                    {{ app()->getLocale() === 'ar' ? 'إدخال التحاليل يدويًا' : 'Add Tests Manually' }}
                </h3>
                <button 
                    type="button" 
                    id="addTestBtn"
                    class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-teal-700 transition duration-200 text-sm font-medium"
                >
                    <i class="bi bi-plus-circle me-1"></i>
                    {{ app()->getLocale() === 'ar' ? 'إضافة فحص' : 'Add Test' }}
                </button>
            </div>
            <p class="text-sm text-gray-600 mb-4">
                {{ app()->getLocale() === 'ar' 
                    ? 'ابحث عن التحاليل بالاسم الإنجليزي أو العربي واخترها من القائمة' 
                    : 'Search for tests by English or Arabic name and select from the list' }}
            </p>
            <div id="testsContainer" class="space-y-4">
                <!-- Tests will be added here dynamically -->
            </div>
        </div>

        <!-- Error Message -->
        @error('tests')
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-red-800">{{ $message }}</p>
            </div>
        @enderror
        @error('error')
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <p class="text-sm text-red-800">{{ $message }}</p>
            </div>
        @enderror

        <!-- Submit Button -->
        <div class="flex items-center justify-end gap-4">
            <a href="{{ route('client.dashboard') }}" class="px-6 py-3 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200">
                {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
            </a>
            <button 
                type="submit" 
                class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-teal-700 transition duration-200 font-medium"
            >
                <i class="bi bi-send me-2"></i>
                {{ app()->getLocale() === 'ar' ? 'إرسال الطلب' : 'Submit Request' }}
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    (function($) {
        'use strict';
        
        const medicalTests = @json($medicalTests);
        const locale = '{{ app()->getLocale() }}';
        const isArabic = locale === 'ar';
        let testCounter = 0;
        
        // Wait for DOM to be ready
        $(document).ready(function() {
                    // Toggle address section based on home visit checkbox
            $('#requires_home_visit').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#addressSection').slideDown();
                    const addressSelect = $('#client_address_id');
                    if (addressSelect.length) {
                        addressSelect.prop('required', true);
                    }
                } else {
                    $('#addressSection').slideUp();
                    const addressSelect = $('#client_address_id');
                    if (addressSelect.length) {
                        addressSelect.prop('required', false);
                    }
                }
            });

            // Reload page when returning from address creation (if address was added)
            window.addEventListener('focus', function() {
                // Check if we're returning from address creation
                if (sessionStorage.getItem('addressAdded') === 'true') {
                    sessionStorage.removeItem('addressAdded');
                    location.reload();
                }
            });

            // Mark when user clicks to add address
            $(document).on('click', 'a[href*="addresses/create"]', function() {
                sessionStorage.setItem('addressAdded', 'true');
            });

            // Add test selection field
            $('#addTestBtn').on('click', function() {
                testCounter++;
                const testId = `test_${testCounter}`;
                const testNumber = testCounter;
                const testLabel = isArabic ? 'فحص' : 'Test';
                const selectPlaceholder = isArabic ? 'اختر فحصًا' : 'Select a test';
                const searchPlaceholder = isArabic ? 'ابحث واختر فحصًا' : 'Search and select a test';
                const noResults = isArabic ? 'لا توجد نتائج' : 'No results found';
                
                const testHtml = `
                    <div class="test-item border border-gray-200 rounded-lg p-4 mb-4" data-test-id="${testId}">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-medium text-gray-700">
                                ${testLabel} ${testNumber}
                            </span>
                            <button type="button" class="remove-test-btn text-red-600 hover:text-red-800">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <select class="test-select w-full" name="tests[${testNumber - 1}][test_id]" required>
                            <option value="">${selectPlaceholder}</option>
                        </select>
                    </div>
                `;
                $('#testsContainer').append(testHtml);
                
                // Initialize Select2 for the new select
                const $select = $(`.test-item[data-test-id="${testId}"] .test-select`);
                $select.select2({
                    data: medicalTests.map(test => ({
                        id: test.id,
                        text: `${test.test_name_en || ''}${test.test_name_ar ? ' - ' + test.test_name_ar : ''}`
                    })),
                    placeholder: searchPlaceholder,
                    allowClear: true,
                    language: {
                        noResults: function() {
                            return noResults;
                        }
                    }
                });
            });

            // Remove test field
            $(document).on('click', '.remove-test-btn', function() {
                $(this).closest('.test-item').remove();
            });

            // Image preview
            $('#images').on('change', function(e) {
                const files = e.target.files;
                const preview = $('#imagePreview');
                preview.empty();
                
                Array.from(files).forEach((file, index) => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const imgHtml = `
                                <div class="image-preview-item">
                                    <img src="${e.target.result}" alt="Preview">
                                    <button type="button" class="remove-btn" data-index="${index}">&times;</button>
                                </div>
                            `;
                            preview.append(imgHtml);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            });

            // Remove image from preview and input
            $(document).on('click', '.remove-btn', function() {
                const index = $(this).data('index');
                const dt = new DataTransfer();
                const files = $('#images')[0].files;
                
                Array.from(files).forEach((file, i) => {
                    if (i !== index) {
                        dt.items.add(file);
                    }
                });
                
                $('#images')[0].files = dt.files;
                $(this).closest('.image-preview-item').remove();
            });

            // Form validation
            $('#testRequestForm').on('submit', function(e) {
                const hasTests = $('.test-select').length > 0 && $('.test-select').filter(function() {
                    return $(this).val() !== '' && $(this).val() !== null;
                }).length > 0;
                const hasImages = $('#images')[0].files.length > 0;
                
                if (!hasTests && !hasImages) {
                    e.preventDefault();
                    const message = isArabic 
                        ? 'الرجاء إضافة فحوصات طبية أو رفع صور الروشتة' 
                        : 'Please add medical tests or upload prescription images';
                    alert(message);
                    return false;
                }
                
                if ($('#requires_home_visit').is(':checked')) {
                    const addressSelect = $('#client_address_id');
                    if (addressSelect.length && !addressSelect.val()) {
                        e.preventDefault();
                        const message = isArabic 
                            ? 'الرجاء اختيار عنوان التوصيل' 
                            : 'Please select a delivery address';
                        alert(message);
                        return false;
                    }
                }
            });
        });
    })(jQuery);
</script>
@endpush

