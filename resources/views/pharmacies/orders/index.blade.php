@extends('pharmacies.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'طلبات الصيدلية' : 'Pharmacy Orders')
@section('page-title', app()->getLocale() === 'ar' ? 'إدارة الطلبات' : 'Manage Orders')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-6">
	<div class="p-6 border-b flex items-center justify-between">
		<h3 class="text-lg font-semibold text-slate-900">{{ app()->getLocale() === 'ar' ? 'طلبات الصيدلية' : 'Pharmacy Orders' }}</h3>
		<!-- Filters -->
		<div class="flex items-center gap-3">
			<form method="GET" action="{{ route('pharmacies.orders.index') }}" class="flex items-center gap-2">
				<select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary">
					<option value="">{{ app()->getLocale() === 'ar' ? 'جميع الحالات' : 'All Statuses' }}</option>
					<option value="preparing" {{ request('status') === 'preparing' ? 'selected' : '' }}>
						{{ app()->getLocale() === 'ar' ? 'قيد التحضير' : 'Preparing' }}
					</option>
					<option value="delivering" {{ request('status') === 'delivering' ? 'selected' : '' }}>
						{{ app()->getLocale() === 'ar' ? 'قيد التوصيل' : 'Delivering' }}
					</option>
					<option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>
						{{ app()->getLocale() === 'ar' ? 'تم التوصيل' : 'Delivered' }}
					</option>
				</select>
				<select name="paid" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary focus:border-primary">
					<option value="">{{ app()->getLocale() === 'ar' ? 'جميع الطلبات' : 'All Orders' }}</option>
					<option value="1" {{ request('paid') === '1' ? 'selected' : '' }}>
						{{ app()->getLocale() === 'ar' ? 'مدفوع' : 'Paid' }}
					</option>
					<option value="0" {{ request('paid') === '0' ? 'selected' : '' }}>
						{{ app()->getLocale() === 'ar' ? 'غير مدفوع' : 'Unpaid' }}
					</option>
				</select>
				@if(request()->has('status') || request()->has('paid') || request()->has('search'))
					<a href="{{ route('pharmacies.orders.index') }}" class="px-3 py-2 text-sm text-gray-600 hover:text-gray-800">
						{{ app()->getLocale() === 'ar' ? 'إعادة تعيين' : 'Reset' }}
					</a>
				@endif
				<button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-teal-700 text-sm font-medium">
					{{ app()->getLocale() === 'ar' ? 'تصفية' : 'Filter' }}
				</button>
			</form>
		</div>
	</div>
	<div class="p-0">
		@if($orders->count())
			<div class="overflow-x-auto">
				<table class="min-w-full divide-y divide-gray-200">
					<thead class="bg-gray-50">
						<tr>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
								{{ app()->getLocale() === 'ar' ? 'رقم الطلب' : 'Order ID' }}
							</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
								{{ app()->getLocale() === 'ar' ? 'العميل' : 'Client' }}
							</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
								{{ app()->getLocale() === 'ar' ? 'الإجمالي' : 'Total Price' }}
							</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
								{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}
							</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
								{{ app()->getLocale() === 'ar' ? 'الدفع' : 'Payment' }}
							</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
								{{ app()->getLocale() === 'ar' ? 'تاريخ الإنشاء' : 'Created At' }}
							</th>
							<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
								{{ app()->getLocale() === 'ar' ? 'الإجراءات' : 'Actions' }}
							</th>
						</tr>
					</thead>
					<tbody class="bg-white divide-y divide-gray-200">
						@foreach($orders as $order)
							<tr class="hover:bg-gray-50">
								<td class="px-6 py-4 whitespace-nowrap">
									<span class="text-sm font-semibold text-slate-800">#{{ $order->id }}</span>
								</td>
								<td class="px-6 py-4 whitespace-nowrap">
									<div class="text-sm text-slate-800">{{ $order->request?->client?->name ?? 'N/A' }}</div>
									<div class="text-xs text-slate-500">{{ $order->request?->client?->phone_number ?? '' }}</div>
								</td>
								<td class="px-6 py-4 whitespace-nowrap">
									<span class="text-sm font-semibold text-slate-800">{{ number_format($order->total_price ?? 0, 2) }} {{ app()->getLocale() === 'ar' ? 'ج.م' : 'EGP' }}</span>
								</td>
								<td class="px-6 py-4 whitespace-nowrap">
									@php
										$statusLabels = [
											'preparing' => app()->getLocale() === 'ar' ? 'قيد التحضير' : 'Preparing',
											'delivering' => app()->getLocale() === 'ar' ? 'قيد التوصيل' : 'Delivering',
											'delivered' => app()->getLocale() === 'ar' ? 'تم التوصيل' : 'Delivered',
										];
										$statusColors = [
											'preparing' => 'bg-yellow-100 text-yellow-800',
											'delivering' => 'bg-blue-100 text-blue-800',
											'delivered' => 'bg-green-100 text-green-800',
										];
									@endphp
									<span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800' }}">
										{{ $statusLabels[$order->status] ?? ucfirst($order->status) }}
									</span>
								</td>
								<td class="px-6 py-4 whitespace-nowrap">
									@if($order->payed)
										<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
											{{ app()->getLocale() === 'ar' ? 'مدفوع' : 'Paid' }}
										</span>
									@else
										<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
											{{ app()->getLocale() === 'ar' ? 'غير مدفوع' : 'Unpaid' }}
										</span>
									@endif
								</td>
								<td class="px-6 py-4 whitespace-nowrap">
									<span class="text-sm text-slate-600">{{ $order->created_at->format('Y-m-d H:i') }}</span>
								</td>
								<td class="px-6 py-4 whitespace-nowrap">
									<div class="flex items-center gap-2">
										<!-- Status Update Form -->
										<form method="POST" action="{{ route('pharmacies.orders.update-status', $order) }}" class="inline-flex items-center gap-2">
											@csrf
											@method('PUT')
											<select name="status" class="px-2 py-1 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-primary focus:border-primary">
												<option value="preparing" {{ $order->status === 'preparing' ? 'selected' : '' }}>
													{{ app()->getLocale() === 'ar' ? 'قيد التحضير' : 'Preparing' }}
												</option>
												<option value="delivering" {{ $order->status === 'delivering' ? 'selected' : '' }}>
													{{ app()->getLocale() === 'ar' ? 'قيد التوصيل' : 'Delivering' }}
												</option>
												<option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>
													{{ app()->getLocale() === 'ar' ? 'تم التوصيل' : 'Delivered' }}
												</option>
											</select>
											<button type="submit" class="px-3 py-1 bg-blue-600 text-white rounded text-xs hover:bg-blue-700">
												{{ app()->getLocale() === 'ar' ? 'تحديث' : 'Update' }}
											</button>
										</form>
										<!-- Mark Paid Button -->
										@if(!$order->payed)
											<form method="POST" action="{{ route('pharmacies.orders.mark-paid', $order) }}" class="inline">
												@csrf
												@method('PUT')
												<button type="submit" class="px-3 py-1 bg-green-600 text-white rounded text-xs hover:bg-green-700">
													{{ app()->getLocale() === 'ar' ? 'تحديد كمدفوع' : 'Mark Paid' }}
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
				{{ app()->getLocale() === 'ar' ? 'لا توجد طلبات' : 'No orders found.' }}
			</div>
		@endif
	</div>
</div>
@endsection

