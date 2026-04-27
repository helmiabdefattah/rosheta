@extends('pharmacies.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'العروض المقبولة' : 'Accepted Offers')
@section('page-title', app()->getLocale() === 'ar' ? 'العروض المقبولة' : 'Accepted Offers')

@section('content')
@php $l = app()->getLocale() === 'ar'; @endphp
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
	<div class="p-4 sm:p-6 border-b">
		<h3 class="text-lg font-semibold text-slate-900">{{ $l ? 'العروض المقبولة' : 'Accepted Offers' }}</h3>
	</div>
	<div class="p-0">
		@if($offers->count())
			<div class="overflow-x-auto lg:overflow-x-visible p-2 sm:p-0">
			<table class="min-w-full divide-y divide-gray-200 stack-table-mobile">
				<thead class="bg-gray-50">
				<tr>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $l ? 'الطلب' : 'Request' }}</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $l ? 'العميل' : 'Client' }}</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $l ? 'الإجمالي' : 'Total' }}</th>
					<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ $l ? 'الحالة' : 'Status' }}</th>
				</tr>
				</thead>
				<tbody class="bg-white divide-y divide-gray-200">
				@foreach($offers as $offer)
					<tr>
						<td class="px-6 py-3 text-sm text-slate-800" data-label="{{ $l ? 'الطلب' : 'Request' }}">#{{ $offer->client_request_id }}</td>
						<td class="px-6 py-3 text-sm text-slate-700" data-label="{{ $l ? 'العميل' : 'Client' }}">{{ $offer->request?->client?->name ?? 'N/A' }}</td>
						<td class="px-6 py-3 text-sm font-semibold" data-label="{{ $l ? 'الإجمالي' : 'Total' }}">{{ number_format($offer->total_price, 2) }}</td>
						<td class="px-6 py-3" data-label="{{ $l ? 'الحالة' : 'Status' }}">
							<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">{{ ucfirst($offer->status) }}</span>
						</td>
					</tr>
				@endforeach
				</tbody>
			</table>
			</div>
			<div class="p-4 border-t">
				{{ $offers->links() }}
			</div>
		@else
			<div class="p-8 text-center text-slate-500">
				{{ $l ? 'لا توجد عروض' : 'No offers found.' }}
			</div>
		@endif
	</div>
</div>
@endsection

