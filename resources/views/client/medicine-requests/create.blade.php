@extends('client.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? ' طلب أدوية' : ' Medicine Request')
@section('page-title', app()->getLocale() === 'ar' ? ' طلب أدوية' : ' Medicine Request')
@section('page-description', app()->getLocale() === 'ar' ? 'ارفع صور الروشتة أو أضف ملاحظات لطلب الأدوية' : 'Upload prescription images or add notes to request medicines')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
	.select2-container--default .select2-selection--single {
		height: 42px;
		border: 1px solid #d1d5db;
		border-radius: 0.5rem;
	}
	.select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 42px; padding-left: 12px; }
	.select2-container--default .select2-selection--single .select2-selection__arrow { height: 40px; }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto">
	<form method="POST" action="{{ route('client.test-requests.store', 'medicine') }}" enctype="multipart/form-data" id="medicineRequestForm">
		@csrf

		@php
			$pharmacy = null;
			if(request()->has('pharmacy_id')) {
				$pharmacy = \App\Models\Pharmacy::where('id', request('pharmacy_id'))
					->where('is_active', true)
					->with(['area.city.governorate'])
					->first();
			}
		@endphp

		@if($pharmacy)
			<!-- Selected Pharmacy Info (Read-only) -->
			<div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
				<h3 class="text-lg font-semibold text-green-900 mb-4">
					{{ app()->getLocale() === 'ar' ? 'الصيدلية المحددة' : 'Selected Pharmacy' }}
				</h3>
				<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
					<div>
						<label class="block text-sm font-medium text-green-800 mb-1">
							{{ app()->getLocale() === 'ar' ? 'اسم الصيدلية' : 'Pharmacy Name' }}
						</label>
						<p class="text-green-900 font-semibold">{{ $pharmacy->name }}</p>
					</div>
					@if($pharmacy->phone)
						<div>
							<label class="block text-sm font-medium text-green-800 mb-1">
								{{ app()->getLocale() === 'ar' ? 'الهاتف' : 'Phone' }}
							</label>
							<p class="text-green-900">{{ $pharmacy->phone }}</p>
						</div>
					@endif
					@if($pharmacy->area)
						<div>
							<label class="block text-sm font-medium text-green-800 mb-1">
								{{ app()->getLocale() === 'ar' ? 'الموقع' : 'Location' }}
							</label>
							<p class="text-green-900">
								{{ app()->getLocale() === 'ar' ? $pharmacy->area->name_ar : $pharmacy->area->name }}
								@if($pharmacy->area->city)
									, {{ app()->getLocale() === 'ar' ? $pharmacy->area->city->name_ar : $pharmacy->area->city->name }}
								@endif
							</p>
						</div>
					@endif
					@if($pharmacy->address)
						<div>
							<label class="block text-sm font-medium text-green-800 mb-1">
								{{ app()->getLocale() === 'ar' ? 'العنوان' : 'Address' }}
							</label>
							<p class="text-green-900">{{ $pharmacy->address }}</p>
						</div>
					@endif
				</div>
				<input type="hidden" name="pharmacy_id" value="{{ $pharmacy->id }}">
			</div>
		@endif

		<!-- Address Selection -->
		<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
			<label for="client_address_id" class="block text-sm font-medium text-gray-700 mb-2">
				{{ app()->getLocale() === 'ar' ? 'اختر عنوان التوصيل' : 'Select Delivery Address' }}
				<span class="text-red-500">*</span>
			</label>
			@php $addresses = $addresses ?? (Auth::guard('client')->user()?->addresses()->with(['city','area'])->orderByDesc('id')->get()); @endphp
			@if(empty($addresses) || $addresses->isEmpty())
				<div class="mb-4 p-4 bg-orange-50 border border-orange-200 rounded-lg">
					<p class="text-sm text-orange-800 mb-3">
						{{ app()->getLocale() === 'ar' ? 'لا توجد عناوين. يرجى إضافة عنوان جديد أولاً.' : 'No addresses found. Please add a new address first.' }}
					</p>
					<a href="{{ route('client.addresses.create') }}" target="_blank" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200 text-sm font-medium">
						<i class="bi bi-plus-circle me-2"></i>
						{{ app()->getLocale() === 'ar' ? 'إضافة عنوان جديد' : 'Add New Address' }}
					</a>
				</div>
			@else
				<select
					id="client_address_id"
					name="client_address_id"
					class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary mb-3"
					required
				>
					<option value="">{{ app()->getLocale() === 'ar' ? 'اختر عنوانًا' : 'Select an address' }}</option>
					@foreach($addresses as $address)
						<option value="{{ $address->id }}">
							{{ ($address->city->name ?? '') . ', ' . ($address->area->name ?? '') . ' - ' . ($address->address ?? '') }}
						</option>
					@endforeach
				</select>
				<div class="text-sm text-gray-600">
					<a href="{{ route('client.addresses.create') }}" target="_blank" class="text-primary hover:underline inline-flex items-center gap-1">
						<i class="bi bi-plus-circle"></i>
						{{ app()->getLocale() === 'ar' ? 'إضافة عنوان جديد' : 'Add New Address' }}
					</a>
				</div>
			@endif
			@error('client_address_id')
				<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
			@enderror
		</div>

		<!-- Medical Conditions -->
		<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
			<h3 class="text-lg font-semibold text-gray-800 mb-4">
				{{ app()->getLocale() === 'ar' ? 'الحالات الطبية' : 'Medical Conditions' }}
			</h3>
			<div class="grid grid-cols-2 md:grid-cols-4 gap-4">
				<label class="flex items-center gap-2 cursor-pointer">
					<input type="checkbox" name="pregnant" value="1" class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
					<span class="text-sm text-gray-700">{{ app()->getLocale() === 'ar' ? 'حامل' : 'Pregnant' }}</span>
				</label>
				<label class="flex items-center gap-2 cursor-pointer">
					<input type="checkbox" name="diabetic" value="1" class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
					<span class="text-sm text-gray-700">{{ app()->getLocale() === 'ar' ? 'مريض سكر' : 'Diabetic' }}</span>
				</label>
				<label class="flex items-center gap-2 cursor-pointer">
					<input type="checkbox" name="heart_patient" value="1" class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
					<span class="text-sm text-gray-700">{{ app()->getLocale() === 'ar' ? 'مريض قلب' : 'Heart Patient' }}</span>
				</label>
				<label class="flex items-center gap-2 cursor-pointer">
					<input type="checkbox" name="high_blood_pressure" value="1" class="w-4 h-4 text-primary border-gray-300 rounded focus:ring-primary">
					<span class="text-sm text-gray-700">{{ app()->getLocale() === 'ar' ? 'مريض  ضغط' : 'High Blood Pressure' }}</span>
				</label>
			</div>
		</div>

		<!-- Notes -->
		<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
			<label for="note" class="block text-sm font-medium text-gray-700 mb-2">
				{{ app()->getLocale() === 'ar' ? 'ملاحظات إضافية' : 'Additional Notes' }}
				<span class="text-gray-500 text-xs">({{ app()->getLocale() === 'ar' ? 'اختياري' : 'Optional' }})</span>
			</label>
			<textarea
				id="note"
				name="note"
				rows="3"
				class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
				placeholder="{{ app()->getLocale() === 'ar' ? 'أضف أي ملاحظات إضافية...' : 'Add any additional notes...' }}"
			>{{ old('note') }}</textarea>
		</div>

		<!-- Prescription Images -->
		<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
			<h3 class="text-lg font-semibold text-gray-800 mb-4">
				{{ app()->getLocale() === 'ar' ? 'صور الروشتة الطبية' : 'Prescription Images' }}
				<span class="text-gray-500 text-sm font-normal">({{ app()->getLocale() === 'ar' ? 'اختياري' : 'Optional' }})</span>
			</h3>
			<p class="text-sm text-gray-600 mb-4">
				{{ app()->getLocale() === 'ar'
                    ? 'ارفع صور الروشتة الطبية ليسهل على الصيدليات تجهيز الأدوية بدقة'
                    : 'Upload prescription images to help pharmacies prepare medicines accurately' }}
			</p>
			<input
				type="file"
				id="images"
				name="images[]"
				multiple
				accept="image/*"
				class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
			>
			@error('images.*')
			<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
			@enderror
		</div>

		<!-- OR Separator -->
		<div class="flex items-center gap-4 mb-6">
			<div class="flex-1 border-t border-gray-300"></div>
			<span class="text-gray-500 font-semibold">{{ app()->getLocale() === 'ar' ? 'أو' : 'OR' }}</span>
			<div class="flex-1 border-t border-gray-300"></div>
		</div>

		<!-- Medicines Selection -->
		<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
			<div class="flex items-center justify-between mb-4">
				<h3 class="text-lg font-semibold text-gray-800">
					{{ app()->getLocale() === 'ar' ? 'إدخال الأدوية يدويًا' : 'Add Medicines Manually' }}
				</h3>
				<button type="button" id="addMedicineBtn" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-teal-700 transition duration-200 text-sm font-medium">
					<i class="bi bi-plus-circle me-1"></i>
					{{ app()->getLocale() === 'ar' ? 'إضافة دواء' : 'Add Medicine' }}
				</button>
			</div>
			<p class="text-sm text-gray-600 mb-4">
				{{ app()->getLocale() === 'ar'
                    ? 'ابحث عن الأدوية بالاسم العربي أو الإنجليزي واخترها من القائمة'
                    : 'Search medicines by Arabic or English name and select from the list' }}
			</p>
			<div id="medicinesContainer" class="space-y-4">
				<!-- Medicine rows will be appended here -->
			</div>
		</div>

		<!-- Submit -->
		<div class="flex items-center justify-end gap-4">
			<a href="{{ route('client.dashboard') }}" class="px-6 py-3 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200">
				{{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
			</a>
			<button
				type="submit"
				class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200 font-medium"
			>
				<i class="bi bi-send me-2"></i>
				{{ app()->getLocale() === 'ar' ? 'إرسال الطلب' : 'Submit Request' }}
			</button>
		</div>
		<input type="hidden" name="type" value="medicine">
	</form>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@php
	$medicines = $medicines ?? \App\Models\Medicine::select('id','name','arabic','dosage_form','price')->orderBy('name')->limit(1500)->get();
	$medicinesList = ($medicines ?? collect())->map(function($m){
		return [
			'id' => $m->id,
			'name' => $m->name,
			'arabic' => $m->arabic ?? '',
			'dosage_form' => $m->dosage_form ?? '',
			'price' => $m->price ?? null,
		];
	})->values();
	$placeholderSearch = app()->getLocale() === 'ar' ? 'ابحث واختر دواء' : 'Search and select a medicine';
	$labelMedicine = app()->getLocale() === 'ar' ? 'دواء' : 'Medicine';
	$selectMedicineText = app()->getLocale() === 'ar' ? 'اختر دواء' : 'Select a medicine';
	$addressMedicineAlert = app()->getLocale() === 'ar'
		? 'الرجاء اختيار عنوان والتأكد من إضافة دواء واحد على الأقل أو رفع صورة روشتة.'
		: 'Please select an address and ensure at least one medicine is added or upload a prescription image.';
	$qtyLabel = app()->getLocale() === 'ar' ? 'الكمية' : 'Quantity';
	$unitLabel = app()->getLocale() === 'ar' ? 'الوحدة' : 'Unit';
@endphp
<script>
	(function($){
		'use strict';
		const medicines = @json($medicinesList);
		const placeholderSearch = @json($placeholderSearch);
		const labelMedicine = @json($labelMedicine);
		const selectMedicineText = @json($selectMedicineText);
		const addressMedicineAlert = @json($addressMedicineAlert);
		const qtyLabel = @json($qtyLabel);
		const unitLabel = @json($unitLabel);

		function getUnitsForDosageForm(dosageForm) {
			if (!dosageForm) return ['box','pack','piece'];
			const f = (dosageForm || '').toLowerCase();
			if (f.includes('tablet') || f.includes('tab') || f.includes('قرص')) return ['tablet','strip','box'];
			if (f.includes('capsule') || f.includes('cap') || f.includes('كبسولة')) return ['capsule','strip','box'];
			if (f.includes('syrup') || f.includes('suspension') || f.includes('شراب')) return ['bottle','ml','box'];
			if (f.includes('drop') || f.includes('قطرة')) return ['bottle','ml','drop'];
			if (f.includes('ointment') || f.includes('cream') || f.includes('مرهم') || f.includes('كريم')) return ['tube','gram','piece'];
			if (f.includes('injection') || f.includes('vial') || f.includes('amp') || f.includes('ampoule')) return ['vial','ampoule','box'];
			if (f.includes('spray')) return ['bottle','spray'];
			return ['box','pack','piece'];
		}

		function buildUnitOptions(units) {
			return units.map(u => `<option value="${u}">${u}</option>`).join('');
		}

		function updateRowUnits($row, dosageForm) {
			const units = getUnitsForDosageForm(dosageForm);
			$row.find('.medicine-unit').html(buildUnitOptions(units));
		}

		let medCounter = 0;
		function addMedicineRow() {
			medCounter++;
			const rowId = `med_${medCounter}`;
			const html = `
				<div class="medicine-item border border-gray-200 rounded-lg p-4" data-med-id="${rowId}">
					<div class="flex items-center justify-between mb-3">
						<span class="text-sm font-medium text-gray-700">${labelMedicine} ${medCounter}</span>
						<button type="button" class="remove-med-btn text-red-600 hover:text-red-800">
							<i class="bi bi-trash"></i>
						</button>
					</div>
					<div class="grid grid-cols-1 md:grid-cols-3 gap-3">
						<div class="md:col-span-2 min-w-0">
                <select class="medicine-select w-full" name="medicines[${medCounter - 1}][medicine_id]">
								<option value="">${selectMedicineText}</option>
							</select>
						</div>
						<div class="grid grid-cols-1 sm:grid-cols-2 gap-2 md:col-span-1 mt-2 md:mt-0">
							<div>
								<label class="block text-xs text-gray-600 mb-1">${qtyLabel}</label>
								<input type="number" min="1" value="1" name="medicines[${medCounter - 1}][quantity]" class="medicine-qty w-full px-3 py-2 border border-gray-300 rounded-lg">
							</div>
							<div>
								<label class="block text-xs text-gray-600 mb-1">${unitLabel}</label>
								<select name="medicines[${medCounter - 1}][unit]" class="medicine-unit w-full px-3 py-2 border border-gray-300 rounded-lg">
									${buildUnitOptions(['box','pack','piece'])}
								</select>
							</div>
						</div>
					</div>
				</div>
			`;
			$('#medicinesContainer').append(html);
			const $select = $(`.medicine-item[data-med-id="${rowId}"] .medicine-select`);
			const $row = $(`.medicine-item[data-med-id="${rowId}"]`);
			const data = medicines.map(m => ({
				id: m.id,
				text: `${m.name || ''}${m.arabic ? ' - ' + m.arabic : ''}${m.dosage_form ? ' (' + m.dosage_form + ')' : ''}`
			}));
			$select.select2({
				data,
				placeholder: placeholderSearch,
				allowClear: true,
				width: '100%'
			});
			$select.on('select2:select', function (e) {
				const medId = $(this).val();
				const med = medicines.find(mm => String(mm.id) === String(medId));
				updateRowUnits($row, med ? med.dosage_form : '');
			});
		}
		$(document).on('click','#addMedicineBtn', addMedicineRow);
		$(document).on('click','.remove-med-btn', function(){ $(this).closest('.medicine-item').remove(); });
		// Start with several empty manual rows (empty rows are ignored on submit server-side).
		for (let i = 0; i < 1; i++) {
			addMedicineRow();
		}

		// Basic submit validation: need address and (at least one medicine or an image)
		$('#medicineRequestForm').on('submit', function(e){
			const hasAddress = $('#client_address_id').length ? !!$('#client_address_id').val() : true;
			const selectedMeds = $('.medicine-select').filter(function(){ return $(this).val(); }).length;
			const hasImages = $('#images').length ? ($('#images')[0].files.length > 0) : false;
			if (!hasAddress || (selectedMeds === 0 && !hasImages)) {
				e.preventDefault();
				alert(addressMedicineAlert);
				return false;
			}
			// ensure quantities are valid if medicines selected
			let validQty = true;
			$('.medicine-select').each(function(idx){
				if ($(this).val()) {
					const qty = $(this).closest('.medicine-item').find('.medicine-qty').val();
					if (!qty || parseInt(qty) <= 0) {
						validQty = false;
					}
				}
			});
			if (!validQty) {
				e.preventDefault();
				alert('{{ app()->getLocale() === 'ar' ? 'الرجاء إدخال كمية صالحة لكل دواء.' : 'Please enter a valid quantity for each selected medicine.' }}');
				return false;
			}
		});
	})(jQuery);
</script>
@endpush


