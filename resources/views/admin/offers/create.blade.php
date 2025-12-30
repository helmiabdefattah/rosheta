@extends(auth()->check() && auth()->user()->laboratory_id ? 'laboratories.layouts.dashboard' : 'admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'إنشاء عرض' : 'Create Offer')

@section('page-description', app()->getLocale() === 'ar' ? 'إنشاء عرض جديد للطلب' : 'Create a new offer for the request')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        :root {
            --primary: #0d9488;
            --primary-dark: #0f766e;
            --primary-light: #ccfbf1;
            --secondary: #ff4081;
            --success: #4caf50;
            --warning: #ff9800;
            --danger: #f44336;
            --gray-50: #fafafa;
            --gray-100: #f5f5f5;
            --gray-200: #eeeeee;
            --gray-300: #e0e0e0;
            --gray-400: #bdbdbd;
            --gray-500: #9e9e9e;
            --gray-600: #757575;
            --gray-700: #616161;
            --gray-800: #424242;
            --gray-900: #212121;
            --border-radius: 8px;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .text-muted {
            color: var(--gray-600) !important;
        }

        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            background: white;
            overflow: hidden;
        }

        .card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .card-header {
            background: #0d9488;
            color: white;
            font-weight: 600;
            border: none;
            padding: 1rem 1.5rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .btn {
            border-radius: 6px;
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.2s ease;
            border: none;
            box-shadow: var(--shadow-sm);
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow);
        }

        .btn-primary {
            background: #0d9488;
            border-color: #0d9488;
        }

        .btn-primary:hover {
            background: #0f766e;
            border-color: #0f766e;
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success) 0%, #388e3c 100%);
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #388e3c 0%, #2e7d32 100%);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger) 0%, #d32f2f 100%);
        }

        .btn-danger:hover {
            background: linear-gradient(135deg, #d32f2f 0%, #c62828 100%);
        }

        .btn-secondary {
            background: linear-gradient(135deg, var(--gray-500) 0%, var(--gray-600) 100%);
        }

        .btn-secondary:hover {
            background: linear-gradient(135deg, var(--gray-600) 0%, var(--gray-700) 100%);
        }

        .form-label {
            font-weight: 500;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border-radius: 6px;
            border: 1px solid var(--gray-300);
            padding: 0.5rem 0.75rem;
            transition: all 0.2s ease;
            box-shadow: var(--shadow-sm);
        }

        .form-control:focus, .form-select:focus {
            border-color: #0d9488;
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1);
        }

        .form-control[readonly] {
            background-color: var(--gray-100);
            color: var(--gray-600);
        }

        .table {
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .table thead th {
            background: #ccfbf1;
            color: #0f766e;
            font-weight: 600;
            border: none;
            padding: 0.75rem 1rem;
        }

        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: rgba(13, 148, 136, 0.05);
        }

        .table tbody td {
            padding: 0.75rem 1rem;
            border-color: var(--gray-200);
            vertical-align: middle;
        }

        .thumbnail-selected {
            border: 2px solid #0d9488 !important;
            box-shadow: 0 0 0 2px #ccfbf1;
        }

        .image-container {
            position: relative;
            max-width: 100%;
            padding-top: 75%; /* 4:3 aspect */
            background: linear-gradient(135deg, var(--gray-100) 0%, var(--gray-200) 100%);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .image-container img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .nav-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(13, 148, 136, 0.8);
            color: #fff;
            border: none;
            padding: 0.75rem;
            border-radius: 50%;
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            z-index: 10;
        }

        .nav-arrow:hover {
            background: #0d9488;
            transform: translateY(-50%) scale(1.1);
        }

        .nav-left { left: 1rem; }
        .nav-right { right: 1rem; }

        .img-thumbnail {
            border-radius: 6px;
            transition: all 0.2s ease;
            border: 2px solid var(--gray-300);
        }

        .img-thumbnail:hover {
            border-color: #ccfbf1;
            transform: scale(1.05);
        }

        .alert {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-sm);
        }

        .alert-danger {
            background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }

        .text-danger {
            color: var(--danger) !important;
        }

        .select2-container--default .select2-selection--single {
            border: 1px solid var(--gray-300);
            border-radius: 6px;
            height: 38px;
            box-shadow: var(--shadow-sm);
        }

        .select2-container--default .select2-selection--single:focus {
            border-color: #0d9488;
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.1);
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 36px;
            padding-left: 12px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }

        .total-price-container {
            background: linear-gradient(135deg, var(--gray-50) 0%, var(--gray-100) 100%);
            border-radius: var(--border-radius);
            padding: 1rem 1.5rem;
        }

        #total_price {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0d9488;
            background: transparent;
            border: none;
            text-align: right;
            width: 120px;
        }

        .offer-line-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: var(--border-radius);
            border-left: 4px solid #0d9488;
            transition: all 0.3s ease;
        }

        .offer-line-card:hover {
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
            transform: translateX(5px);
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: var(--gray-100);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--gray-400);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--gray-500);
        }

        /* Animation for cards appearing */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card {
            animation: fadeInUp 0.5s ease;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .card-body {
                padding: 1rem;
            }

            .btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.9rem;
            }

            .nav-arrow {
                padding: 0.5rem;
                width: 35px;
                height: 35px;
            }
        }
        /* Add these styles to your existing CSS */
        .offer-line-row td {
            padding: 0.75rem 0.5rem;
            vertical-align: middle;
        }

        .offer-line-row .form-control,
        .offer-line-row .form-select {
            border-radius: 4px;
            font-size: 0.875rem;
        }

        .offer-line-row .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }

        .table-hover .offer-line-row:hover {
            background-color: rgba(13, 148, 136, 0.04) !important;
        }

        /* Ensure Select2 dropdowns look good in table */
        .select2-container--default .select2-selection--single {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            height: 32px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 30px;
            font-size: 0.875rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 30px;
        }

        /* Type indicator */
        .request-type-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .request-type-medicine {
            background: linear-gradient(135deg, #4caf50 0%, #388e3c 100%);
            color: white;
        }

        .request-type-test {
            background: linear-gradient(135deg, #2196f3 0%, #1976d2 100%);
            color: white;
        }

        .readonly-field {
            background-color: var(--gray-100);
            color: var(--gray-600);
            padding: 0.5rem 0.75rem;
            border-radius: 6px;
            border: 1px solid var(--gray-300);
            min-height: 38px;
            display: flex;
            align-items: center;
        }

        /* Tab styling */
        .nav-tabs {
            border-bottom: 2px solid #dee2e6;
        }

        .nav-tabs .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            color: #6c757d;
            font-weight: 500;
            padding: 0.75rem 1.5rem;
        }

        .nav-tabs .nav-link:hover {
            border-color: #0d9488;
            color: #0d9488;
        }

        .nav-tabs .nav-link.active {
            color: #0d9488;
            background-color: transparent;
            border-color: #0d9488;
            border-bottom-color: #0d9488;
        }
    </style>
@endpush

@section('content')

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs mb-4" id="offersTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="create-offer-tab" data-bs-toggle="tab" data-bs-target="#create-offer" type="button" role="tab" aria-controls="create-offer" aria-selected="true">
                {{ app()->getLocale() === 'ar' ? 'إنشاء عرض جديد' : 'Create New Offer' }}
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="existing-offers-tab" data-bs-toggle="tab" data-bs-target="#existing-offers" type="button" role="tab" aria-controls="existing-offers" aria-selected="false">
                {{ app()->getLocale() === 'ar' ? 'العروض الموجودة' : 'Existing Offers' }}
                @if($existingOffers->count() > 0)
                    <span class="badge bg-primary ms-2">{{ $existingOffers->count() }}</span>
                @endif
            </button>
        </li>
    </ul>

    <!-- Tabs Content -->
    <div class="tab-content" id="offersTabContent">
        <!-- Create Offer Tab -->
        <div class="tab-pane fade show active" id="create-offer" role="tabpanel" aria-labelledby="create-offer-tab">
            <form id="offerForm" action="{{ route('offers.store') }}" method="POST">
        @csrf
        <input type="hidden" name="client_request_id" value="{{ $clientRequest->id }}">

        <!-- Offer Details Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Offer Details</span>
                    <span id="requestTypeBadge" class="request-type-badge {{ in_array($clientRequest->type, ['test', 'radiology']) ? 'request-type-test' : 'request-type-medicine' }}">
    {{ in_array($clientRequest->type, ['test', 'radiology']) ? strtoupper($clientRequest->type).' REQUEST' : 'MEDICINE REQUEST' }}
</span>

                </div>
                <div class="card-body row g-3">
                    <!-- Client Request Display (Readonly) -->
                    <div class="col-md-6">
                        <label class="form-label">Client Request</label>
                        <div class="readonly-field">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <span>
                                    Request #{{ $clientRequest->id }} - {{ $clientRequest->client->name ?? 'N/A' }}
                                </span>
                                <span class="badge {{ in_array($clientRequest->type, ['test', 'radiology']) ? 'bg-info' : 'bg-success' }}">
                                    {{ in_array($clientRequest->type, ['test', 'radiology']) ? ucfirst($clientRequest->type) : 'Medicine' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Pharmacy/Laboratory Selection -->
                    <div class="col-md-6">
                        @if(in_array($clientRequest->type, ['test', 'radiology']))                            <!-- Laboratory Selection for Tests -->
                            <label for="laboratory_id" class="form-label">Laboratory <span class="text-danger">*</span></label>
                            @if(auth()->user()->laboratory_id)
                                <input type="hidden" name="laboratory_id" value="{{ auth()->user()->laboratory_id }}">
                                <div class="readonly-field">
                                    {{ auth()->user()->laboratory->name ?? 'N/A' }}
                                </div>
                            @else
                                <select name="laboratory_id" id="laboratory_id" class="form-select select2" required>
                                    <option value="">Select a laboratory</option>
                                    @foreach($laboratories as $id => $name)
                                        <option value="{{ $id }}" {{ old('laboratory_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            @endif
                            @error('laboratory_id')
                            <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        @else
                            <!-- Pharmacy Selection for Medicines -->
                            <label for="pharmacy_id" class="form-label">Pharmacy <span class="text-danger">*</span></label>
                            @if(auth()->user()->pharmacy_id)
                                <input type="hidden" name="pharmacy_id" value="{{ auth()->user()->pharmacy_id }}">
                                <div class="readonly-field">
                                    {{ auth()->user()->pharmacy->name ?? 'N/A' }}
                                </div>
                            @else
                                <select name="pharmacy_id" id="pharmacy_id" class="form-select select2" required>
                                    <option value="">Select a pharmacy</option>
                                    @foreach($pharmacies as $id => $name)
                                        <option value="{{ $id }}" {{ old('pharmacy_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            @endif
                            @error('pharmacy_id')
                            <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>
                    @if($clientRequest->client_address_id)
                    <!-- Client Address (Readonly) -->
                    <div class="col-md-6">
                        <label class="form-label">{{ app()->getLocale() === 'ar' ? 'عنوان العميل' : 'Client Address' }}</label>
                        <div class="readonly-field">
                            <div class="d-flex flex-column w-100">
                                <span>{{ $clientRequest->address->address ?? 'N/A' }}</span>
                                <small class="text-muted">
                                    {{ $clientRequest->address->city->name ?? '' }}
                                    @if(!empty($clientRequest->address->area))
                                        - {{ $clientRequest->address->area->name ?? '' }}
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>
                    <!-- Visit Price Input -->
                        <!-- Home Visit Options -->
                        <div class="col-md-6">
                            <label class="form-label">Home Visit</label>

                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="radio"
                                    name="home_visit_type"
                                    id="no_home_visit"
                                    value="no_visit"
                                >
                                <label class="form-check-label" for="no_home_visit">
                                    Lab does not offer home visit
                                </label>
                            </div>

                            <div class="form-check mt-1">
                                <input
                                    class="form-check-input"
                                    type="radio"
                                    name="home_visit_type"
                                    id="free_home_visit"
                                    value="free_visit"
                                >
                                <label class="form-check-label" for="free_home_visit">
                                    Lab offers free home visit
                                </label>
                            </div>
                            <div class="form-check mt-1">
                                <input
                                    class="form-check-input"
                                    type="radio"
                                    name="home_visit_type"
                                    id="price"
                                    value="price"
                                >
                                <label class="form-check-label" for="free_home_visit">
                                    add price
                                </label>
                            </div>

                        </div>

                        <div class="col-md-6">
                            <label for="visit_price" class="form-label">
                                {{ app()->getLocale() === 'ar' ? 'سعر الزيارة' : 'Visit Price' }}
                            </label>

                            <div class="input-group">
                                <input
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    class="form-control text-end"
                                    id="visit_price"
                                    name="visit_price"
                                    value="{{ old('visit_price', '0.00') }}"
                                >
                                <span class="input-group-text">EGP</span>
                            </div>

                            @error('visit_price')
                            <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                    @endif
                </div>
            </div>
        <!-- Request Images Card -->
        @if(!empty($clientRequest->images))
            <div class="card mb-4">
                <div class="card-header">Request Images</div>
                <div class="card-body">
                    <div class="image-container mb-3">
                        <button type="button" class="nav-arrow nav-left" id="prevImage">&lt;</button>
                        <img id="currentImage" src="{{ $clientRequest->images[0] ?? '' }}" alt="Request Image">
                        <button type="button" class="nav-arrow nav-right" id="nextImage">&gt;</button>
                    </div>
                    <div class="d-flex gap-2 overflow-auto" id="thumbnails">
                        @foreach($clientRequest->images as $index => $image)
                            <img src="{{ $image }}" class="img-thumbnail {{ $index == 0 ? 'thumbnail-selected' : '' }}"
                                 style="width: 75px; cursor: pointer;"
                                 data-index="{{ $index }}"
                                 alt="Thumbnail {{ $index + 1 }}">
                        @endforeach
                    </div>
                    <div class="text-center mt-2" id="imageCounter">1 / {{ count($clientRequest->images) }}</div>
                </div>
            </div>
        @endif

        <!-- Client Request Lines Card -->
        @if($clientRequest->lines->count() > 0)
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                <span>
                    @if(in_array($clientRequest->type, ['test', 'radiology']))
                        Client Request Tests ({{ $clientRequest->testLines->count() }})
                    @else
                        Client Request Lines ({{ $clientRequest->medicineLines->count() }})
                    @endif
                </span>
                    <button type="button" class="btn btn-success btn-sm" id="addAllLinesBtn">Add All to Offer</button>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                        @if(in_array($clientRequest->type, ['test', 'radiology']))
                            <tr>
                                <th>Test Name (EN)</th>
                                <th>Test Name (AR)</th>
                                <th>Description</th>
                                <th>Conditions</th>
                                <th>Action</th>
                            </tr>
                        @else
                            <tr>
                                <th>Medicine</th>
                                <th>Dosage Form</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Action</th>
                            </tr>
                        @endif
                        </thead>
                        <tbody>
                        @foreach($clientRequest->lines as $line)
                            @if(in_array($clientRequest->type, ['test', 'radiology']))
                                <tr>
                                    <td>{{ $line->medicalTest->test_name_en ?? 'N/A' }}</td>
                                    <td>{{ $line->medicalTest->test_name_ar ?? 'N/A' }}</td>
                                    <td class="small">{{ Str::limit($line->medicalTest->test_description ?? '', 100) }}</td>
                                    <td class="small">{{ Str::limit($line->medicalTest->conditions ?? '', 100) }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary add-line-btn"
                                                data-type="{{ $clientRequest->type }}"
                                                data-id="{{ $line->medical_test_id }}"
                                                data-name-en="{{ $line->medicalTest->test_name_en ?? '' }}"
                                                data-name-ar="{{ $line->medicalTest->test_name_ar ?? '' }}">
                                            Add to Offer
                                        </button>
                                    </td>
                                </tr>
                            @else
                                <tr>
                                    <td>{{ $line->medicine->name ?? 'N/A' }}</td>
                                    <td>{{ $line->medicine->dosage_form ?? 'N/A' }}</td>
                                    <td>{{ $line->quantity }}</td>
                                    <td>{{ $line->unit }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary add-line-btn"
                                                data-type="medicine"
                                                data-id="{{ $line->medicine_id }}"
                                                data-name="{{ $line->medicine->name ?? '' }}"
                                                data-dosage="{{ $line->medicine->dosage_form ?? '' }}"
                                                data-quantity="{{ $line->quantity }}"
                                                data-unit="{{ $line->unit }}"
                                                data-old-price="{{ $line->medicine->price ?? 0 }}">
                                            Add to Offer
                                        </button>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- Offer Lines Card -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span id="offerLinesTitle">
                    @if(in_array($clientRequest->type, ['test', 'radiology']))
                        Offer {{ strtoupper($clientRequest->type) }}s
                    @else
                        Offer Lines
                    @endif

                </span>
                <button type="button" class="btn btn-primary btn-sm" id="addOfferLineBtn">Add Line</button>
            </div>
            <div class="card-body" id="offerLinesContainer">
                <div class="text-center text-muted" id="noOfferLinesMsg">No offer lines added yet.</div>
            </div>
        </div>

        <!-- Total Price -->
        <div class="card mb-4">
            <div class="card-body total-price-container d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Total Price</h5>
                <div class="d-flex align-items-center gap-2">
                    <input type="text" id="total_price" name="total_price" class="form-control text-end fw-bold" value="0.00" readonly>
                    <span class="fw-bold">EGP</span>
                </div>
            </div>
        </div>

            <!-- Submit Buttons -->
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ url()->previous() }}" class="btn btn-secondary">{{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}</a>
                <button type="submit" class="btn btn-primary">{{ app()->getLocale() === 'ar' ? 'إنشاء العرض' : 'Create Offer' }}</button>
            </div>
            </form>
        </div>

        <!-- Existing Offers Tab -->
        <div class="tab-pane fade" id="existing-offers" role="tabpanel" aria-labelledby="existing-offers-tab">
            @if($existingOffers->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ app()->getLocale() === 'ar' ? 'العروض الموجودة للطلب' : 'Existing Offers for Request' }} #{{ $clientRequest->id }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ app()->getLocale() === 'ar' ? 'رقم العرض' : 'Offer ID' }}</th>
                                        <th>{{ app()->getLocale() === 'ar' ? 'المزود' : 'Provider' }}</th>
                                        <th>{{ app()->getLocale() === 'ar' ? 'السعر الإجمالي' : 'Total Price' }}</th>
                                        <th>{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                                        <th>{{ app()->getLocale() === 'ar' ? 'تاريخ الإنشاء' : 'Created At' }}</th>
                                        <th>{{ app()->getLocale() === 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($existingOffers as $offer)
                                        <tr>
                                            <td><strong>#{{ $offer->id }}</strong></td>
                                            <td>
                                                @if($offer->laboratory)
                                                    <span class="badge bg-info">{{ $offer->laboratory->name }}</span>
                                                @elseif($offer->pharmacy)
                                                    <span class="badge bg-success">{{ $offer->pharmacy->name }}</span>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td><strong>{{ number_format($offer->total_price, 2) }} {{ app()->getLocale() === 'ar' ? 'جنيه' : 'EGP' }}</strong></td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'bg-warning',
                                                        'accepted' => 'bg-success',
                                                        'rejected' => 'bg-danger',
                                                        'draft' => 'bg-secondary',
                                                    ];
                                                    $statusColor = $statusColors[$offer->status] ?? 'bg-secondary';
                                                @endphp
                                                <span class="badge {{ $statusColor }}">{{ ucfirst($offer->status) }}</span>
                                            </td>
                                            <td>{{ $offer->created_at->format('Y-m-d H:i') }}</td>
                                            <td>
                                                @php
                                                    $offerData = [
                                                        'id' => $offer->id,
                                                        'provider' => $offer->laboratory->name ?? $offer->pharmacy->name ?? 'N/A',
                                                        'status' => $offer->status,
                                                        'total_price' => (float) $offer->total_price,
                                                        'created_at' => $offer->created_at->format('Y-m-d H:i'),
                                                        'request_type' => $offer->request_type,
                                                        'lines' => [],
                                                    ];

                                                    // Compute lines total
                                                    $linesTotal = 0;

                                                    if (in_array($offer->request_type, ['test', 'radiology'])) {
                                                        foreach ($offer->testLines as $line) {
                                                            $price = (float) ($line->price ?? 0);
                                                            $linesTotal += $price;

                                                            $offerData['lines'][] = [
                                                                'test_name_en' => $line->medicalTest->test_name_en ?? 'N/A',
                                                                'test_name_ar' => $line->medicalTest->test_name_ar ?? 'N/A',
                                                                'price' => $price,
                                                            ];
                                                        }
                                                    } else { // medicines
                                                        foreach ($offer->medicineLines as $line) {
                                                            $quantity = (int) ($line->quantity ?? 1);
                                                            $price = (float) ($line->price ?? 0);
                                                            $linesTotal += $quantity * $price;

                                                            $offerData['lines'][] = [
                                                                'medicine_name' => $line->medicine->name ?? 'N/A',
                                                                'quantity' => $quantity,
                                                                'unit' => $line->unit ?? 'box',
                                                                'price' => $price,
                                                            ];
                                                        }
                                                    }

                                                    // Determine if the offer includes a home visit and calculate visit price
                                                    $hasHomeVisit = optional($offer->request)->client_address_id ? true : false;
                                                    $visitPrice = $hasHomeVisit ? max($offerData['total_price'] - $linesTotal, 0) : 0;

                                                    $offerData['has_home_visit'] = $hasHomeVisit;
                                                    $offerData['visit_price'] = $visitPrice;
                                                @endphp

                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-info view-offer-details"
                                                    data-offer='{{ json_encode($offerData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) }}'
                                                >
                                                    {{ app()->getLocale() === 'ar' ? 'عرض التفاصيل' : 'View Details' }}
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <p class="text-muted mb-0">{{ app()->getLocale() === 'ar' ? 'لا توجد عروض موجودة لهذا الطلب' : 'No existing offers for this request' }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Single Dynamic Modal for Offer Details -->
    <div class="modal fade" id="offerDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="offerModalTitle">{{ app()->getLocale() === 'ar' ? 'تفاصيل العرض' : 'Offer Details' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="offerModalBody">
                    <!-- Content will be generated by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ app()->getLocale() === 'ar' ? 'إغلاق' : 'Close' }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        const medicines = {!! json_encode($medicines ?? [], JSON_UNESCAPED_UNICODE) !!};
        const tests = {!! json_encode($tests ?? [], JSON_UNESCAPED_UNICODE) !!};
        const testPrices = {!! json_encode($testPrices ?? [], JSON_UNESCAPED_UNICODE) !!};
        const laboratoryId = {{ auth()->user()->laboratory_id ?? 'null' }};
        const requestType = "{{ $clientRequest->type ?? 'medicine' }}";
        const requestImages = {!! json_encode($clientRequest->images ?? [], JSON_UNESCAPED_UNICODE) !!};
        const requestLines = {!! json_encode($linesData ?? [], JSON_UNESCAPED_UNICODE) !!};

        let offerLines = [...requestLines];
        let currentImageIndex = 0;

        $(document).ready(function() {
            $('.select2').select2({ width: '100%' });
            initImageCarousel();
            initOfferLines();
            initVisitPriceToggle();
            $('#visit_price').on('input', updateTotal);

            // Add individual line
            $('.add-line-btn').click(function() {
                const type = $(this).data('type');
                if (type === 'test' || type === 'radiology') {
                    const testId = $(this).data('id');
                    addOfferLine({
                        medical_test_id: testId,
                        test_name_en: $(this).data('name-en'),
                        test_name_ar: $(this).data('name-ar'),
                        price: (laboratoryId && testPrices[testId]) ? testPrices[testId] : '',
                        dosage_form: 'test'
                    });
                } else {
                    addOfferLine({
                        medicine_id: $(this).data('id'),
                        medicine_name: $(this).data('name'),
                        dosage_form: $(this).data('dosage'),
                        quantity: $(this).data('quantity'),
                        unit: $(this).data('unit'),
                        old_price: $(this).data('old-price'),
                        price: ''
                    });
                }
            });

            // Add all pre-loaded lines
            $('#addAllLinesBtn').click(() => requestLines.forEach(addOfferLine));
            // Add empty line
            $('#addOfferLineBtn').click(() => addOfferLine());
            // Submit validation
            $('#offerForm').on('submit', e => {
                if (offerLines.length === 0) {
                    alert('Please add at least one offer line before submitting.');
                    e.preventDefault();
                }
            });
        });

        // ---------------- Image Carousel ----------------
        function initImageCarousel() {
            if (requestImages.length === 0) return;

            function updateImageDisplay() {
                $('#currentImage').attr('src', requestImages[currentImageIndex]);
                $('.img-thumbnail').removeClass('thumbnail-selected');
                $(`.img-thumbnail[data-index="${currentImageIndex}"]`).addClass('thumbnail-selected');
                $('#imageCounter').text(`${currentImageIndex + 1} / ${requestImages.length}`);
            }

            $('#prevImage').click(() => {
                currentImageIndex = (currentImageIndex - 1 + requestImages.length) % requestImages.length;
                updateImageDisplay();
            });
            $('#nextImage').click(() => {
                currentImageIndex = (currentImageIndex + 1) % requestImages.length;
                updateImageDisplay();
            });
            $('.img-thumbnail').click(function() {
                currentImageIndex = parseInt($(this).data('index'));
                updateImageDisplay();
            });

            updateImageDisplay();
        }

        // ---------------- Offer Lines ----------------
        function initOfferLines() {
            renderOfferLines();
        }

        function addOfferLine(line = null) {
            const defaultLine = (requestType === 'test' || requestType === 'radiology') ? {
                medical_test_id: '',
                test_name_en: '',
                test_name_ar: '',
                price: '',
                dosage_form: 'test'
            } : {
                medicine_id: '',
                medicine_name: '',
                dosage_form: '',
                quantity: 1,
                unit: 'box',
                old_price: '',
                price: ''
            };

            offerLines.push(line ? {...defaultLine, ...line} : defaultLine);
            renderOfferLines();
        }

        function renderOfferLines() {
            const container = $('#offerLinesContainer').empty();
            if (offerLines.length === 0) {
                $('#noOfferLinesMsg').show();
                updateTotal();
                return;
            }

            $('#noOfferLinesMsg').hide();
            const table = $('<table class="table table-bordered table-hover align-middle">');
            const thead = $('<thead class="table-light">');
            const tbody = $('<tbody>');

            if (requestType === 'test' || requestType === 'radiology') {
                thead.html(`
            <tr>
                <th width="25%">Test Name (EN)</th>
                <th width="25%">Test Name (AR)</th>
                <th width="20%" class="text-center">Price (EGP)</th>
                <th width="15%" class="text-center">Actions</th>
            </tr>
        `);
                offerLines.forEach((line, i) => {
                    const row = $('<tr>');
                    // Test dropdown
                    const testSelect = $('<select class="form-select form-select-sm">')
                        .attr('name', `offer_lines[${i}][medical_test_id]`)
                        .attr('id', `test_select_${i}`)
                        .append('<option value="">Select test</option>');
                    Object.entries(tests).forEach(([id, test]) => {
                        testSelect.append(`<option value="${id}" data-name-ar="${test.test_name_ar}">${test.test_name_en}</option>`);
                    });
                    testSelect.val(line.medical_test_id).on('change', function() {
                        const testId = $(this).val();
                        const selectedTest = tests[testId];
                        if (selectedTest) {
                            line.medical_test_id = testId;
                            line.test_name_en = selectedTest.test_name_en;
                            line.test_name_ar = selectedTest.test_name_ar;
                            line.price = (laboratoryId && testPrices[testId]) ? testPrices[testId] : '';
                        }
                        renderOfferLines();
                    });
                    row.append($('<td>').append(testSelect));
                    row.append($('<td>').text(line.test_name_ar || '-'));
                    row.append($('<td class="text-center">').append(
                        $('<input type="number" min="0" step="0.01" class="form-control form-control-sm">')
                            .attr('name', `offer_lines[${i}][price]`)
                            .val(line.price)
                            .on('input', function() { line.price = $(this).val(); updateTotal(); })
                    ));
                    row.append($('<td class="text-center">').append($('<button type="button" class="btn btn-danger btn-sm">Remove</button>')
                        .click(() => { offerLines.splice(i, 1); renderOfferLines(); })));

                    tbody.append(row);

                    $(`#test_select_${i}`).select2({ width: '100%', dropdownParent: container });
                });
            } else {
                thead.html(`
            <tr>
                <th width="25%">Medicine</th>
                <th width="12%" class="text-center">Dosage Form</th>
                <th width="10%" class="text-center">Old Price</th>
                <th width="8%" class="text-center">Quantity</th>
                <th width="10%" class="text-center">Unit</th>
                <th width="12%" class="text-center">Price (EGP)</th>
                <th width="15%" class="text-center">Actions</th>
            </tr>
        `);
                offerLines.forEach((line, i) => {
                    const row = $('<tr>');
                    const medSelect = $('<select class="form-select form-select-sm">')
                        .attr('name', `offer_lines[${i}][medicine_id]`)
                        .attr('id', `medicine_select_${i}`)
                        .append('<option value="">Select medicine</option>');
                    Object.entries(medicines).forEach(([id, med]) => medSelect.append(`<option value="${id}" data-dosage-form="${med.dosage_form}" data-old-price="${med.old_price}">${med.name}</option>`));
                    medSelect.val(line.medicine_id).on('change', function() {
                        const medId = $(this).val();
                        const selectedMed = medicines[medId];
                        if (selectedMed) {
                            line.medicine_id = medId;
                            line.medicine_name = selectedMed.name;
                            line.dosage_form = selectedMed.dosage_form;
                            line.old_price = selectedMed.old_price;
                        }
                        renderOfferLines();
                    });
                    row.append($('<td>').append(medSelect));
                    row.append($('<td class="text-center">').text(line.dosage_form || '-'));
                    row.append($('<td class="text-center">').text(line.old_price || '0.00'));
                    row.append($('<td class="text-center">').append(
                        $('<input type="number" min="1" class="form-control form-control-sm">')
                            .attr('name', `offer_lines[${i}][quantity]`)
                            .val(line.quantity)
                            .on('input', function(e) { line.quantity = e.target.value; updateTotal(); })
                    ));
                    row.append($('<td class="text-center">').append(
                        $('<select class="form-select form-select-sm"><option>box</option><option>strips</option><option>bottle</option></select>')
                            .attr('name', `offer_lines[${i}][unit]`)
                            .val(line.unit)
                            .on('change', e => { line.unit = e.target.value; })
                    ));
                    row.append($('<td class="text-center">').append(
                        $('<input type="number" min="0" step="0.01" class="form-control form-control-sm">')
                            .attr('name', `offer_lines[${i}][price]`)
                            .val(line.price)
                            .on('input', e => { line.price = e.target.value; updateTotal(); })
                    ));
                    row.append($('<td class="text-center">').append($('<button type="button" class="btn btn-danger btn-sm">Remove</button>').click(() => { offerLines.splice(i, 1); renderOfferLines(); })));
                    tbody.append(row);
                    $(`#medicine_select_${i}`).select2({ width: '100%', dropdownParent: container });
                });
            }

            table.append(thead, tbody);
            container.append(table);
            updateTotal();
        }

        // ---------------- Total Calculation ----------------
        function updateTotal() {
            let total = offerLines.reduce((sum, line) => sum + (parseFloat(line.price) || 0) * (parseFloat(line.quantity) || 1), 0);
            total += parseFloat($('#visit_price').val()) || 0;
            $('#total_price').val(total.toFixed(2));
        }

        // ---------------- Visit Price Toggle ----------------
        function initVisitPriceToggle() {
            const visitPriceInput = $('#visit_price');
            const noVisit = $('#no_home_visit');
            const freeVisit = $('#free_home_visit');
            const paidVisit = $('#price');

            function toggleVisitPrice() {
                if (paidVisit.is(':checked')) {
                    visitPriceInput.prop('disabled', false);
                } else {
                    visitPriceInput.val(0).prop('disabled', true);
                }
                updateTotal();
            }

            noVisit.add(freeVisit).add(paidVisit).change(toggleVisitPrice);
            toggleVisitPrice();
        }
    </script>
@endpush
