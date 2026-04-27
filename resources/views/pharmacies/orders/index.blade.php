@extends('pharmacies.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'طلبات الصيدلية' : 'Pharmacy Orders')
@section('page-title', app()->getLocale() === 'ar' ? 'إدارة الطلبات' : 'Manage Orders')

@section('content')
@php $l = app()->getLocale() === 'ar'; @endphp
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-6">
	<div class="p-4 sm:p-6 border-b flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
		<h3 class="text-lg font-semibold text-slate-900 shrink-0">{{ $l ? 'طلبات الصيدلية' : 'Pharmacy Orders' }}</h3>
		<div class="stack-toolbar-mobile flex flex-col w-full lg:w-auto lg:items-center gap-3">
			<form method="GET" action="{{ route('pharmacies.orders.index') }}" class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-2 w-full">
				<select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary w-full sm:w-auto min-w-0">
					<option value="">{{ $l ? 'جميع الحالات' : 'All Statuses' }}</option>
					<option value="preparing" {{ request('status') === 'preparing' ? 'selected' : '' }}>
						{{ $l ? 'قيد التحضير' : 'Preparing' }}
					</option>
					<option value="delivering" {{ request('status') === 'delivering' ? 'selected' : '' }}>
						{{ $l ? 'قيد التوصيل' : 'Delivering' }}
					</option>
					<option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>
						{{ $l ? 'تم التوصيل' : 'Delivered' }}
					</option>
				</select>
				<select name="paid" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary w-full sm:w-auto min-w-0">
					<option value="">{{ $l ? 'جميع الطلبات' : 'All Orders' }}</option>
					<option value="1" {{ request('paid') === '1' ? 'selected' : '' }}>
						{{ $l ? 'مدفوع' : 'Paid' }}
					</option>
					<option value="0" {{ request('paid') === '0' ? 'selected' : '' }}>
						{{ $l ? 'غير مدفوع' : 'Unpaid' }}
					</option>
				</select>
				@if(request()->has('status') || request()->has('paid') || request()->has('search'))
					<a href="{{ route('pharmacies.orders.index') }}" class="px-3 py-2 text-sm text-gray-600 hover:text-gray-800 text-center sm:text-start">
						{{ $l ? 'إعادة تعيين' : 'Reset' }}
					</a>
				@endif
				<button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium w-full sm:w-auto">
					{{ $l ? 'تصفية' : 'Filter' }}
				</button>
			</form>
		</div>
	</div>
	<div class="p-0">
		@if($orders->count())
			<div class="overflow-x-auto lg:overflow-x-visible p-2 sm:p-0">
				<table class="min-w-full divide-y divide-gray-200 stack-table-mobile">
					<thead class="bg-gray-50">
						<tr>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'رقم الطلب' : 'Order ID' }}</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'العميل' : 'Client' }}</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الإجمالي' : 'Total Price' }}</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الحالة' : 'Status' }}</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الدفع' : 'Payment' }}</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'تاريخ الإنشاء' : 'Created At' }}</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الإجراءات' : 'Actions' }}</th>
						</tr>
					</thead>
					<tbody class="bg-white divide-y divide-gray-200">
						@foreach($orders as $order)
							@php
								$statusLabels = [
									'preparing' => $l ? 'قيد التحضير' : 'Preparing',
									'delivering' => $l ? 'قيد التوصيل' : 'Delivering',
									'delivered' => $l ? 'تم التوصيل' : 'Delivered',
								];
								$statusColors = [
									'preparing' => 'bg-yellow-100 text-yellow-800',
									'delivering' => 'bg-blue-100 text-blue-800',
									'delivered' => 'bg-green-100 text-green-800',
								];
							@endphp
							<tr class="hover:bg-gray-50">
								<td class="px-6 py-4 whitespace-nowrap" data-label="{{ $l ? 'رقم الطلب' : 'Order ID' }}">
									<span class="text-sm font-semibold text-slate-800">#{{ $order->id }}</span>
								</td>
								<td class="px-6 py-4 whitespace-nowrap" data-label="{{ $l ? 'العميل' : 'Client' }}">
									<div class="text-sm text-slate-800">{{ $order->request?->client?->name ?? 'N/A' }}</div>
									<div class="text-xs text-slate-500">{{ $order->request?->client?->phone_number ?? '' }}</div>
								</td>
								<td class="px-6 py-4 whitespace-nowrap" data-label="{{ $l ? 'الإجمالي' : 'Total Price' }}">
									<span class="text-sm font-semibold text-slate-800">{{ number_format($order->total_price ?? 0, 2) }} {{ $l ? 'ج.م' : 'EGP' }}</span>
								</td>
								<td class="px-6 py-4 whitespace-nowrap" data-label="{{ $l ? 'الحالة' : 'Status' }}">
									<span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
										{{ $statusLabels[$order->status] ?? ucfirst($order->status) }}
									</span>
								</td>
								<td class="px-6 py-4 whitespace-nowrap" data-label="{{ $l ? 'الدفع' : 'Payment' }}">
									@if($order->payed)
										<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
											{{ $l ? 'مدفوع' : 'Paid' }}
										</span>
									@else
										<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
											{{ $l ? 'غير مدفوع' : 'Unpaid' }}
										</span>
									@endif
								</td>
								<td class="px-6 py-4 whitespace-nowrap" data-label="{{ $l ? 'تاريخ الإنشاء' : 'Created At' }}">
									<span class="text-sm text-slate-600">{{ $order->created_at->format('Y-m-d H:i') }}</span>
								</td>
								<td class="px-6 py-4 whitespace-nowrap stack-td-actions" data-label="{{ $l ? 'الإجراءات' : 'Actions' }}">
									<div class="flex items-center gap-2">
										<form method="POST" action="{{ route('pharmacies.orders.update-status', $order) }}" class="inline-flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full">
											@csrf
											@method('PUT')
											<select name="status" class="px-2 py-2 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary w-full sm:w-auto min-w-0">
												<option value="preparing" {{ $order->status === 'preparing' ? 'selected' : '' }}>
													{{ $l ? 'قيد التحضير' : 'Preparing' }}
												</option>
												<option value="delivering" {{ $order->status === 'delivering' ? 'selected' : '' }}>
													{{ $l ? 'قيد التوصيل' : 'Delivering' }}
												</option>
												<option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>
													{{ $l ? 'تم التوصيل' : 'Delivered' }}
												</option>
											</select>
											<button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded text-xs hover:bg-blue-700 whitespace-nowrap">
												{{ $l ? 'تحديث' : 'Update' }}
											</button>
										</form>
										@if(!$order->payed)
											<form method="POST" action="{{ route('pharmacies.orders.mark-paid', $order) }}" class="inline w-full sm:w-auto">
												@csrf
												@method('PUT')
												<button type="submit" class="w-full sm:w-auto px-3 py-2 bg-green-600 text-white rounded text-xs hover:bg-green-700">
													{{ $l ? 'تحديد كمدفوع' : 'Mark Paid' }}
												</button>
											</form>
										@endif
									</div>
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
			<div class="p-4 border-t">
				{{ $orders->links() }}
			</div>
		@else
			<div class="p-8 text-center text-slate-500">
				{{ $l ? 'لا توجد طلبات' : 'No orders found.' }}
			</div>
		@endif
	</div>
</div>
@endsection
