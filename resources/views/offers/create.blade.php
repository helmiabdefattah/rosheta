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
            --primary: #f1c20d;
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
    </style>
</head>
<body class="bg-light">

<div class="container py-5">

    <h1 class="mb-3">Create New Offer</h1>
    <p class="text-muted mb-4">Fill in the details below to create a new offer</p>

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form id="offerForm" action="{{ route('offers.store') }}" method="POST">
        @csrf

        <!-- Offer Details Card -->
        <div class="card mb-4">
            <div class="card-header">Offer Details</div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label for="client_request_id" class="form-label">Client Request <span class="text-danger">*</span></label>
                    <select name="client_request_id" id="client_request_id" class="form-select select2" required>
                        <option value="">Select a client request</option>
                        @foreach($clientRequests as $id => $label)
                            <option value="{{ $id }}" {{ old('client_request_id', $clientRequest?->id ?? '') == $id ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('client_request_id')
                    <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="pharmacy_id" class="form-label">Pharmacy <span class="text-danger">*</span></label>
                    @if(auth()->user()->pharmacy_id)
                        <input type="hidden" name="pharmacy_id" value="{{ auth()->user()->pharmacy_id }}">
                        <input type="text" class="form-control" value="{{ auth()->user()->pharmacy->name ?? 'N/A' }}" readonly>
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
                </div>
            </div>
        </div>

        <!-- Request Images Card -->
        <div class="card mb-4" id="requestImagesCard" style="display: none;">
            <div class="card-header">Request Images</div>
            <div class="card-body">
                <div class="image-container mb-3">
                    <button type="button" class="nav-arrow nav-left" id="prevImage">&lt;</button>
                    <img id="currentImage" src="" alt="Request Image">
                    <button type="button" class="nav-arrow nav-right" id="nextImage">&gt;</button>
                </div>
                <div class="d-flex gap-2 overflow-auto" id="thumbnails"></div>
                <div class="text-center mt-2" id="imageCounter"></div>
            </div>
        </div>

        <!-- Client Request Lines Card -->
        <div class="card mb-4" id="requestLinesCard" style="display: none;">
            <div class="card-header d-flex justify-content-between align-items-center">
                Client Request Lines
                <button type="button" class="btn btn-success btn-sm" id="addAllLinesBtn">Add All to Offer</button>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-hover" id="requestLinesTable">
                    <thead class="table-light">
                    <tr>
                        <th>Medicine</th>
                        <th>Dosage Form</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <!-- Offer Lines Card -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                Offer Lines
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
                    <input type="text" id="total_price" name="total_price" class="form-control text-end fw-bold" value="{{ old('total_price', '0.00') }}" readonly>
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

<!-- Pass medicines to JS -->
<script>
    const medicines = @json($medicines);
</script>

<script>
    $(document).ready(function() {
        $('.select2').select2({ width: '100%' });

        let requestImages = [];
        let currentImageIndex = 0;
        let requestLines = [];
        let offerLines = [];

        function renderImages() {
            if (!requestImages.length) { $('#requestImagesCard').hide(); return; }
            $('#requestImagesCard').show();
            $('#currentImage').attr('src', requestImages[currentImageIndex]);
            $('#thumbnails').empty();
            requestImages.forEach((img,i)=>{
                const thumb = $('<img>').attr('src',img).addClass('img-thumbnail').css({width:'75px', cursor:'pointer'});
                if(i===currentImageIndex) thumb.addClass('thumbnail-selected');
                thumb.on('click',()=>{ currentImageIndex=i; renderImages(); });
                $('#thumbnails').append(thumb);
            });
            $('#imageCounter').text(`${currentImageIndex+1} / ${requestImages.length}`);
        }
        $('#prevImage').click(()=>{ currentImageIndex=(currentImageIndex-1+requestImages.length)%requestImages.length; renderImages(); });
        $('#nextImage').click(()=>{ currentImageIndex=(currentImageIndex+1)%requestImages.length; renderImages(); });

        function renderRequestLines() {
            if(!requestLines.length){ $('#requestLinesCard').hide(); return; }
            $('#requestLinesCard').show();
            const tbody = $('#requestLinesTable tbody').empty();
            requestLines.forEach((line)=>{
                const row = $('<tr>');
                row.append('<td>'+line.medicine_name+'</td>');
                row.append('<td>'+line.dosage_form+'</td>');
                row.append('<td>'+line.quantity+'</td>');
                row.append('<td>'+line.unit+'</td>');
                const btn = $('<button type="button" class="btn btn-sm btn-primary">Add to Offer</button>');
                btn.on('click',()=> addOfferLine(line));
                row.append($('<td>').append(btn));
                tbody.append(row);
            });
        }

        function addOfferLine(line=null){
            const newLine = line ? {
                medicine_id: line.medicine_id,
                quantity: line.quantity || 1,
                unit: line.unit || 'box',
                price: line.price || '',
                old_price: medicines[line.medicine_id]?.old_price || '',
                dosage_form: medicines[line.medicine_id]?.dosage_form || ''
            } : { medicine_id:'', quantity:1, unit:'box', price:'', old_price:'', dosage_form:'' };
            offerLines.push(newLine);
            renderOfferLines();
        }

        function renderOfferLines(){
            const container = $('#offerLinesContainer').empty();
            if(!offerLines.length){
                container.append($('#noOfferLinesMsg').show());
                updateTotal();
                return;
            }
            $('#noOfferLinesMsg').hide();

            // Create table structure
            const table = $('<table class="table table-bordered table-hover align-middle">');
            const thead = $('<thead class="table-light">');
            const tbody = $('<tbody>');

            // Define column headers with appropriate widths
            const headers = [
                { text: 'Medicine', width: '25%', class: 'fw-semibold' },
                { text: 'Dosage Form', width: '12%', class: 'text-center' },
                { text: 'Old Price (EGP)', width: '10%', class: 'text-center' },
                { text: 'Quantity', width: '8%', class: 'text-center' },
                { text: 'Unit', width: '10%', class: 'text-center' },
                { text: 'Price (EGP)', width: '12%', class: 'text-center' },
                { text: 'Actions', width: '15%', class: 'text-center' }
            ];

            // Create header row
            const headerRow = $('<tr>');
            headers.forEach(header => {
                headerRow.append(`<th width="${header.width}" class="${header.class}">${header.text}</th>`);
            });
            thead.append(headerRow);
            table.append(thead);

            // Create data rows
            offerLines.forEach((line, i) => {
                const row = $('<tr class="offer-line-row">');

                // Medicine column (25%)
                const medicineCell = $('<td>');
                const medSelect = $('<select class="form-select form-select-sm" required>')
                    .attr('name', `offer_lines[${i}][medicine_id]`)
                    .css('min-width', '120px');

                medSelect.append('<option value="">Select medicine</option>');
                Object.entries(medicines).forEach(([id, med]) => {
                    medSelect.append(`<option value="${id}">${med.name}</option>`);
                });
                medSelect.val(line.medicine_id);
                medSelect.on('change', function() {
                    const medId = $(this).val();
                    line.medicine_id = medId;
                    line.dosage_form = medicines[medId]?.dosage_form || '';
                    line.old_price = medicines[medId]?.old_price || '';
                    line.unit = medicines[medId]?.units || 'box';
                    renderOfferLines();
                });
                medicineCell.append(medSelect);
                row.append(medicineCell);

                // Dosage Form column (12%)
                const dosageCell = $('<td class="text-center">');
                dosageCell.append(
                    $('<span class="small">').text(line.dosage_form || '-')
                        .css('font-size', '0.875rem')
                );
                row.append(dosageCell);

                // Old Price column (10%)
                const oldPriceCell = $('<td class="text-center">');
                oldPriceCell.append(
                    $('<span class="text-muted small">').text(line.old_price || '0.00')
                        .css('font-size', '0.875rem')
                );
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

                // Initialize Select2 for medicine dropdown
                medSelect.select2({
                    width: '100%',
                    dropdownParent: container,
                    minimumResultsForSearch: 3
                });
            });

            table.append(tbody);
            container.append(table);
            updateTotal();
        }
        function updateTotal(){
            let total=0;
            offerLines.forEach((line,i)=>{
                const qty=parseFloat($(`[name="offer_lines[${i}][quantity]"]`).val())||0;
                const price=parseFloat($(`[name="offer_lines[${i}][price]"]`).val())||0;
                total+=qty*price;
            });
            $('#total_price').val(total.toFixed(2));
        }

        $('#addOfferLineBtn').click(()=>addOfferLine());
        $('#addAllLinesBtn').click(()=>requestLines.forEach(l=>addOfferLine(l)));

        $('#client_request_id').on('change',function(){
            const id=$(this).val();
            if(!id){ requestImages=[]; requestLines=[]; renderImages(); renderRequestLines(); return; }

            // Fetch images
            $.getJSON(`/api/client-requests/${id}/images`,function(data){ requestImages=data.images||[]; currentImageIndex=0; renderImages(); });

            // Fetch lines
            $.getJSON(`/api/client-requests/${id}/lines`,function(data){ requestLines=data.lines||[]; renderRequestLines(); });
        });

        if($('#client_request_id').val()) $('#client_request_id').trigger('change');
    });
</script>

</body>
</html>
