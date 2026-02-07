@extends('pharmacies.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'تعديل ملف الصيدلية' : 'Edit Pharmacy Profile')

@section('page-description', app()->getLocale() === 'ar' ? 'تعديل معلومات الصيدلية' : 'Update pharmacy information')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .card {
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
            background: white;
            border-radius: 0.5rem;
        }
        .card-header {
            background: #0d9488;
            color: white;
            font-weight: 600;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem 0.5rem 0 0;
        }
        .card-body {
            padding: 1.5rem;
        }
        .btn-primary {
            background: #0d9488;
            border: none;
            color: white;
        }
        .btn-primary:hover {
            background: #0f766e;
            color: white;
        }
        
        /* Select2 Custom Styling */
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #0d9488 !important;
            color: white !important;
        }
        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #e0f2fe;
        }
        
        .select2-results__option {
            padding: 8px 12px;
            line-height: 1.5;
        }
        
        .select2-container--default .select2-selection--single {
            height: 38px;
            border: 1px solid #ced4da;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
        
        /* Switch styling */
        .form-check-input:checked {
            background-color: #0d9488 !important;
            border-color: #0d9488 !important;
        }
        .form-check-input:focus {
            border-color: #0d9488;
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(13, 148, 136, 0.25);
        }
        .form-check-input {
            cursor: pointer;
        }
        
        /* Leaflet Map Styling */
        #locationMap {
            height: 400px;
            width: 100%;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            margin-top: 0.5rem;
        }
        
        .map-controls {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            flex-wrap: wrap;
        }
        
        .map-search-container {
            flex: 1;
            min-width: 200px;
        }
        
        .map-search-container .form-control {
            width: 100%;
        }
        
        .search-results {
            position: absolute;
            z-index: 1000;
            width: 100%;
            max-height: 200px;
            overflow-y: auto;
            background: white;
            border: 1px solid #ced4da;
            border-radius: 0.375rem;
            margin-top: 0.25rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .search-results .list-group-item {
            cursor: pointer;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .search-results .list-group-item:hover {
            background-color: #f8f9fa;
        }
        
        .search-results .list-group-item:last-child {
            border-bottom: none;
        }
        
        .map-search-wrapper {
            position: relative;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid py-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('pharmacies.profile.update', $pharmacy) }}" method="POST" id="pharmacy-edit-form" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="is_active" value="0" id="is_active_hidden">

        <!-- Basic Information -->
        <div class="card">
            <div class="card-header">{{ app()->getLocale() === 'ar' ? 'المعلومات الأساسية' : 'Basic Information' }}</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ app()->getLocale() === 'ar' ? 'اسم الصيدلية' : 'Pharmacy Name' }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $pharmacy->name) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ app()->getLocale() === 'ar' ? 'المالك' : 'Owner' }}</label>
                        <select name="user_id" id="user_id" class="form-control select2-owner">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'اختر المالك' : 'Select Owner' }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" data-email="{{ $user->email }}" {{ old('user_id', $pharmacy->user_id) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ app()->getLocale() === 'ar' ? 'الهاتف' : 'Phone' }}</label>
                        <input type="tel" name="phone" class="form-control" value="{{ old('phone', $pharmacy->phone) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email' }}</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $pharmacy->email) }}">
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">{{ app()->getLocale() === 'ar' ? 'العنوان' : 'Address' }}</label>
                        <textarea name="address" class="form-control" rows="2">{{ old('address', $pharmacy->address) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Location Information -->
        <div class="card">
            <div class="card-header">{{ app()->getLocale() === 'ar' ? 'معلومات الموقع' : 'Location Information' }}</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ app()->getLocale() === 'ar' ? 'المنطقة' : 'Area' }}</label>
                        <select name="area_id" id="area_id" class="form-control select2-area">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'اختر المنطقة' : 'Select Area' }}</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}" {{ old('area_id', $pharmacy->area_id) == $area->id ? 'selected' : '' }}>
                                    @if(app()->getLocale() === 'ar')
                                        {{ $area->name_ar }} - {{ $area->city->name_ar ?? '' }} - {{ $area->city->governorate->name_ar ?? '' }}
                                    @else
                                        {{ $area->name }} - {{ $area->city->name ?? '' }} - {{ $area->city->governorate->name ?? '' }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="form-label">{{ app()->getLocale() === 'ar' ? 'اختر الموقع على الخريطة' : 'Select Location on Map' }}</label>
                        <p class="text-muted small">{{ app()->getLocale() === 'ar' ? 'ابحث عن موقع أو انقر على الخريطة لتحديد موقع الصيدلية أو اسحب العلامة لتغيير الموقع' : 'Search for a location, click on the map to set the pharmacy location, or drag the marker to change the location' }}</p>
                        
                        <!-- Map Controls -->
                        <div class="map-controls">
                            <div class="map-search-wrapper map-search-container">
                                <input type="text" id="locationSearch" class="form-control" placeholder="{{ app()->getLocale() === 'ar' ? 'ابحث عن موقع...' : 'Search for a location...' }}" autocomplete="off">
                                <div id="searchResults" class="search-results list-group"></div>
                            </div>
                            <button type="button" id="getCurrentLocation" class="btn btn-primary btn-location">
                                <i class="bi bi-geo-alt-fill"></i> {{ app()->getLocale() === 'ar' ? 'موقعي الحالي' : 'My Current Location' }}
                            </button>
                        </div>
                        
                        <div id="locationMap"></div>
                        <!-- Hidden inputs for lat/lng -->
                        <input type="hidden" name="lat" id="lat" value="{{ old('lat', $pharmacy->lat) }}">
                        <input type="hidden" name="lng" id="lng" value="{{ old('lng', $pharmacy->lng) }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- License Information -->
        <div class="card">
            <div class="card-header">{{ app()->getLocale() === 'ar' ? 'معلومات الترخيص' : 'License Information' }}</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ app()->getLocale() === 'ar' ? 'رقم الترخيص' : 'License Number' }}</label>
                        <input type="text" name="license_number" class="form-control" value="{{ old('license_number', $pharmacy->license_number) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ app()->getLocale() === 'ar' ? 'اسم الصيدلي' : 'Pharmacist Name' }}</label>
                        <input type="text" name="pharmacist_name" class="form-control" value="{{ old('pharmacist_name', $pharmacy->pharmacist_name) }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Operating Hours -->
        <div class="card">
            <div class="card-header">{{ app()->getLocale() === 'ar' ? 'ساعات العمل' : 'Operating Hours' }}</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ app()->getLocale() === 'ar' ? 'وقت الفتح' : 'Opening Time' }}</label>
                        <input type="time" name="opening_time" class="form-control" value="{{ old('opening_time', $pharmacy->opening_time) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ app()->getLocale() === 'ar' ? 'وقت الإغلاق' : 'Closing Time' }}</label>
                        <input type="time" name="closing_time" class="form-control" value="{{ old('closing_time', $pharmacy->closing_time) }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Status -->
        <div class="card">
            <div class="card-header">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</div>
            <div class="card-body">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $pharmacy->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">
                        {{ app()->getLocale() === 'ar' ? 'نشط' : 'Active' }}
                    </label>
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="card">
            <div class="card-header">{{ app()->getLocale() === 'ar' ? 'ملاحظات' : 'Notes' }}</div>
            <div class="card-body">
                <textarea name="notes" class="form-control" rows="4">{{ old('notes', $pharmacy->notes) }}</textarea>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="d-flex justify-content-end gap-2">
            <a href="{{ route('pharmacies.dashboard') }}" class="btn btn-secondary">
                {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
            </a>
            <button type="submit" class="btn btn-success">
                <i class="bi bi-check-circle me-2"></i>
                {{ app()->getLocale() === 'ar' ? 'حفظ التغييرات' : 'Save Changes' }}
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('.select2-owner, .select2-area').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });

        // Handle is_active checkbox
        $('#is_active').on('change', function() {
            $('#is_active_hidden').val(this.checked ? '0' : '0');
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

        // Search functionality using Nominatim Geocoding API
        var searchTimeout;
        var searchResults = $('#searchResults');
        
        $('#locationSearch').on('input', function() {
            var query = $(this).val().trim();
            
            clearTimeout(searchTimeout);
            
            if (query.length < 3) {
                searchResults.hide().empty();
                return;
            }
            
            searchTimeout = setTimeout(function() {
                // Use Nominatim API for geocoding
                $.ajax({
                    url: 'https://nominatim.openstreetmap.org/search',
                    data: {
                        q: query,
                        format: 'json',
                        limit: 5,
                        addressdetails: 1
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        searchResults.html('<div class="list-group-item text-center"><small>{{ app()->getLocale() === "ar" ? "جاري البحث..." : "Searching..." }}</small></div>').show();
                    },
                    success: function(data) {
                        searchResults.empty();
                        
                        if (data.length === 0) {
                            searchResults.html('<div class="list-group-item text-muted text-center"><small>{{ app()->getLocale() === "ar" ? "لا توجد نتائج" : "No results found" }}</small></div>').show();
                            return;
                        }
                        
                        data.forEach(function(item) {
                            var displayName = item.display_name;
                            if (displayName.length > 60) {
                                displayName = displayName.substring(0, 60) + '...';
                            }
                            
                            var listItem = $('<div class="list-group-item"></div>')
                                .html('<small><strong>' + displayName + '</strong></small>')
                                .on('click', function() {
                                    var lat = parseFloat(item.lat);
                                    var lng = parseFloat(item.lon);
                                    updateLocation(lat, lng);
                                    $('#locationSearch').val(item.display_name);
                                    searchResults.hide();
                                });
                            
                            searchResults.append(listItem);
                        });
                        
                        searchResults.show();
                    },
                    error: function() {
                        searchResults.html('<div class="list-group-item text-danger text-center"><small>{{ app()->getLocale() === "ar" ? "حدث خطأ أثناء البحث" : "An error occurred while searching" }}</small></div>').show();
                    }
                });
            }, 500);
        });

        // Get current location
        $('#getCurrentLocation').on('click', function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    updateLocation(position.coords.latitude, position.coords.longitude);
                }, function() {
                    alert('{{ app()->getLocale() === "ar" ? "فشل الحصول على موقعك الحالي" : "Failed to get your current location" }}');
                });
            } else {
                alert('{{ app()->getLocale() === "ar" ? "المتصفح لا يدعم تحديد الموقع" : "Browser does not support geolocation" }}');
            }
        });

        // Hide search results when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.map-search-wrapper').length) {
                searchResults.hide();
            }
        });
    });
</script>
@endpush
