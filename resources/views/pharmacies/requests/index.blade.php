@extends('pharmacies.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'طلبات الأدوية' : 'Medicine Requests')
@section('page-title', app()->getLocale() === 'ar' ? 'طلبات الأدوية' : 'Medicine Requests')

@section('content')
@php
	$l = app()->getLocale() === 'ar';
@endphp
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
	<div class="p-4 sm:p-6 border-b flex items-center justify-between">
		<h3 class="text-lg font-semibold text-slate-900">{{ $l ? 'الطلبات المتاحة' : 'Available Requests' }}</h3>
	</div>
	<div class="p-0">
		@if($requests->count())
			<div class="overflow-x-auto lg:overflow-x-visible p-2 sm:p-0">
			<table class="min-w-full divide-y divide-gray-200 stack-table-mobile">
				<thead class="bg-gray-50">
				<tr>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $l ? 'رقم الطلب' : 'Request' }}</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $l ? 'العميل' : 'Client' }}</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $l ? 'عدد الأدوية' : 'Medicines' }}</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $l ? 'العنوان' : 'Address' }}</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $l ? 'الإجراءات' : 'Actions' }}</th>
				</tr>
				</thead>
				<tbody class="bg-white divide-y divide-gray-200">
				@foreach($requests as $req)
					@php
						$isForThisPharmacy = $req->model_type === 'App\Models\Pharmacy' && $req->model_id == $pharmacy->id;
						$addr = '-';
						if ($req->address) {
							$parts = [];
							if ($req->address->address) $parts[] = $req->address->address;
							if ($req->address->area?->name) $parts[] = $req->address->area->name;
							if ($req->address->city?->name) $parts[] = $req->address->city->name;
							$addr = implode(', ', $parts);
						}
					@endphp
					<tr class="{{ $isForThisPharmacy ? 'bg-green-50 border-l-4 border-green-500 lg:border-l-4' : '' }}">
						<td class="px-6 py-3 text-sm text-slate-800" data-label="{{ $l ? 'رقم الطلب' : 'Request' }}">
							<div class="flex items-center gap-2 flex-wrap">
								<span>#{{ $req->id }}</span>
								@if($isForThisPharmacy)
									<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-600 text-white">
										{{ $l ? 'خاص بك' : 'For You' }}
									</span>
								@endif
							</div>
						</td>
						<td class="px-6 py-3 text-sm text-slate-700" data-label="{{ $l ? 'العميل' : 'Client' }}">{{ $req->client->name ?? 'N/A' }}<div class="text-xs text-slate-500">{{ $req->client->phone_number ?? '' }}</div></td>
						<td class="px-6 py-3" data-label="{{ $l ? 'عدد الأدوية' : 'Medicines' }}"><span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">{{ $req->lines->where('item_type','medicine')->count() }}</span></td>
						<td class="px-6 py-3 text-sm text-slate-600" data-label="{{ $l ? 'العنوان' : 'Address' }}">{{ $addr }}</td>
						<td class="px-6 py-3 stack-td-actions" data-label="{{ $l ? 'الإجراءات' : 'Actions' }}">
							<a href="{{ route('offers.create', ['request' => $req->id]) }}" class="inline-block px-3 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 text-center w-full sm:w-auto">
								{{ $l ? 'إنشاء عرض' : 'Make Offer' }}
							</a>
						</td>
					</tr>
				@endforeach
				</tbody>
			</table>
			</div>
			<div class="p-4 border-t">
				{{ $requests->links() }}
			</div>
		@else
			<div class="p-8 text-center text-slate-500">
				{{ $l ? 'لا توجد طلبات متاحة حالياً.' : 'No requests at the moment.' }}
			</div>
		@endif
	</div>
</div>
@endsection

