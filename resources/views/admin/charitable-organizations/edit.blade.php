@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'تعديل منظمة خيرية' : 'Edit Charitable Organization')
@section('page-title', app()->getLocale() === 'ar' ? 'تعديل منظمة خيرية' : 'Edit Charitable Organization')
@section('page-description', app()->getLocale() === 'ar' ? 'تعديل تفاصيل المنظمة الخيرية' : 'Edit charitable organization details')

@section('content')
<div class="max-w-4xl mx-auto">
    <form action="{{ route('admin.charitable-organizations.update', $charitableOrganization) }}" method="POST">
        @csrf
        @method('PUT')

        <x-admin.ui.form-card :title="app()->getLocale() === 'ar' ? 'معلومات المنظمة الخيرية' : 'Charitable Organization Information'">
            <div class="space-y-6">
                <!-- Name -->
                <div>
                    <x-admin.ui.label for="name" required>{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</x-admin.ui.label>
                    <x-admin.ui.input name="name" :value="old('name', $charitableOrganization->name)" required placeholder="{{ app()->getLocale() === 'ar' ? 'مثال: جمعية خيرية' : 'e.g. Charity Organization' }}" />
                </div>

                <!-- Governorate, City and Area -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Governorate -->
                    <div>
                        <x-admin.ui.label for="governorate_id" required>{{ app()->getLocale() === 'ar' ? 'المحافظة' : 'Governorate' }}</x-admin.ui.label>
                        <select name="governorate_id" id="governorate_id" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'اختر المحافظة' : 'Select Governorate' }}</option>
                            @foreach($governorates as $governorate)
                                <option value="{{ $governorate->id }}" {{ old('governorate_id', $charitableOrganization->governorate_id) == $governorate->id ? 'selected' : '' }}>
                                    {{ app()->getLocale() === 'ar' ? ($governorate->name_ar ?? $governorate->name) : ($governorate->name ?? $governorate->name_ar) }}
                                </option>
                            @endforeach
                        </select>
                        @error('governorate_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- City -->
                    <div>
                        <x-admin.ui.label for="city_id" required>{{ app()->getLocale() === 'ar' ? 'المدينة' : 'City' }}</x-admin.ui.label>
                        <select name="city_id" id="city_id" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'اختر المدينة' : 'Select City' }}</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}" {{ old('city_id', $charitableOrganization->city_id) == $city->id ? 'selected' : '' }}>
                                    {{ app()->getLocale() === 'ar' ? ($city->name_ar ?? $city->name) : ($city->name ?? $city->name_ar) }}
                                </option>
                            @endforeach
                        </select>
                        @error('city_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Area -->
                    <div>
                        <x-admin.ui.label for="area_id" required>{{ app()->getLocale() === 'ar' ? 'المنطقة' : 'Area' }}</x-admin.ui.label>
                        <select name="area_id" id="area_id" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            <option value="">{{ app()->getLocale() === 'ar' ? 'اختر المنطقة' : 'Select Area' }}</option>
                            @foreach($areas as $area)
                                <option value="{{ $area->id }}" {{ old('area_id', $charitableOrganization->area_id) == $area->id ? 'selected' : '' }}>
                                    {{ app()->getLocale() === 'ar' ? ($area->name_ar ?? $area->name) : ($area->name ?? $area->name_ar) }}
                                </option>
                            @endforeach
                        </select>
                        @error('area_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Address -->
                <div>
                    <x-admin.ui.label for="address" required>{{ app()->getLocale() === 'ar' ? 'العنوان' : 'Address' }}</x-admin.ui.label>
                    <x-admin.ui.input name="address" :value="old('address', $charitableOrganization->address)" required placeholder="{{ app()->getLocale() === 'ar' ? 'مثال: شارع الرئيسي، رقم 10' : 'e.g. Main Street, No. 10' }}" />
                </div>

                <!-- Phone Numbers -->
                <div>
                    <x-admin.ui.label>{{ app()->getLocale() === 'ar' ? 'أرقام الهاتف' : 'Phone Numbers' }}</x-admin.ui.label>
                    <div id="phone-numbers-container" class="space-y-2">
                        @php
                            $phoneNumbers = old('phone_numbers', $charitableOrganization->phone_numbers ?? []);
                            if (empty($phoneNumbers)) {
                                $phoneNumbers = [''];
                            }
                        @endphp
                        @foreach($phoneNumbers as $index => $phone)
                            <div class="flex items-center gap-2 phone-number-item">
                                <x-admin.ui.input name="phone_numbers[]" :value="$phone" placeholder="{{ app()->getLocale() === 'ar' ? 'مثال: 01234567890' : 'e.g. 01234567890' }}" />
                                <button type="button" onclick="removePhoneNumber(this)" class="px-3 py-2 text-sm text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                                    {{ app()->getLocale() === 'ar' ? 'حذف' : 'Remove' }}
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" onclick="addPhoneNumber()" class="mt-2 px-4 py-2 text-sm text-primary bg-primary/10 rounded-lg hover:bg-primary/20 transition-colors">
                        {{ app()->getLocale() === 'ar' ? '+ إضافة رقم هاتف' : '+ Add Phone Number' }}
                    </button>
                </div>

                <!-- Services -->
                <div>
                    <x-admin.ui.label>{{ app()->getLocale() === 'ar' ? 'الخدمات' : 'Services' }}</x-admin.ui.label>
                    <div id="services-container" class="space-y-2">
                        @php
                            $services = old('services', $charitableOrganization->services ?? []);
                            if (empty($services)) {
                                $services = [''];
                            }
                        @endphp
                        @foreach($services as $index => $service)
                            <div class="flex items-center gap-2 service-item">
                                <x-admin.ui.input name="services[]" :value="$service" placeholder="{{ app()->getLocale() === 'ar' ? 'مثال: تقديم مساعدات غذائية' : 'e.g. Food Assistance' }}" />
                                <button type="button" onclick="removeService(this)" class="px-3 py-2 text-sm text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                                    {{ app()->getLocale() === 'ar' ? 'حذف' : 'Remove' }}
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" onclick="addService()" class="mt-2 px-4 py-2 text-sm text-primary bg-primary/10 rounded-lg hover:bg-primary/20 transition-colors">
                        {{ app()->getLocale() === 'ar' ? '+ إضافة خدمة' : '+ Add Service' }}
                    </button>
                </div>
            </div>
        </x-admin.ui.form-card>

        <div class="mt-8 flex items-center justify-end gap-3">
            <a href="{{ route('admin.charitable-organizations.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-all">
                {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
            </a>
            <x-admin.ui.button type="submit">
                {{ app()->getLocale() === 'ar' ? 'حفظ التغييرات' : 'Save Changes' }}
            </x-admin.ui.button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const isRTL = {{ app()->getLocale() === 'ar' ? 'true' : 'false' }};
        const currentGovernorateId = {{ $charitableOrganization->governorate_id ?? 'null' }};
        const currentCityId = {{ $charitableOrganization->city_id ?? 'null' }};
        const currentAreaId = {{ $charitableOrganization->area_id ?? 'null' }};
        const oldGovernorateId = {{ old('governorate_id', 'null') }};
        const oldCityId = {{ old('city_id', 'null') }};
        const oldAreaId = {{ old('area_id', 'null') }};

        // Load cities when governorate changes
        $('#governorate_id').on('change', function() {
            const governorateId = $(this).val();
            const citySelect = $('#city_id');
            const areaSelect = $('#area_id');
            
            citySelect.empty().append('<option value="">{{ app()->getLocale() === "ar" ? "اختر المدينة" : "Select City" }}</option>');
            areaSelect.empty().append('<option value="">{{ app()->getLocale() === "ar" ? "اختر المنطقة" : "Select Area" }}</option>');
            
            if (governorateId) {
                $.ajax({
                    url: '/api/cities',
                    method: 'GET',
                    data: { governorate_id: governorateId },
                    success: function(response) {
                        if (response.data) {
                            response.data.forEach(function(city) {
                                const option = $('<option></option>')
                                    .attr('value', city.id)
                                    .text(isRTL ? (city.name_ar || city.name) : (city.name || city.name_ar));
                                const selectedCityId = oldCityId || currentCityId;
                                if (selectedCityId && city.id == selectedCityId) {
                                    option.attr('selected', 'selected');
                                }
                                citySelect.append(option);
                            });
                            
                            // If city was selected, trigger city change to load areas
                            const selectedCityId = oldCityId || currentCityId;
                            if (selectedCityId) {
                                citySelect.trigger('change');
                            }
                        }
                    },
                    error: function() {
                        console.error('Failed to load cities');
                    }
                });
            }
        });

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
                                const option = $('<option></option>')
                                    .attr('value', area.id)
                                    .text(isRTL ? (area.name_ar || area.name) : (area.name || area.name_ar));
                                const selectedAreaId = oldAreaId || currentAreaId;
                                if (selectedAreaId && area.id == selectedAreaId) {
                                    option.attr('selected', 'selected');
                                }
                                areaSelect.append(option);
                            });
                        }
                    },
                    error: function() {
                        console.error('Failed to load areas');
                    }
                });
            }
        });

        // Trigger governorate change on load if governorate is selected
        const selectedGovernorateId = oldGovernorateId || currentGovernorateId;
        if (selectedGovernorateId) {
            $('#governorate_id').trigger('change');
        }
    });

    function addPhoneNumber() {
        const container = document.getElementById('phone-numbers-container');
        const newItem = document.createElement('div');
        newItem.className = 'flex items-center gap-2 phone-number-item';
        newItem.innerHTML = `
            <input type="text" name="phone_numbers[]" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="{{ app()->getLocale() === 'ar' ? 'مثال: 01234567890' : 'e.g. 01234567890' }}" />
            <button type="button" onclick="removePhoneNumber(this)" class="px-3 py-2 text-sm text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                {{ app()->getLocale() === 'ar' ? 'حذف' : 'Remove' }}
            </button>
        `;
        container.appendChild(newItem);
    }

    function removePhoneNumber(button) {
        const container = document.getElementById('phone-numbers-container');
        if (container.children.length > 1) {
            button.closest('.phone-number-item').remove();
        }
    }

    function addService() {
        const container = document.getElementById('services-container');
        const newItem = document.createElement('div');
        newItem.className = 'flex items-center gap-2 service-item';
        newItem.innerHTML = `
            <input type="text" name="services[]" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="{{ app()->getLocale() === 'ar' ? 'مثال: تقديم مساعدات غذائية' : 'e.g. Food Assistance' }}" />
            <button type="button" onclick="removeService(this)" class="px-3 py-2 text-sm text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors">
                {{ app()->getLocale() === 'ar' ? 'حذف' : 'Remove' }}
            </button>
        `;
        container.appendChild(newItem);
    }

    function removeService(button) {
        const container = document.getElementById('services-container');
        if (container.children.length > 1) {
            button.closest('.service-item').remove();
        }
    }
</script>
@endpush
