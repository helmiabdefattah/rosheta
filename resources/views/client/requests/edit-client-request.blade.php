@extends('client.layouts.dashboard')

@php
	$isAr = app()->getLocale() === 'ar';
@endphp

@section('title', $isAr ? 'تعديل الطلب' : 'Edit request')
@section('page-title', $isAr ? 'تعديل الطلب' : 'Edit request')
@section('page-description', $isAr ? 'تحديث الملاحظات والعنوان والتأمين (الطلب قيد الانتظار فقط)' : 'Update notes, address, and insurance (pending requests only)')

@section('content')
<div class="max-w-3xl mx-auto">
	<form method="POST" action="{{ route('client.requests.pharmacy-lab.update', $requestModel) }}" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-6">
		@csrf
		@method('PUT')

		<div>
			<label class="block text-sm font-medium text-gray-700 mb-2">{{ $isAr ? 'ملاحظات' : 'Notes' }}</label>
			<textarea name="note" rows="4" class="w-full border border-gray-300 rounded-lg p-3 text-sm">{{ old('note', $requestModel->note) }}</textarea>
			@error('note')
				<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
			@enderror
		</div>

		<div>
			<label class="block text-sm font-medium text-gray-700 mb-2">{{ $isAr ? 'العنوان' : 'Address' }}</label>
			<select name="client_address_id" class="w-full border border-gray-300 rounded-lg p-3 text-sm">
				<option value="">{{ $isAr ? '— بدون —' : '— None —' }}</option>
				@foreach($addresses as $addr)
					<option value="{{ $addr->id }}" @selected(old('client_address_id', $requestModel->client_address_id) == $addr->id)>
						{{ $addr->address }} @if($addr->area) — {{ $addr->area->name }} @endif
					</option>
				@endforeach
			</select>
			@error('client_address_id')
				<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
			@enderror
		</div>

		<div>
			<label class="block text-sm font-medium text-gray-700 mb-2">{{ $isAr ? 'شركة التأمين' : 'Insurance' }}</label>
			<select name="insurance_company_id" id="insurance_company_id" class="w-full border border-gray-300 rounded-lg p-3 text-sm">
				<option value="">{{ $isAr ? 'لا يوجد' : 'None' }}</option>
				@foreach($insuranceCompanies as $company)
					<option value="{{ $company->id }}" @selected(old('insurance_company_id', $requestModel->insurance_company_id) == $company->id)>
						{{ $isAr ? ($company->name_ar ?? $company->name) : $company->name }}
					</option>
				@endforeach
				<option value="new">{{ $isAr ? '+ شركة جديدة' : '+ New company' }}</option>
			</select>
			<div id="new_insurance_company_container" class="mt-2 {{ old('insurance_company_name') ? '' : 'hidden' }}">
				<input type="text" name="insurance_company_name" value="{{ old('insurance_company_name') }}"
					class="w-full border border-gray-300 rounded-lg p-3 text-sm"
					placeholder="{{ $isAr ? 'اسم شركة التأمين' : 'Insurance company name' }}">
			</div>
			@error('insurance_company_id')
				<p class="mt-1 text-sm text-red-600">{{ $message }}</p>
			@enderror
		</div>

		<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
			<label class="flex items-center gap-2 text-sm text-gray-800">
				<input type="hidden" name="pregnant" value="0">
				<input type="checkbox" name="pregnant" value="1" class="rounded border-gray-300" @checked(old('pregnant', $requestModel->pregnant))>
				{{ $isAr ? 'حامل' : 'Pregnant' }}
			</label>
			<label class="flex items-center gap-2 text-sm text-gray-800">
				<input type="hidden" name="diabetic" value="0">
				<input type="checkbox" name="diabetic" value="1" class="rounded border-gray-300" @checked(old('diabetic', $requestModel->diabetic))>
				{{ $isAr ? 'سكري' : 'Diabetic' }}
			</label>
			<label class="flex items-center gap-2 text-sm text-gray-800">
				<input type="hidden" name="heart_patient" value="0">
				<input type="checkbox" name="heart_patient" value="1" class="rounded border-gray-300" @checked(old('heart_patient', $requestModel->heart_patient))>
				{{ $isAr ? 'مريض قلب' : 'Heart patient' }}
			</label>
			<label class="flex items-center gap-2 text-sm text-gray-800">
				<input type="hidden" name="high_blood_pressure" value="0">
				<input type="checkbox" name="high_blood_pressure" value="1" class="rounded border-gray-300" @checked(old('high_blood_pressure', $requestModel->high_blood_pressure))>
				{{ $isAr ? 'ضغط مرتفع' : 'High blood pressure' }}
			</label>
		</div>

		<p class="text-xs text-gray-500">
			{{ $isAr ? 'لا يمكن تعديل قائمة الأدوية أو التحاليل من هنا؛ يمكنك حذف الطلب وإنشاء طلب جديد إذا لزم.' : 'Medicine/test line items cannot be changed here; delete this request and create a new one if needed.' }}
		</p>

		<div class="flex flex-wrap gap-3 justify-end pt-4 border-t border-gray-200">
			<a href="{{ route('client.requests.pharmacy-lab.show', $requestModel) }}" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm">
				{{ $isAr ? 'إلغاء' : 'Cancel' }}
			</a>
			<button type="submit" class="px-5 py-2.5 rounded-lg bg-green-600 text-white hover:bg-green-700 text-sm font-medium">
				{{ $isAr ? 'حفظ' : 'Save' }}
			</button>
		</div>
	</form>
</div>
@endsection

@push('scripts')
<script>
	document.getElementById('insurance_company_id')?.addEventListener('change', function () {
		const box = document.getElementById('new_insurance_company_container');
		if (!box) return;
		if (this.value === 'new') {
			box.classList.remove('hidden');
			this.value = '';
		} else {
			box.classList.add('hidden');
		}
	});
</script>
@endpush
