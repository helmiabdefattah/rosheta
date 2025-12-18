<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Laboratory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
        .btn-primary {
            background: #3f51b5;
            border: none;
        }
        .btn-primary:hover {
            background: #303f9f;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Edit Laboratory</h1>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('laboratories.update', $laboratory) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Basic Information -->
            <div class="card">
                <div class="card-header">Basic Information</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Laboratory Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $laboratory->name) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Owner</label>
                            <select name="user_id" class="form-control select2">
                                <option value="">Select Owner</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id', $laboratory->user_id) == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" name="phone" class="form-control" value="{{ old('phone', $laboratory->phone) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $laboratory->email) }}">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2">{{ old('address', $laboratory->address) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location Information -->
            <div class="card">
                <div class="card-header">Location Information</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Area</label>
                            <select name="area_id" id="area_id" class="form-control select2">
                                <option value="">Select Area</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}" {{ old('area_id', $laboratory->area_id) == $area->id ? 'selected' : '' }}>
                                        {{ $area->name }} - {{ $area->city->name ?? '' }} - {{ $area->city->governorate->name ?? '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Latitude</label>
                            <input type="number" name="lat" id="lat" class="form-control" step="0.00000001" value="{{ old('lat', $laboratory->lat) }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Longitude</label>
                            <input type="number" name="lng" id="lng" class="form-control" step="0.00000001" value="{{ old('lng', $laboratory->lng) }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- License Information -->
            <div class="card">
                <div class="card-header">License Information</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">License Number</label>
                            <input type="text" name="license_number" class="form-control" value="{{ old('license_number', $laboratory->license_number) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Manager Name</label>
                            <input type="text" name="manager_name" class="form-control" value="{{ old('manager_name', $laboratory->manager_name) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Manager License</label>
                            <input type="text" name="manager_license" class="form-control" value="{{ old('manager_license', $laboratory->manager_license) }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Operating Hours -->
            <div class="card">
                <div class="card-header">Operating Hours</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Opening Time</label>
                            <input type="time" name="opening_time" class="form-control" value="{{ old('opening_time', $laboratory->opening_time ? \Carbon\Carbon::parse($laboratory->opening_time)->format('H:i') : '') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Closing Time</label>
                            <input type="time" name="closing_time" class="form-control" value="{{ old('closing_time', $laboratory->closing_time ? \Carbon\Carbon::parse($laboratory->closing_time)->format('H:i') : '') }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status -->
            <div class="card">
                <div class="card-header">Status</div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $laboratory->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Is Active</label>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $laboratory->notes) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('filament.admin.resources.laboratories.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary" onclick="console.log('Save button clicked!')">Save Changes</button>
            </div>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2();
        });
    </script>
</body>
</html>

