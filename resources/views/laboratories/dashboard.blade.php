<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratory Dashboard - Pending Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f7fa;
            padding: 2rem 0;
        }
        .card {
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }
        .card-header {
            background: #3f51b5;
            color: white;
            font-weight: 600;
        }
        .badge {
            padding: 0.5rem 0.75rem;
        }
        .btn-primary {
            background: #3f51b5;
            border: none;
        }
        .btn-primary:hover {
            background: #303f9f;
        }
        .btn-success {
            background: #4caf50;
        }
        .table {
            background: white;
        }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .search-box {
            max-width: 400px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="mb-1">Laboratory Dashboard</h1>
                <p class="text-muted mb-0">
                    @if($laboratory)
                        {{ $laboratory->name }}
                    @endif
                </p>
            </div>
            <div>
                <a href="{{ route('filament.admin.resources.laboratories.index') }}" class="btn btn-secondary">Back to Laboratories</a>
            </div>
        </div>

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

        <!-- Search Box -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('laboratories.dashboard') }}" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control search-box" 
                           placeholder="Search by Request ID, Client Name, or Phone..." 
                           value="{{ request('search') }}">
                    <button type="submit" class="btn btn-primary">Search</button>
                    @if(request('search'))
                        <a href="{{ route('laboratories.dashboard') }}" class="btn btn-secondary">Clear</a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Pending Requests Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Pending Test Requests</span>
                <span class="badge bg-light text-dark">{{ $requests->total() }} Requests</span>
            </div>
            <div class="card-body p-0">
                @if($requests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Client</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                    <th>Tests Count</th>
                                    <th>Created At</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requests as $request)
                                    <tr>
                                        <td><strong>#{{ $request->id }}</strong></td>
                                        <td>{{ $request->client->name ?? 'N/A' }}</td>
                                        <td>{{ $request->client->phone_number ?? 'N/A' }}</td>
                                        <td>
                                            @if($request->address)
                                                {{ $request->address->address ?? 'N/A' }}
                                                @if($request->address->area)
                                                    <br><small class="text-muted">
                                                        {{ $request->address->area->name ?? '' }}
                                                        @if($request->address->area->city)
                                                            - {{ $request->address->area->city->name ?? '' }}
                                                        @endif
                                                    </small>
                                                @endif
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ $request->lines->where('item_type', 'test')->count() ?? 0 }} Test(s)
                                            </span>
                                        </td>
                                        <td>{{ $request->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <span class="status-badge status-pending">
                                                {{ ucfirst($request->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('offers.create', ['request' => $request->id]) }}" 
                                                   class="btn btn-success btn-sm">
                                                    Make Offer
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-info btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#requestModal{{ $request->id }}">
                                                    View Details
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Request Details Modal -->
                                    <div class="modal fade" id="requestModal{{ $request->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Request #{{ $request->id }} Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Client:</strong> {{ $request->client->name ?? 'N/A' }}<br>
                                                            <strong>Phone:</strong> {{ $request->client->phone_number ?? 'N/A' }}<br>
                                                            <strong>Email:</strong> {{ $request->client->email ?? 'N/A' }}
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Status:</strong> 
                                                            <span class="status-badge status-pending">{{ ucfirst($request->status) }}</span><br>
                                                            <strong>Created:</strong> {{ $request->created_at->format('Y-m-d H:i') }}
                                                        </div>
                                                    </div>

                                                    @if($request->address)
                                                        <div class="mb-3">
                                                            <strong>Address:</strong><br>
                                                            {{ $request->address->address ?? 'N/A' }}<br>
                                                            @if($request->address->area)
                                                                <small class="text-muted">
                                                                    {{ $request->address->area->name ?? '' }}
                                                                    @if($request->address->area->city)
                                                                        - {{ $request->address->area->city->name ?? '' }}
                                                                        @if($request->address->area->city->governorate)
                                                                            - {{ $request->address->area->city->governorate->name ?? '' }}
                                                                        @endif
                                                                    @endif
                                                                </small>
                                                            @endif
                                                        </div>
                                                    @endif

                                                    @php
                                                        $testLines = $request->lines->where('item_type', 'test');
                                                    @endphp
                                                    @if($testLines && $testLines->count() > 0)
                                                        <div class="mb-3">
                                                            <strong>Requested Tests:</strong>
                                                            <table class="table table-sm table-bordered mt-2">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Test Name (EN)</th>
                                                                        <th>Test Name (AR)</th>
                                                                        <th>Description</th>
                                                                        <th>Conditions</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($testLines as $line)
                                                                        <tr>
                                                                            <td>{{ $line->medicalTest->test_name_en ?? 'N/A' }}</td>
                                                                            <td>{{ $line->medicalTest->test_name_ar ?? 'N/A' }}</td>
                                                                            <td>{{ $line->medicalTest->test_description ?? 'N/A' }}</td>
                                                                            <td>{{ $line->medicalTest->conditions ?? 'N/A' }}</td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @endif

                                                    @if($request->note)
                                                        <div class="mb-3">
                                                            <strong>Notes:</strong><br>
                                                            {{ $request->note }}
                                                        </div>
                                                    @endif

                                                    @if($request->images && count($request->images) > 0)
                                                        <div class="mb-3">
                                                            <strong>Images:</strong><br>
                                                            <div class="d-flex gap-2 flex-wrap">
                                                                @foreach($request->images as $image)
                                                                    <img src="{{ asset('storage/requests/' . $image) }}" 
                                                                         alt="Request Image" 
                                                                         class="img-thumbnail" 
                                                                         style="max-width: 150px; max-height: 150px;">
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif

                                                    @if($request->offers && $request->offers->count() > 0)
                                                        <div class="mb-3">
                                                            <strong>Existing Offers:</strong>
                                                            <ul class="list-group mt-2">
                                                                @foreach($request->offers as $offer)
                                                                    <li class="list-group-item">
                                                                        Offer #{{ $offer->id }} - 
                                                                        Total: {{ number_format($offer->total_price, 2) }} EGP - 
                                                                        Status: {{ ucfirst($offer->status) }}
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    <a href="{{ route('offers.create', ['request' => $request->id]) }}" 
                                                       class="btn btn-success">
                                                        Make Offer
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="card-footer">
                        {{ $requests->links() }}
                    </div>
                @else
                    <div class="card-body text-center py-5">
                        <p class="text-muted mb-0">No pending test requests found.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

