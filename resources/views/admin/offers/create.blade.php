@extends('admin.layouts.admin')
@section('title', 'Create Offer')
@section('page-title', 'Create Offer')
@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Offer</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Google Fonts for Filament style -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #3f51b5;
            --primary-dark: #303f9f;
            --primary-light: #c5cae9;
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

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            color: var(--gray-800);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
        }

        h1 {
            font-weight: 700;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
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
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
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
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, #283593 100%);
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
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(63, 81, 181, 0.1);
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
            background: linear-gradient(135deg, var(--primary-light) 0%, #e8eaf6 100%);
            color: var(--primary-dark);
            font-weight: 600;
            border: none;
            padding: 0.75rem 1rem;
        }

        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: rgba(63, 81, 181, 0.05);
        }

        .table tbody td {
            padding: 0.75rem 1rem;
            border-color: var(--gray-200);
            vertical-align: middle;
        }

        .thumbnail-selected {
            border: 2px solid var(--primary) !important;
            box-shadow: 0 0 0 2px var(--primary-light);
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
            background: rgba(63, 81, 181, 0.8);
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
            background: var(--primary);
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
            border-color: var(--primary-light);
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
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(63, 81, 181, 0.1);
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
            color: var(--primary);
            background: transparent;
            border: none;
            text-align: right;
            width: 120px;
        }

        .offer-line-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: var(--border-radius);
            border-left: 4px solid var(--primary);
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
            background-color: rgba(63, 81, 181, 0.04) !important;
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
    </style>
</head>
<body class="bg-light">

<div class="container py-5">

    <h1 class="mb-3">Create New Offer</h1>
    <p class="text-muted mb-4">Fill in the details below to create a new offer for Request #{{ $clientRequest->id }}</p>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form id="offerForm" action="{{ route('offers.store') }}" method="POST">
        @csrf
        <input type="hidden" name="client_request_id" value="{{ $clientRequest->id }}">

        <!-- Offer Details Card -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Offer Details</span>
                <span id="requestTypeBadge" class="request-type-badge {{ $clientRequest->type == 'test' ? 'request-type-test' : 'request-type-medicine' }}">
                    {{ $clientRequest->type == 'test' ? 'TEST REQUEST' : 'MEDICINE REQUEST' }}
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
                            <span class="badge {{ $clientRequest->type == 'test' ? 'bg-info' : 'bg-success' }}">
                                {{ $clientRequest->type == 'test' ? 'Test' : 'Medicine' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Pharmacy/Laboratory Selection -->
                <div class="col-md-6">
                    @if($clientRequest->type == 'test')
                        <!-- Laboratory Selection for Tests -->
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
        @if(($clientRequest->type == 'test' && $clientRequest->lines->count() > 0) ||
            ($clientRequest->type != 'test' && $clientRequest->lines->count() > 0))
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                <span>
                    @if($clientRequest->type == 'test')
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
                        @if($clientRequest->type == 'test')
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
                            @if($clientRequest->type == 'test')
                                <!-- عرض بيانات الفحص -->
                                <tr>
                                    <td>{{ $line->medicalTest->test_name_en ?? 'N/A' }}</td>
                                    <td>{{ $line->medicalTest->test_name_ar ?? 'N/A' }}</td>
                                    <td class="small">{{ Str::limit($line->medicalTest->test_description ?? '', 100) }}</td>
                                    <td class="small">{{ Str::limit($line->medicalTest->conditions ?? '', 100) }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary add-line-btn"
                                                data-type="test"
                                                data-id="{{ $line->medical_test_id }}"
                                                data-name-en="{{ $line->medicalTest->test_name_en ?? '' }}"
                                                data-name-ar="{{ $line->medicalTest->test_name_ar ?? '' }}">
                                            Add to Offer
                                        </button>
                                    </td>
                                </tr>
                            @else
                                <!-- عرض بيانات الدواء -->
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
                    @if($clientRequest->type == 'test')
                        Offer Tests
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
            <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Create Offer</button>
        </div>
    </form>
</div>

<!-- JS Dependencies -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Pass data to JS -->
<script>
    const medicines = {!! json_encode($medicines ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) !!};

    const tests = {!! json_encode($tests ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) !!};

    const requestType = "{{ $clientRequest->type ?? 'medicine' }}";

    const requestImages = {!! json_encode($clientRequest->images ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) !!};

    @php
        // Prepare request lines data in PHP
        $linesData = [];
        foreach ($clientRequest->lines as $line) {
            if ($clientRequest->type == 'test') {
                if ($line->medicalTest) {
                    $linesData[] = [
                        'medical_test_id' => $line->medical_test_id ?? null,
                        'test_name_en' => $line->medicalTest->test_name_en ?? null,
                        'test_name_ar' => $line->medicalTest->test_name_ar ?? null,
                        'test_description' => $line->medicalTest->test_description ?? null,
                        'conditions' => $line->medicalTest->conditions ?? null,
                    ];
                }
            } else {
                if ($line->medicine) {
                    $linesData[] = [
                        'medicine_id' => $line->medicine_id ?? null,
                        'medicine_name' => $line->medicine->name ?? null,
                        'dosage_form' => $line->medicine->dosage_form ?? null,
                        'quantity' => $line->quantity ?? 1,
                        'unit' => $line->unit ?? 'box',
                        'old_price' => $line->medicine->price ?? null,
                    ];
                }
            }
        }
    @endphp

    const requestLines = {!! json_encode($linesData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) !!};
</script>

<script>
    $(document).ready(function() {
        $('.select2').select2({ width: '100%' });

        let currentImageIndex = 0;
        let offerLines = [];
        let currentRequestType = requestType;

        // Image carousel functionality
        if (requestImages.length > 0) {
            $('#prevImage').click(function() {
                currentImageIndex = (currentImageIndex - 1 + requestImages.length) % requestImages.length;
                updateImageDisplay();
            });

            $('#nextImage').click(function() {
                currentImageIndex = (currentImageIndex + 1) % requestImages.length;
                updateImageDisplay();
            });

            $('.img-thumbnail').click(function() {
                currentImageIndex = parseInt($(this).data('index'));
                updateImageDisplay();
            });

            function updateImageDisplay() {
                $('#currentImage').attr('src', requestImages[currentImageIndex]);
                $('.img-thumbnail').removeClass('thumbnail-selected');
                $(`.img-thumbnail[data-index="${currentImageIndex}"]`).addClass('thumbnail-selected');
                $('#imageCounter').text(`${currentImageIndex + 1} / ${requestImages.length}`);
            }
        }

        // Add individual line button
        $('.add-line-btn').click(function() {
            const type = $(this).data('type');

            if (type === 'test') {
                addOfferLine({
                    medical_test_id: $(this).data('id'),
                    test_name_en: $(this).data('name-en'),
                    test_name_ar: $(this).data('name-ar'),
                    price: '',
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

        // Add all lines button
        $('#addAllLinesBtn').click(function() {
            requestLines.forEach(line => addOfferLine(line));
        });

        // Add empty line button
        $('#addOfferLineBtn').click(function() {
            addOfferLine();
        });

        function addOfferLine(line = null) {
            if (currentRequestType === 'test') {
                const newLine = line ? {
                    medical_test_id: line.medical_test_id || line.test_id || '',
                    test_name_en: line.test_name_en || line.medicine_name || '',
                    test_name_ar: line.test_name_ar || '',
                    price: line.price || '',
                    dosage_form: 'test'
                } : {
                    medical_test_id: '',
                    test_name_en: '',
                    test_name_ar: '',
                    price: '',
                    dosage_form: 'test'
                };
                offerLines.push(newLine);
            } else {
                const newLine = line ? {
                    medicine_id: line.medicine_id || '',
                    medicine_name: line.medicine_name || '',
                    dosage_form: line.dosage_form || '',
                    quantity: line.quantity || 1,
                    unit: line.unit || 'box',
                    old_price: line.old_price || '',
                    price: line.price || ''
                } : {
                    medicine_id: '',
                    medicine_name: '',
                    dosage_form: '',
                    quantity: 1,
                    unit: 'box',
                    old_price: '',
                    price: ''
                };
                offerLines.push(newLine);
            }
            renderOfferLines();
        }

        function renderOfferLines() {
            const container = $('#offerLinesContainer').empty();

            if (!offerLines.length) {
                container.append($('#noOfferLinesMsg').show());
                updateTotal();
                return;
            }

            $('#noOfferLinesMsg').hide();

            // Create table structure
            const table = $('<table class="table table-bordered table-hover align-middle">');
            const thead = $('<thead class="table-light">');
            const tbody = $('<tbody>');

            if (currentRequestType === 'test') {
                // Tests table header
                thead.html(`
                <tr>
                    <th width="25%" class="fw-semibold">Test Name (EN)</th>
                    <th width="25%" class="fw-semibold">Test Name (AR)</th>
                    <th width="20%" class="text-center">Price (EGP)</th>
                    <th width="15%" class="text-center">Actions</th>
                </tr>
            `);

                // Test rows
                offerLines.forEach((line, i) => {
                    const row = $('<tr class="offer-line-row">');

                    // Test Name EN (25%)
                    const testEnCell = $('<td>');
                    const testSelect = $('<select class="form-select form-select-sm test-select" required>')
                        .attr('name', `offer_lines[${i}][medical_test_id]`)
                        .attr('id', `test_select_${i}`)
                        .css('min-width', '200px');

                    testSelect.append('<option value="">Select test</option>');
                    Object.entries(tests).forEach(([id, test]) => {
                        testSelect.append(`<option value="${id}"
                        data-name-en="${test.test_name_en || ''}"
                        data-name-ar="${test.test_name_ar || ''}"
                        data-description="${test.test_description || ''}"
                        data-conditions="${test.conditions || ''}">${test.test_name_en}</option>`);
                    });
                    testSelect.val(line.medical_test_id);
                    testSelect.on('change', function() {
                        const testId = $(this).val();
                        const selectedTest = tests[testId];
                        if (selectedTest) {
                            line.medical_test_id = testId;
                            line.test_name_en = selectedTest.test_name_en;
                            line.test_name_ar = selectedTest.test_name_ar;
                        }
                        renderOfferLines();
                    });
                    testEnCell.append(testSelect);
                    row.append(testEnCell);

                    // Test Name AR (25%)
                    const testArCell = $('<td>');
                    const arNameSpan = $('<span class="small test-ar-name">').text(line.test_name_ar || '-');
                    testArCell.append(arNameSpan);
                    row.append(testArCell);

                    // Price column (20%)
                    const priceCell = $('<td class="text-center">');
                    const priceInput = $('<input type="number" min="0" step="0.01" class="form-control form-control-sm">')
                        .attr('name', `offer_lines[${i}][price]`)
                        .val(line.price)
                        .css('min-width', '120px')
                        .on('input', function() {
                            line.price = $(this).val();
                            updateTotal();
                        });
                    priceCell.append(priceInput);
                    row.append(priceCell);

                    // Actions column (15%)
                    const actionsCell = $('<td class="text-center">');
                    const removeBtn = $('<button type="button" class="btn btn-danger btn-sm">Remove</button>')
                        .on('click', () => {
                            offerLines.splice(i, 1);
                            renderOfferLines();
                        });
                    actionsCell.append(removeBtn);
                    row.append(actionsCell);

                    tbody.append(row);

                    // Initialize Select2 for test dropdown with custom search
                    $(`#test_select_${i}`).select2({
                        width: '100%',
                        dropdownParent: container,
                        minimumResultsForSearch: 2,
                        templateResult: formatTestResult,
                        templateSelection: formatTestSelection,
                        matcher: function(params, data) {
                            // If there's no search term, return all results
                            if ($.trim(params.term) === '') {
                                return data;
                            }

                            // Normalize search term (remove diacritics, convert to lowercase)
                            const term = params.term.toLowerCase()
                                .normalize("NFD")
                                .replace(/[\u0300-\u036f]/g, "");

                            // Get test data
                            const testId = data.id;
                            const testData = tests[testId];

                            if (!testData) return null;

                            // Search in both English and Arabic names
                            const nameEn = (testData.test_name_en || '').toLowerCase()
                                .normalize("NFD")
                                .replace(/[\u0300-\u036f]/g, "");
                            const nameAr = (testData.test_name_ar || '').toLowerCase()
                                .normalize("NFD")
                                .replace(/[\u0300-\u036f]/g, "");

                            // Check if search term exists in either name
                            if (nameEn.includes(term) || nameAr.includes(term)) {
                                return data;
                            }

                            return null;
                        }
                    });

                    // Update Arabic name when test is selected
                    if (line.medical_test_id && tests[line.medical_test_id]) {
                        arNameSpan.text(tests[line.medical_test_id].test_name_ar || '-');
                    }
                });
            } else {
                // Medicines table header
                thead.html(`
                <tr>
                    <th width="25%" class="fw-semibold">Medicine</th>
                    <th width="12%" class="text-center">Dosage Form</th>
                    <th width="10%" class="text-center">Old Price</th>
                    <th width="8%" class="text-center">Quantity</th>
                    <th width="10%" class="text-center">Unit</th>
                    <th width="12%" class="text-center">Price (EGP)</th>
                    <th width="15%" class="text-center">Actions</th>
                </tr>
            `);

                // Medicine rows
                offerLines.forEach((line, i) => {
                    const row = $('<tr class="offer-line-row">');

                    // Medicine column (25%)
                    const medicineCell = $('<td>');
                    const medSelect = $('<select class="form-select form-select-sm medicine-select" required>')
                        .attr('name', `offer_lines[${i}][medicine_id]`)
                        .attr('id', `medicine_select_${i}`)
                        .css('min-width', '120px');

                    medSelect.append('<option value="">Select medicine</option>');
                    Object.entries(medicines).forEach(([id, med]) => {
                        medSelect.append(`<option value="${id}"
                        data-name="${med.name || ''}"
                        data-name-ar="${med.name_ar || ''}"
                        data-dosage-form="${med.dosage_form || ''}"
                        data-old-price="${med.old_price || ''}"
                        data-units="${med.units || 'box'}">${med.name}</option>`);
                    });
                    medSelect.val(line.medicine_id);
                    medSelect.on('change', function() {
                        const medId = $(this).val();
                        const selectedMed = medicines[medId];
                        if (selectedMed) {
                            line.medicine_id = medId;
                            line.medicine_name = selectedMed.name;
                            line.dosage_form = selectedMed.dosage_form;
                            line.old_price = selectedMed.old_price;
                            line.unit = selectedMed.units || 'box';
                        }
                        renderOfferLines();
                    });
                    medicineCell.append(medSelect);
                    row.append(medicineCell);

                    // Dosage Form column (12%)
                    const dosageCell = $('<td class="text-center">');
                    const dosageSpan = $('<span class="small dosage-form">').text(line.dosage_form || '-');
                    dosageCell.append(dosageSpan);
                    row.append(dosageCell);

                    // Old Price column (10%)
                    const oldPriceCell = $('<td class="text-center">');
                    const oldPriceSpan = $('<span class="text-muted small old-price">').text(line.old_price || '0.00');
                    oldPriceCell.append(oldPriceSpan);
                    row.append(oldPriceCell);

                    // Quantity column (8%)
                    const qtyCell = $('<td class="text-center">');
                    const qtyInput = $('<input type="number" min="1" class="form-control form-control-sm">')
                        .attr('name', `offer_lines[${i}][quantity]`)
                        .val(line.quantity)
                        .css('min-width', '70px')
                        .on('input', updateTotal);
                    qtyCell.append(qtyInput);
                    row.append(qtyCell);

                    // Unit column (10%)
                    const unitCell = $('<td class="text-center">');
                    const unitSelect = $('<select class="form-select form-select-sm" required>')
                        .attr('name', `offer_lines[${i}][unit]`)
                        .css('min-width', '90px');

                    ['box', 'strips', 'bottle', 'pack', 'piece', 'tablet', 'capsule', 'vial', 'ampoule'].forEach(u => {
                        unitSelect.append(`<option value="${u}">${u}</option>`);
                    });
                    unitSelect.val(line.unit);
                    unitSelect.on('change', function() {
                        line.unit = $(this).val();
                    });
                    unitCell.append(unitSelect);
                    row.append(unitCell);

                    // Price column (12%)
                    const priceCell = $('<td class="text-center">');
                    const priceInput = $('<input type="number" min="0" step="0.01" class="form-control form-control-sm">')
                        .attr('name', `offer_lines[${i}][price]`)
                        .val(line.price)
                        .css('min-width', '100px')
                        .on('input', function() {
                            line.price = $(this).val();
                            updateTotal();
                        });
                    priceCell.append(priceInput);
                    row.append(priceCell);

                    // Actions column (15%)
                    const actionsCell = $('<td class="text-center">');
                    const removeBtn = $('<button type="button" class="btn btn-danger btn-sm">Remove</button>')
                        .on('click', () => {
                            offerLines.splice(i, 1);
                            renderOfferLines();
                        });
                    actionsCell.append(removeBtn);
                    row.append(actionsCell);

                    tbody.append(row);

                    // Initialize Select2 for medicine dropdown with custom search
                    $(`#medicine_select_${i}`).select2({
                        width: '100%',
                        dropdownParent: container,
                        minimumResultsForSearch: 2,
                        templateResult: formatMedicineResult,
                        templateSelection: formatMedicineSelection,
                        matcher: function(params, data) {
                            // If there's no search term, return all results
                            if ($.trim(params.term) === '') {
                                return data;
                            }

                            // Normalize search term (remove diacritics, convert to lowercase)
                            const term = params.term.toLowerCase()
                                .normalize("NFD")
                                .replace(/[\u0300-\u036f]/g, "");

                            // Get medicine data
                            const medId = data.id;
                            const medData = medicines[medId];

                            if (!medData) return null;

                            // Search in both English and Arabic names
                            const nameEn = (medData.name || '').toLowerCase()
                                .normalize("NFD")
                                .replace(/[\u0300-\u036f]/g, "");
                            const nameAr = (medData.name_ar || '').toLowerCase()
                                .normalize("NFD")
                                .replace(/[\u0300-\u036f]/g, "");

                            // Check if search term exists in either name
                            if (nameEn.includes(term) || nameAr.includes(term)) {
                                return data;
                            }

                            return null;
                        }
                    });

                    // Update related fields when medicine is selected
                    if (line.medicine_id && medicines[line.medicine_id]) {
                        const med = medicines[line.medicine_id];
                        dosageSpan.text(med.dosage_form || '-');
                        oldPriceSpan.text(med.old_price || '0.00');
                    }
                });
            }

            table.append(thead, tbody);
            container.append(table);
            updateTotal();
        }

        // Custom format functions for Select2
        function formatTestResult(result) {
            if (!result.id) return result.text;

            const testData = tests[result.id];
            if (!testData) return result.text;

            const $container = $('<span class="d-flex flex-column">');
            $container.append(`<span class="fw-semibold">${testData.test_name_en || ''}</span>`);
            if (testData.test_name_ar) {
                $container.append(`<span class="text-muted small text-end">${testData.test_name_ar}</span>`);
            }
            return $container;
        }

        function formatTestSelection(selection) {
            const testData = tests[selection.id];
            if (!testData) return selection.text;
            return testData.test_name_en || selection.text;
        }

        function formatMedicineResult(result) {
            if (!result.id) return result.text;

            const medData = medicines[result.id];
            if (!medData) return result.text;

            const $container = $('<span class="d-flex flex-column">');
            $container.append(`<span class="fw-semibold">${medData.name || ''}</span>`);
            if (medData.name_ar) {
                $container.append(`<span class="text-muted small text-end">${medData.name_ar}</span>`);
            }
            if (medData.dosage_form) {
                $container.append(`<span class="text-info small">${medData.dosage_form}</span>`);
            }
            return $container;
        }

        function formatMedicineSelection(selection) {
            const medData = medicines[selection.id];
            if (!medData) return selection.text;
            return medData.name || selection.text;
        }

        function updateTotal() {
            let total = 0;
            offerLines.forEach(line => {
                const price = parseFloat(line.price) || 0;
                const quantity = parseFloat(line.quantity) || 1;
                total += price * quantity;
            });
            $('#total_price').val(total.toFixed(2));
        }

        // Form submission: optional validation before sending
        $('#offerForm').on('submit', function(e) {
            if (offerLines.length === 0) {
                alert('Please add at least one offer line before submitting.');
                e.preventDefault();
            }
        });

        // Initialize with any pre-added lines
        renderOfferLines();
    });
</script>

</body>
</html>
@endsection
