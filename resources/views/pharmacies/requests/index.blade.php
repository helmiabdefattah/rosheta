@extends('pharmacies.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'طلبات الأدوية' : 'Medicine Requests')
@section('page-title', app()->getLocale() === 'ar' ? 'طلبات الأدوية' : 'Medicine Requests')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
	<div class="p-6 border-b flex items-center justify-between">
		<h3 class="text-lg font-semibold text-slate-900">{{ app()->getLocale() === 'ar' ? 'الطلبات المتاحة' : 'Available Requests' }}</h3>
	</div>
	<div class="p-0">
		@if($requests->count())
			<table class="min-w-full divide-y divide-gray-200">
				<thead class="bg-gray-50">
				<tr>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ app()->getLocale() === 'ar' ? 'رقم الطلب' : 'Request' }}</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ app()->getLocale() === 'ar' ? 'العميل' : 'Client' }}</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ app()->getLocale() === 'ar' ? 'عدد الأدوية' : 'Medicines' }}</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ app()->getLocale() === 'ar' ? 'العنوان' : 'Address' }}</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ app()->getLocale() === 'ar' ? 'الإجراءات' : 'Actions' }}</th>
				</tr>
				</thead>
				<tbody class="bg-white divide-y divide-gray-200">
				@foreach($requests as $req)
					@php
						$isForThisPharmacy = $req->model_type === 'App\Models\Pharmacy' && $req->model_id == $pharmacy->id;
					@endphp
					<tr class="{{ $isForThisPharmacy ? 'bg-green-50 border-l-4 border-green-500' : '' }}">
						<td class="px-6 py-3 text-sm text-slate-800">
							<div class="flex items-center gap-2">
								<span>#{{ $req->id }}</span>
								@if($isForThisPharmacy)
									<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-600 text-white">
										{{ app()->getLocale() === 'ar' ? 'خاص بك' : 'For You' }}
									</span>
								@endif
							</div>
						</td>
						<td class="px-6 py-3 text-sm text-slate-700">{{ $req->client->name ?? 'N/A' }}<div class="text-xs text-slate-500">{{ $req->client->phone_number ?? '' }}</div></td>
						<td class="px-6 py-3"><span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">{{ $req->lines->where('item_type','medicine')->count() }}</span></td>
						<td class="px-6 py-3 text-sm text-slate-600">
							@if($req->address)
								@php
									$parts = [];
									if ($req->address->address) $parts[] = $req->address->address;
									if ($req->address->area?->name) $parts[] = $req->address->area->name;
									if ($req->address->city?->name) $parts[] = $req->address->city->name;
								@endphp
								{{ implode(', ', $parts) }}
							@else
								-
							@endif
						</td>
						<td class="px-6 py-3">
							<a href="{{ route('offers.create', ['request' => $req->id]) }}" class="px-3 py-1 text-sm bg-primary text-white rounded">
								{{ app()->getLocale() === 'ar' ? 'إنشاء عرض' : 'Make Offer' }}
							</a>
						</td>
					</tr>
				@endforeach
				</tbody>
			</table>
			<div class="p-4 border-t">
				{{ $requests->links() }}
			</div>
		@else
			<div class="p-8 text-center text-slate-500">
				{{ app()->getLocale() === 'ar' ? 'لا توجد طلبات متاحة حالياً.' : 'No requests at the moment.' }}
			</div>
		@endif
	</div>
</div>
@endsection


