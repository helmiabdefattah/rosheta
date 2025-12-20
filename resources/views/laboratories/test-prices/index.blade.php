@extends('laboratories.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'أسعار الفحوصات' : 'Test Prices')

@section('page-description', app()->getLocale() === 'ar' ? 'إدارة أسعار الفحوصات الطبية' : 'Manage medical test prices')

@push('styles')
    <style>
        .price-input {
            width: 120px;
            text-align: right;
        }
        .price-input:focus {
            border-color: #0d9488;
            box-shadow: 0 0 0 0.2rem rgba(13, 148, 136, 0.25);
        }
        .save-indicator {
            display: none;
            color: #0d9488;
            font-size: 0.875rem;
            margin-top: 4px;
        }
        .save-indicator.show {
            display: block;
        }
    </style>
@endpush

@section('content')
    <div class="bg-white rounded-lg shadow-sm mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">{{ app()->getLocale() === 'ar' ? 'أسعار الفحوصات الطبية' : 'Medical Test Prices' }}</h2>
                    <p class="text-sm text-slate-500 mt-1">{{ app()->getLocale() === 'ar' ? 'أدخل أو عدّل سعر كل فحص طبي' : 'Enter or update the price for each medical test' }}</p>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'رقم' : 'ID' }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'اسم الفحص (EN)' : 'Test Name (EN)' }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'اسم الفحص (AR)' : 'Test Name (AR)' }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'السعر (جنيه)' : 'Price (EGP)' }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($medicalTests as $test)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-semibold text-slate-800">#{{ $test->id }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-slate-800">{{ $test->test_name_en }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-slate-800">{{ $test->test_name_ar }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <input 
                                        type="number" 
                                        step="0.01" 
                                        min="0"
                                        class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 price-input" 
                                        data-test-id="{{ $test->id }}"
                                        value="{{ isset($existingPrices[$test->id]) ? number_format($existingPrices[$test->id], 2, '.', '') : '' }}"
                                        placeholder="0.00"
                                    >
                                    <span class="save-indicator" id="save-indicator-{{ $test->id }}">✓ {{ app()->getLocale() === 'ar' ? 'تم الحفظ' : 'Saved' }}</span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let saveTimeouts = {};

            $('.price-input').on('input', function() {
                const $input = $(this);
                const testId = $input.data('test-id');
                const price = $input.val();

                // Clear existing timeout for this input
                if (saveTimeouts[testId]) {
                    clearTimeout(saveTimeouts[testId]);
                }

                // Hide save indicator
                $('#save-indicator-' + testId).removeClass('show');

                // Set new timeout to save after 1 second of no typing
                saveTimeouts[testId] = setTimeout(function() {
                    if (price === '' || price === null) {
                        return; // Don't save empty values
                    }

                    // Show loading state
                    $input.prop('disabled', true);
                    $input.css('opacity', '0.6');

                    $.ajax({
                        url: '{{ route("laboratories.test-prices.store-or-update") }}',
                        method: 'POST',
                        data: {
                            medical_test_id: testId,
                            price: price
                        },
                        success: function(response) {
                            if (response.success) {
                                // Show save indicator
                                $('#save-indicator-' + testId).addClass('show');
                                
                                // Hide indicator after 2 seconds
                                setTimeout(function() {
                                    $('#save-indicator-' + testId).removeClass('show');
                                }, 2000);
                            }
                        },
                        error: function(xhr) {
                            alert(xhr.responseJSON?.message || '{{ app()->getLocale() === "ar" ? "حدث خطأ أثناء الحفظ" : "An error occurred while saving" }}');
                        },
                        complete: function() {
                            $input.prop('disabled', false);
                            $input.css('opacity', '1');
                        }
                    });
                }, 1000); // Wait 1 second after user stops typing
            });
        });
    </script>
@endpush

