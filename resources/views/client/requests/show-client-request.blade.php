@extends('client.layouts.dashboard')

@php
	$isAr = app()->getLocale() === 'ar';
@endphp

@section('title', $isAr ? 'تفاصيل الطلب' : 'Request details')
@section('page-title', $isAr ? 'تفاصيل الطلب' : 'Request details')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
	@if(session('success'))
		<div class="p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">{{ session('success') }}</div>
	@endif

	<div class="flex flex-wrap gap-2">
		<a href="{{ route('client.requests.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-gray-100 text-gray-800 text-sm hover:bg-gray-200">
			{{ $isAr ? '← جميع الطلبات' : '← All requests' }}
		</a>
		@if($canMutate)
			<a href="{{ route('client.requests.pharmacy-lab.edit', $requestModel) }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700">
				{{ $isAr ? 'تعديل' : 'Edit' }}
			</a>
			<form action="{{ route('client.requests.pharmacy-lab.destroy', $requestModel) }}" method="POST" class="inline" onsubmit="return confirm(@json($isAr ? 'حذف هذا الطلب؟' : 'Delete this request?'));">
				@csrf
				@method('DELETE')
				<button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white text-sm hover:bg-red-700">
					{{ $isAr ? 'حذف' : 'Delete' }}
				</button>
			</form>
		@endif
		<a href="{{ route('client.offers.index') }}" class="inline-flex items-center px-4 py-2 rounded-lg bg-primary/10 text-primary text-sm hover:bg-primary/20">
			{{ $isAr ? 'العروض' : 'Offers' }}
		</a>
	</div>

	<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-4">
		<div class="flex flex-wrap gap-2 items-center">
			<span class="text-xs text-gray-500">#{{ $requestModel->id }}</span>
			<span class="px-2 py-1 rounded text-xs font-medium bg-slate-100">{{ $requestModel->type }}</span>
			<span class="px-2 py-1 rounded text-xs font-medium bg-amber-50 text-amber-900">{{ ucfirst($requestModel->status) }}</span>
		</div>

		@if($requestModel->note)
			<div>
				<h3 class="text-sm font-semibold text-gray-700 mb-1">{{ $isAr ? 'ملاحظات' : 'Notes' }}</h3>
				<p class="text-gray-800 whitespace-pre-wrap">{{ $requestModel->note }}</p>
			</div>
		@endif

		@if($requestModel->address)
			<div>
				<h3 class="text-sm font-semibold text-gray-700 mb-1">{{ $isAr ? 'العنوان' : 'Address' }}</h3>
				<p class="text-gray-800">{{ $requestModel->address->address }}
					@if($requestModel->address->area) — {{ $requestModel->address->area->name }} @endif
				</p>
			</div>
		@endif

		<div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
			<div><span class="text-gray-500">{{ $isAr ? 'حامل' : 'Pregnant' }}:</span> {{ $requestModel->pregnant ? ($isAr ? 'نعم' : 'Yes') : ($isAr ? 'لا' : 'No') }}</div>
			<div><span class="text-gray-500">{{ $isAr ? 'سكري' : 'Diabetic' }}:</span> {{ $requestModel->diabetic ? ($isAr ? 'نعم' : 'Yes') : ($isAr ? 'لا' : 'No') }}</div>
			<div><span class="text-gray-500">{{ $isAr ? 'قلب' : 'Heart' }}:</span> {{ $requestModel->heart_patient ? ($isAr ? 'نعم' : 'Yes') : ($isAr ? 'لا' : 'No') }}</div>
			<div><span class="text-gray-500">{{ $isAr ? 'ضغط' : 'BP' }}:</span> {{ $requestModel->high_blood_pressure ? ($isAr ? 'نعم' : 'Yes') : ($isAr ? 'لا' : 'No') }}</div>
		</div>

		@if($requestModel->lines->isNotEmpty())
			<div>
				<h3 class="text-sm font-semibold text-gray-700 mb-2">{{ $isAr ? 'البنود' : 'Items' }}</h3>
				<ul class="list-disc list-inside text-sm text-gray-800 space-y-1">
					@foreach($requestModel->lines as $line)
						<li>
							@if($line->item_type === 'medicine' && $line->medicine)
								{{ $line->medicine->name }} × {{ $line->quantity }} {{ $line->unit }}
							@elseif(in_array($line->item_type, ['test', 'radiology'], true) && $line->medicalTest)
								{{ app()->getLocale() === 'ar' ? ($line->medicalTest->test_name_ar ?? $line->medicalTest->test_name_en) : $line->medicalTest->test_name_en }}
							@else
								#{{ $line->id }}
							@endif
						</li>
					@endforeach
				</ul>
			</div>
		@endif
	</div>
</div>
@endsection
