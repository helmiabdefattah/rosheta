@extends('client.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'عروض المختبر' : 'Laboratory Offers')

@section('page-title', app()->getLocale() === 'ar' ? 'عروض المختبر' : 'Laboratory Offers')

@section('content')
    <div class="space-y-6">
        {{-- Laboratory Info Header --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-start gap-4">
                @if($laboratory->logo)
                    <img src="{{ asset('storage/' . $laboratory->logo) }}" 
                         alt="{{ $laboratory->name }}" 
                         class="w-16 h-16 object-contain rounded-lg border"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="w-16 h-16 rounded-lg bg-gray-200 flex items-center justify-center text-2xl text-gray-600" style="display: none;">
                        {{ strtoupper(mb_substr($laboratory->name, 0, 1)) }}
                    </div>
                @else
                    <div class="w-16 h-16 rounded-lg bg-gray-200 flex items-center justify-center text-2xl text-gray-600">
                        {{ strtoupper(mb_substr($laboratory->name, 0, 1)) }}
                    </div>
                @endif
                <div class="flex-1">
                    <h2 class="text-2xl font-bold text-slate-800 mb-2">{{ $laboratory->name }}</h2>
                    <div class="flex flex-wrap gap-4 text-sm text-slate-600">
                        @if($laboratory->area)
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>
                                    {{ app()->getLocale() === 'ar' ? $laboratory->area->name_ar : $laboratory->area->name }}
                                    @if($laboratory->area->city)
                                        , {{ app()->getLocale() === 'ar' ? $laboratory->area->city->name_ar : $laboratory->area->city->name }}
                                    @endif
                                </span>
                            </div>
                        @endif
                        @if($laboratory->phone)
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <span>{{ $laboratory->phone }}</span>
                            </div>
                        @endif
                    </div>
                </div>
                <a href="{{ route('client.laboratories.index') }}" 
                   class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    {{ app()->getLocale() === 'ar' ? 'رجوع' : 'Back' }}
                </a>
            </div>
        </div>

        {{-- Offers List --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">
                {{ app()->getLocale() === 'ar' ? 'عروض الفحوصات الطبية' : 'Medical Test Offers' }}
                <span class="text-sm text-gray-500 font-normal">
                    ({{ $offers->total() }} {{ app()->getLocale() === 'ar' ? 'عرض' : 'offer' }}{{ $offers->total() !== 1 ? (app()->getLocale() === 'ar' ? 'ات' : 's') : '' }})
                </span>
            </h3>

            @if($offers->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($offers as $offer)
                        <div class="border rounded-lg p-4 hover:shadow-lg transition-shadow bg-white">
                            {{-- Laboratory Logo --}}
                            <div class="flex items-center gap-3 mb-4">
                                @if($laboratory->logo)
                                    <img src="{{ asset('storage/' . $laboratory->logo) }}" 
                                         alt="{{ $laboratory->name }}" 
                                         class="w-12 h-12 object-contain rounded-lg border"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="w-12 h-12 rounded-lg bg-gray-200 flex items-center justify-center text-lg text-gray-600" style="display: none;">
                                        {{ strtoupper(mb_substr($laboratory->name, 0, 1)) }}
                                    </div>
                                @else
                                    <div class="w-12 h-12 rounded-lg bg-gray-200 flex items-center justify-center text-lg text-gray-600">
                                        {{ strtoupper(mb_substr($laboratory->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="flex-1">
                                    <h4 class="text-sm font-semibold text-slate-800">{{ $laboratory->name }}</h4>
                                </div>
                            </div>

                            {{-- Test Name --}}
                            <div class="mb-4">
                                <h5 class="text-lg font-bold text-slate-800 mb-1">
                                    {{ app()->getLocale() === 'ar' 
                                        ? ($offer->medicalTest->test_name_ar ?? $offer->medicalTest->test_name_en ?? 'N/A')
                                        : ($offer->medicalTest->test_name_en ?? $offer->medicalTest->test_name_ar ?? 'N/A') }}
                                </h5>
                                @if($offer->medicalTest->type)
                                    <span class="text-xs text-slate-500">
                                        {{ app()->getLocale() === 'ar' ? 'النوع:' : 'Type:' }} 
                                        {{ $offer->medicalTest->type }}
                                    </span>
                                @endif
                            </div>

                            {{-- Pricing --}}
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-slate-600">
                                        {{ app()->getLocale() === 'ar' ? 'السعر الأصلي:' : 'Original Price:' }}
                                    </span>
                                    <span class="text-sm text-slate-800 font-medium line-through">
                                        {{ number_format($offer->price, 2) }} 
                                        {{ app()->getLocale() === 'ar' ? 'ج.م' : 'EGP' }}
                                    </span>
                                </div>
                                @if($offer->offer_price)
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-medium text-slate-700">
                                            {{ app()->getLocale() === 'ar' ? 'سعر العرض:' : 'Offer Price:' }}
                                        </span>
                                        <span class="text-lg font-bold text-green-600">
                                            {{ number_format($offer->offer_price, 2) }} 
                                            {{ app()->getLocale() === 'ar' ? 'ج.م' : 'EGP' }}
                                        </span>
                                    </div>
                                @endif
                                @if($offer->discount > 0)
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-slate-600">
                                            {{ app()->getLocale() === 'ar' ? 'الخصم:' : 'Discount:' }}
                                        </span>
                                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                                            {{ number_format($offer->discount, 1) }}%
                                        </span>
                                    </div>
                                @endif
                            </div>

                            {{-- Accept Offer Button --}}
                            <button 
                                type="button"
                                class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors accept-offer-btn"
                                data-offer-id="{{ $offer->id }}"
                                data-laboratory-id="{{ $laboratory->id }}"
                                data-medical-test-id="{{ $offer->medical_test_id }}"
                                data-price="{{ $offer->offer_price ?? $offer->price }}"
                                data-test-name-en="{{ $offer->medicalTest->test_name_en ?? '' }}"
                                data-test-name-ar="{{ $offer->medicalTest->test_name_ar ?? '' }}">
                                <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ app()->getLocale() === 'ar' ? 'أحجز ألان ' : ' make reservation' }}
                            </button>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div class="mt-6">
                    {{ $offers->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 011 12V7a4 4 0 014-4z"/>
                    </svg>
                    <p class="text-gray-500 text-lg">
                        {{ app()->getLocale() === 'ar' ? 'لا توجد عروض متاحة حالياً' : 'No offers available at the moment' }}
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- Accept Offer Modal --}}
    <div id="acceptOfferModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-semibold text-gray-800">
                        {{ app()->getLocale() === 'ar' ? 'قبول العرض' : 'Accept Offer' }}
                    </h3>
                    <button type="button" id="closeAcceptOfferModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form id="acceptOfferForm">
                    <input type="hidden" name="type" value="{{ $laboratory->type }}">
                    <input type="hidden" name="laboratory_id" id="modalLaboratoryId">
                    <input type="hidden" name="medical_test_id" id="modalMedicalTestId">
                    <input type="hidden" name="price" id="modalPrice">

                    {{-- Test Info --}}
                    <div class="mb-4 p-3 bg-slate-50 rounded-lg">
                        <p class="text-sm text-slate-600 mb-1">
                            <strong>{{ app()->getLocale() === 'ar' ? 'الفحص:' : 'Test:' }}</strong>
                            <span id="modalTestName" class="text-slate-800"></span>
                        </p>
                        <p class="text-sm text-slate-600">
                            <strong>{{ app()->getLocale() === 'ar' ? 'السعر:' : 'Price:' }}</strong>
                            <span id="modalPriceDisplay" class="text-green-600 font-semibold"></span>
                        </p>
                    </div>

                    {{-- Quantity (Hidden) --}}
                    <input 
                        type="hidden" 
                        name="quantity" 
                        id="quantity" 
                        value="1">

                    {{-- Home Visit Checkbox --}}
                    <div class="mb-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input 
                                type="checkbox" 
                                name="home_visit" 
                                id="home_visit" 
                                class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
                            <span class="text-sm font-medium text-gray-700">
                                {{ app()->getLocale() === 'ar' ? 'زيارة منزلية' : 'Home Visit' }}
                            </span>
                        </label>
                    </div>

                    {{-- Client Address (Hidden by default, shown when home visit is checked) --}}
                    <div class="mb-4 hidden" id="addressContainer">
                        <label for="client_address_id" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'العنوان' : 'Address' }}
                            <span class="text-red-500">*</span>
                        </label>
                        <select 
                            name="client_address_id" 
                            id="client_address_id" 
                            class="w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-primary focus:border-primary">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'اختر العنوان' : 'Select Address' }}</option>
                            @foreach($addresses as $address)
                                <option value="{{ $address->id }}">
                                    {{ $address->address }}
                                    @if($address->area)
                                        - {{ app()->getLocale() === 'ar' ? $address->area->name_ar : $address->area->name }}
                                    @endif
                                    @if($address->area && $address->area->city)
                                        , {{ app()->getLocale() === 'ar' ? $address->area->city->name_ar : $address->area->city->name }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @if($addresses->isEmpty())
                            <p class="mt-2 text-sm text-amber-600">
                                {{ app()->getLocale() === 'ar' 
                                    ? 'لا توجد عناوين. يرجى إضافة عنوان أولاً.' 
                                    : 'No addresses found. Please add an address first.' }}
                            </p>
                            <a href="{{ route('client.addresses.create') }}" class="mt-2 inline-block text-sm text-primary hover:underline">
                                {{ app()->getLocale() === 'ar' ? 'إضافة عنوان' : 'Add Address' }}
                            </a>
                        @endif
                    </div>

                    {{-- Note --}}
                    <div class="mb-4">
                        <label for="note" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'ملاحظات (اختياري)' : 'Notes (Optional)' }}
                        </label>
                        <textarea 
                            name="note" 
                            id="note" 
                            rows="3" 
                            class="w-full border border-gray-300 rounded-md p-3 focus:ring-2 focus:ring-primary focus:border-primary"
                            placeholder="{{ app()->getLocale() === 'ar' ? 'أضف أي ملاحظات...' : 'Add any notes...' }}"></textarea>
                    </div>

                    <div id="acceptOfferError" class="mb-4 hidden">
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                            <p id="acceptOfferErrorText"></p>
                        </div>
                    </div>

                    <div id="acceptOfferSuccess" class="mb-4 hidden">
                        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                            <p id="acceptOfferSuccessText"></p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button" id="cancelAcceptOfferBtn" class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                            {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                        </button>
                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            {{ app()->getLocale() === 'ar' ? 'قبول العرض' : 'Accept Offer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const isArabic = {{ app()->getLocale() === 'ar' ? 'true' : 'false' }};
            const acceptOfferModal = document.getElementById('acceptOfferModal');
            const acceptOfferForm = document.getElementById('acceptOfferForm');
            const closeAcceptOfferModal = document.getElementById('closeAcceptOfferModal');
            const cancelAcceptOfferBtn = document.getElementById('cancelAcceptOfferBtn');
            const acceptOfferError = document.getElementById('acceptOfferError');
            const acceptOfferSuccess = document.getElementById('acceptOfferSuccess');
            const acceptOfferErrorText = document.getElementById('acceptOfferErrorText');
            const acceptOfferSuccessText = document.getElementById('acceptOfferSuccessText');

            // Open modal when accept offer button is clicked
            $(document).on('click', '.accept-offer-btn', function() {
                const offerId = $(this).data('offer-id');
                const laboratoryId = $(this).data('laboratory-id');
                const medicalTestId = $(this).data('medical-test-id');
                const price = $(this).data('price');
                const testNameEn = $(this).data('test-name-en');
                const testNameAr = $(this).data('test-name-ar');
                
                $('#modalLaboratoryId').val(laboratoryId);
                $('#modalMedicalTestId').val(medicalTestId);
                $('#modalPrice').val(price);
                
                // Set test name based on locale
                const testName = isArabic ? (testNameAr || testNameEn) : (testNameEn || testNameAr);
                $('#modalTestName').text(testName);
                $('#modalPriceDisplay').text(parseFloat(price).toFixed(2) + ' ' + (isArabic ? 'ج.م' : 'EGP'));
                
                // Reset form
                acceptOfferForm.reset();
                $('#quantity').val(1);
                $('#home_visit').prop('checked', false);
                $('#addressContainer').addClass('hidden');
                $('#client_address_id').removeAttr('required');
                acceptOfferError.classList.add('hidden');
                acceptOfferSuccess.classList.add('hidden');
                
                // Show modal
                acceptOfferModal.classList.remove('hidden');
            });

            // Handle home visit checkbox change
            $('#home_visit').on('change', function() {
                const addressContainer = $('#addressContainer');
                const addressSelect = $('#client_address_id');
                
                if ($(this).is(':checked')) {
                    addressContainer.removeClass('hidden');
                    addressSelect.attr('required', 'required');
                } else {
                    addressContainer.addClass('hidden');
                    addressSelect.removeAttr('required');
                    addressSelect.val('');
                }
            });

            // Close modal
            function closeAcceptModal() {
                acceptOfferModal.classList.add('hidden');
                acceptOfferForm.reset();
                $('#quantity').val(1);
                $('#home_visit').prop('checked', false);
                $('#addressContainer').addClass('hidden');
                $('#client_address_id').removeAttr('required');
                acceptOfferError.classList.add('hidden');
                acceptOfferSuccess.classList.add('hidden');
            }

            closeAcceptOfferModal.addEventListener('click', closeAcceptModal);
            cancelAcceptOfferBtn.addEventListener('click', closeAcceptModal);

            // Close modal when clicking outside
            acceptOfferModal.addEventListener('click', function(e) {
                if (e.target === acceptOfferModal) {
                    closeAcceptModal();
                }
            });

            // Handle form submission
            acceptOfferForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Hide previous messages
                acceptOfferError.classList.add('hidden');
                acceptOfferSuccess.classList.add('hidden');

                const formData = new FormData(acceptOfferForm);
                const submitBtn = acceptOfferForm.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                
                // Validate home visit address if checked
                const homeVisit = formData.get('home_visit') === 'on';
                const clientAddressId = formData.get('client_address_id');
                
                if (homeVisit && !clientAddressId) {
                    acceptOfferErrorText.textContent = isArabic ? 'يرجى اختيار العنوان للزيارة المنزلية' : 'Please select an address for home visit';
                    acceptOfferError.classList.remove('hidden');
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                    return;
                }
                
                // Prepare request data
                const requestData = {
                    type: formData.get('type'),
                    laboratory_id: parseInt(formData.get('laboratory_id')),
                    client_address_id: homeVisit ? parseInt(clientAddressId) : null,
                    note: formData.get('note') || null,
                    lines: [{
                        item_type: 'test',
                        medical_test_id: parseInt(formData.get('medical_test_id')),
                        quantity: parseInt(formData.get('quantity')),
                        price: parseFloat(formData.get('price'))
                    }]
                };
                
                // Disable submit button
                submitBtn.disabled = true;
                submitBtn.textContent = isArabic ? 'جاري المعالجة...' : 'Processing...';

                // Get auth token from meta tag or cookie
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                fetch('{{ route("client.offers.direct") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token || ''
                    },
                    body: JSON.stringify(requestData)
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw new Error(data.message || 'Request failed');
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === true || data.success === true) {
                        acceptOfferSuccessText.textContent = data.message || (isArabic ? 'تم قبول العرض بنجاح' : 'Offer accepted successfully');
                        acceptOfferSuccess.classList.remove('hidden');
                        
                        // Redirect to orders page after 2 seconds
                        setTimeout(() => {
                            window.location.href = '{{ route("client.orders.index") }}';
                        }, 2000);
                    } else {
                        acceptOfferErrorText.textContent = data.message || (isArabic ? 'حدث خطأ أثناء قبول العرض' : 'An error occurred while accepting the offer');
                        acceptOfferError.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    acceptOfferErrorText.textContent = isArabic ? 'حدث خطأ أثناء قبول العرض' : 'An error occurred while accepting the offer';
                    acceptOfferError.classList.remove('hidden');
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
