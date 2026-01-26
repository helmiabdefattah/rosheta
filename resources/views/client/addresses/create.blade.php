@extends('client.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'إضافة عنوان' : 'Add Address')

@section('page-title', app()->getLocale() === 'ar' ? 'إضافة عنوان جديد' : 'Add New Address')
@section('page-description', app()->getLocale() === 'ar' ? 'أضف عنوان توصيل جديد' : 'Add a new delivery address')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
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
    #locationMap {
        height: 400px;
        width: 100%;
        border-radius: 0.5rem;
        border: 1px solid #d1d5db;
    }
</style>
@endpush

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 lg:p-8">
        <form method="POST" action="{{ route('client.addresses.store') }}">
            @csrf

            <div class="mb-6">
                <label for="city_id" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'المدينة' : 'City' }}
                    <span class="text-red-500">*</span>
                </label>
                <select 
                    id="city_id" 
                    name="city_id" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                >
                    <option value="">{{ app()->getLocale() === 'ar' ? 'اختر المدينة' : 'Select City' }}</option>
                    @foreach($cities as $city)
                        <option value="{{ $city->id }}" {{ old('city_id') == $city->id ? 'selected' : '' }}>
                            {{ app()->getLocale() === 'ar' ? $city->name_ar : $city->name }}
                        </option>
                    @endforeach
                </select>
                @error('city_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="area_id" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'المنطقة' : 'Area' }}
                    <span class="text-red-500">*</span>
                </label>
                <select 
                    id="area_id" 
                    name="area_id" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                >
                    <option value="">{{ app()->getLocale() === 'ar' ? 'اختر المنطقة' : 'Select Area' }}</option>
                </select>
                @error('area_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'العنوان التفصيلي' : 'Detailed Address' }}
                    <span class="text-red-500">*</span>
                </label>
                <textarea 
                    id="address" 
                    name="address" 
                    rows="3"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                    placeholder="{{ app()->getLocale() === 'ar' ? 'أدخل العنوان التفصيلي (الشارع، المبنى، الطابق، الشقة)' : 'Enter detailed address (street, building, floor, apartment)' }}"
                >{{ old('address') }}</textarea>
                @error('address')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'اختر موقعك على الخريطة' : 'Select Your Location on Map' }}
                    <span class="text-gray-500 text-xs">({{ app()->getLocale() === 'ar' ? 'انقر على الخريطة لتحديد موقعك' : 'Click on the map to select your location' }})</span>
                </label>
                <div id="locationMap"></div>
                <p class="mt-2 text-xs text-gray-500">
                    {{ app()->getLocale() === 'ar' ? 'انقر على الخريطة أو اسحب العلامة لتحديد موقعك بدقة' : 'Click on the map or drag the marker to set your exact location' }}
                </p>
                <!-- Hidden inputs for lat and lng -->
                <input type="hidden" id="lat" name="lat" value="{{ old('lat') }}">
                <input type="hidden" id="lng" name="lng" value="{{ old('lng') }}">
                @error('lat')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @error('lng')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                <a href="{{ route('client.addresses.index') }}" class="px-6 py-3 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200">
                    {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                </a>
                <button 
                    type="submit" 
                    class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200 font-medium"
                >
                    <i class="bi bi-check-circle me-2"></i>
                    {{ app()->getLocale() === 'ar' ? 'إضافة العنوان' : 'Add Address' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2 for city
        $('#city_id').select2({
            placeholder: '{{ app()->getLocale() === "ar" ? "اختر المدينة" : "Select City" }}',
            allowClear: true
        });

        // Initialize Select2 for area
        $('#area_id').select2({
            placeholder: '{{ app()->getLocale() === "ar" ? "اختر المنطقة" : "Select Area" }}',
            allowClear: true
        });

        const isRTL = {{ app()->getLocale() === 'ar' ? 'true' : 'false' }};

        // Load areas when city changes
        $('#city_id').on('change', function() {
            const cityId = $(this).val();
            const areaSelect = $('#area_id');
            
            areaSelect.empty().append('<option value="">{{ app()->getLocale() === "ar" ? "اختر المنطقة" : "Select Area" }}</option>');
            
            if (cityId) {
                    $.ajax({
                        url: '/api/areas',
                        method: 'GET',
                        data: { city_id: cityId },
                    success: function(response) {
                        if (response.success && response.data) {
                            response.data.forEach(function(area) {
                                areaSelect.append(
                                    $('<option></option>')
                                        .attr('value', area.id)
                                        .text(isRTL ? (area.name_ar || area.name) : (area.name || area.name_ar))
                                );
                            });
                        }
                    },
                    error: function() {
                        console.error('Failed to load areas');
                    }
                });
            }
        });

        // Initialize Leaflet Map
        var currentLat = parseFloat($('#lat').val()) || 30.0444; // Default to Cairo, Egypt
        var currentLng = parseFloat($('#lng').val()) || 31.2357;
        
        // Initialize map
        var map = L.map('locationMap').setView([currentLat, currentLng], 13);
        
        // Add OpenStreetMap tiles
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);
        
        // Create a marker (draggable)
        var marker = L.marker([currentLat, currentLng], {
            draggable: true
        }).addTo(map);
        
        // Function to update marker and hidden inputs
        function updateLocation(lat, lng) {
            marker.setLatLng([lat, lng]);
            map.setView([lat, lng], 15);
            $('#lat').val(lat.toFixed(8));
            $('#lng').val(lng.toFixed(8));
        }
        
        // Update hidden inputs when marker is dragged
        marker.on('dragend', function(e) {
            var position = marker.getLatLng();
            updateLocation(position.lat, position.lng);
        });
        
        // Update marker position and hidden inputs when map is clicked
        map.on('click', function(e) {
            updateLocation(e.latlng.lat, e.latlng.lng);
        });
        
        // If coordinates exist, center map on them
        if ($('#lat').val() && $('#lng').val()) {
            map.setView([currentLat, currentLng], 15);
        }
    });
</script>
@endpush

