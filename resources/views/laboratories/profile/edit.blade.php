@extends('laboratories.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'تعديل ملف المعمل' : 'Edit Laboratory Profile')

@section('page-description', app()->getLocale() === 'ar' ? 'تعديل معلومات المعمل' : 'Update laboratory information')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
    </style>
@endpush

@section('content')
    @if($errors->any())
        <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm" role="alert">
            <p class="font-bold">{{ app()->getLocale() === 'ar' ? 'خطأ' : 'Error' }}</p>
            <ul class="list-disc list-inside mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('laboratories.profile.update', $laboratory) }}" method="POST" id="laboratory-edit-form" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="is_active" value="0" id="is_active_hidden">

        <!-- Logo Section -->
        <div class="card">
            <div class="card-header">{{ app()->getLocale() === 'ar' ? 'شعار المعمل' : 'Laboratory Logo' }}</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ app()->getLocale() === 'ar' ? 'معاينة الشعار الحالي' : 'Current Logo Preview' }}</label>
                        <div class="mb-3">
                            @if($laboratory->getFirstMediaUrl('logo'))
                                <img src="{{ $laboratory->getFirstMediaUrl('logo') }}" alt="Logo" class="img-thumbnail" style="max-width: 200px; max-height: 200px; object-fit: contain;">
                            @else
                                <div class="border rounded p-4 text-center text-muted" style="width: 200px; height: 200px; display: flex; align-items: center; justify-content: center;">
                                    {{ app()->getLocale() === 'ar' ? 'لا يوجد شعار' : 'No Logo' }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ app()->getLocale() === 'ar' ? 'رفع شعار جديد' : 'Upload New Logo' }}</label>
                        <input type="file" name="logo" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
                        <small class="form-text text-muted">{{ app()->getLocale() === 'ar' ? 'الصيغ المدعومة: JPEG, PNG, GIF, WebP. الحد الأقصى للحجم: 2MB' : 'Supported formats: JPEG, PNG, GIF, WebP. Max size: 2MB' }}</small>
                        <div id="logoPreview" class="mt-3" style="display: none;">
                            <label class="form-label">{{ app()->getLocale() === 'ar' ? 'معاينة الشعار الجديد' : 'New Logo Preview' }}</label>
                            <img id="logoPreviewImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px; max-height: 200px; object-fit: contain;">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Basic Information -->
        <div class="card">
            <div class="card-header">{{ app()->getLocale() === 'ar' ? 'المعلومات الأساسية' : 'Basic Information' }}</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ app()->getLocale() === 'ar' ? 'اسم المعمل' : 'Laboratory Name' }} <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $laboratory->name) }}" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ app()->getLocale() === 'ar' ? 'المالك' : 'Owner' }}</label>
                        <select name="user_id" id="user_id" class="form-control select2-owner">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'اختر المالك' : 'Select Owner' }}</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" data-email="{{ $user->email }}" {{ old('user_id', $laboratory->user_id) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ app()->getLocale() === 'ar' ? 'الهاتف' : 'Phone' }}</label>
                        <input type="tel" name="phone" class="form-control" value="{{ old('phone', $laboratory->phone) }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email' }}</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $laboratory->email) }}">
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">{{ app()->getLocale() === 'ar' ? 'العنوان' : 'Address' }}</label>
                        <textarea name="address" class="form-control" rows="2">{{ old('address', $laboratory->address) }}</textarea>
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
                                <option value="{{ $area->id }}" {{ old('area_id', $laboratory->area_id) == $area->id ? 'selected' : '' }}>
                                    {{ $area->name }} - {{ $area->city->name ?? '' }} - {{ $area->city->governorate->name ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">{{ app()->getLocale() === 'ar' ? 'خط العرض' : 'Latitude' }}</label>
                        <input type="number" name="lat" id="lat" class="form-control" step="0.00000001" value="{{ old('lat', $laboratory->lat) }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">{{ app()->getLocale() === 'ar' ? 'خط الطول' : 'Longitude' }}</label>
                        <input type="number" name="lng" id="lng" class="form-control" step="0.00000001" value="{{ old('lng', $laboratory->lng) }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- License Information -->
        <div class="card">
            <div class="card-header">{{ app()->getLocale() === 'ar' ? 'معلومات الترخيص' : 'License Information' }}</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ app()->getLocale() === 'ar' ? 'رقم الترخيص' : 'License Number' }}</label>
                        <input type="text" name="license_number" class="form-control" value="{{ old('license_number', $laboratory->license_number) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ app()->getLocale() === 'ar' ? 'اسم المدير' : 'Manager Name' }}</label>
                        <input type="text" name="manager_name" class="form-control" value="{{ old('manager_name', $laboratory->manager_name) }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">{{ app()->getLocale() === 'ar' ? 'ترخيص المدير' : 'Manager License' }}</label>
                        <input type="text" name="manager_license" class="form-control" value="{{ old('manager_license', $laboratory->manager_license) }}">
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
                        <input type="time" name="opening_time" class="form-control" value="{{ old('opening_time', $laboratory->opening_time ? \Carbon\Carbon::parse($laboratory->opening_time)->format('H:i') : '') }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ app()->getLocale() === 'ar' ? 'وقت الإغلاق' : 'Closing Time' }}</label>
                        <input type="time" name="closing_time" class="form-control" value="{{ old('closing_time', $laboratory->closing_time ? \Carbon\Carbon::parse($laboratory->closing_time)->format('H:i') : '') }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Status -->
        <div class="card">
            <div class="card-header">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active" {{ old('is_active', $laboratory->is_active) ? 'checked' : '' }} style="width: 3rem; height: 1.5rem; cursor: pointer;">
                        <label class="form-check-label ms-2" for="is_active" style="cursor: pointer;">{{ app()->getLocale() === 'ar' ? 'نشط' : 'Is Active' }}</label>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ app()->getLocale() === 'ar' ? 'ملاحظات' : 'Notes' }}</label>
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes', $laboratory->notes) }}</textarea>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('laboratories.dashboard') }}" class="btn btn-secondary">{{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}</a>
            <button type="submit" class="btn btn-primary" id="save-btn">{{ app()->getLocale() === 'ar' ? 'حفظ التغييرات' : 'Save Changes' }}</button>
        </div>
    </form>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2 for Owner with custom formatting
            $('#user_id').select2({
                placeholder: '{{ app()->getLocale() === 'ar' ? 'اختر المالك' : 'Select Owner' }}',
                allowClear: true,
                width: '100%',
                templateResult: formatUser,
                templateSelection: formatUserSelection,
                escapeMarkup: function(markup) {
                    return markup;
                }
            });
            
            // Initialize Select2 for Area
            $('#area_id').select2({
                placeholder: '{{ app()->getLocale() === 'ar' ? 'اختر المنطقة' : 'Select Area' }}',
                allowClear: true,
                width: '100%'
            });
            
            // Format user option with name and email
            function formatUser(user) {
                if (!user.id) {
                    return user.text;
                }
                var $user = $('<div style="padding: 4px 0;"></div>');
                var text = user.text;
                var parts = text.split(' (');
                if (parts.length === 2) {
                    var email = parts[1].replace(')', '');
                    $user.append('<div style="font-weight: 600; color: #1e293b; font-size: 14px;">' + parts[0] + '</div>');
                    $user.append('<div style="color: #64748b; font-size: 12px; margin-top: 2px;">' + email + '</div>');
                } else {
                    $user.text(text);
                }
                return $user;
            }
            
            function formatUserSelection(user) {
                if (!user.id) {
                    return user.text;
                }
                var text = user.text;
                var parts = text.split(' (');
                return parts[0] || text;
            }
            
            // Handle checkbox to send 0 when unchecked
            $('#is_active').on('change', function() {
                $('#is_active_hidden').prop('disabled', this.checked);
            });
            // Set initial state
            $('#is_active_hidden').prop('disabled', $('#is_active').is(':checked'));
            
            // Handle form submission - let it submit normally
            $('#laboratory-edit-form').on('submit', function(e) {
                var $btn = $('#save-btn');
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>{{ app()->getLocale() === 'ar' ? 'جاري الحفظ...' : 'Saving...' }}');
                // Form will submit normally
            });
            
            // Handle logo preview
            $('input[name="logo"]').on('change', function(e) {
                var file = e.target.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#logoPreviewImg').attr('src', e.target.result);
                        $('#logoPreview').show();
                    };
                    reader.readAsDataURL(file);
                } else {
                    $('#logoPreview').hide();
                }
            });
        });
    </script>
@endpush

