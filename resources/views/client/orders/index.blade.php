@extends('client.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'تتبع الطلبات' : 'Track Orders')
@section('page-title', app()->getLocale() === 'ar' ? 'تتبع الطلبات' : 'Track Orders')
@section('page-description', app()->getLocale() === 'ar' ? 'عرض ومتابعة جميع طلباتك' : 'View and track all your orders')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
	<!-- Filters -->
	<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
		<form method="GET" action="{{ route('client.orders.index') }}" class="flex flex-wrap items-center gap-3">
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
				<a href="{{ route('client.orders.index') }}" class="px-3 py-2 text-sm text-gray-600 hover:text-gray-800">
					{{ app()->getLocale() === 'ar' ? 'إعادة تعيين' : 'Reset' }}
				</a>
			@endif
			<button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-teal-700 text-sm font-medium">
				{{ app()->getLocale() === 'ar' ? 'تصفية' : 'Filter' }}
			</button>
		</form>
	</div>

	<!-- Orders -->
	<div class="bg-white rounded-lg shadow-sm border border-gray-200">
		<div class="p-4 md:p-6 border-b">
			<h3 class="text-lg font-semibold text-slate-900">
				{{ app()->getLocale() === 'ar' ? 'طلباتي' : 'My Orders' }}
			</h3>
		</div>

		@if($orders->count())
			<!-- Desktop Table View -->
			<div class="hidden md:block overflow-x-auto">
				<table class="min-w-full divide-y divide-gray-200">
					<thead class="bg-gray-50">
						<tr>
							<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
								{{ app()->getLocale() === 'ar' ? 'رقم الطلب' : 'Order ID' }}
							</th>
							<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
								{{ app()->getLocale() === 'ar' ? 'المقدم' : 'Provider' }}
							</th>
							<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
								{{ app()->getLocale() === 'ar' ? 'الإجمالي' : 'Total Price' }}
							</th>
							<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
								{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}
							</th>
							<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
								{{ app()->getLocale() === 'ar' ? 'الدفع' : 'Payment' }}
							</th>
							<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
								{{ app()->getLocale() === 'ar' ? 'تاريخ الإنشاء' : 'Created At' }}
							</th>
							<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
								{{ app()->getLocale() === 'ar' ? 'التقييم' : 'Review' }}
							</th>
						</tr>
					</thead>
					<tbody class="bg-white divide-y divide-gray-200">
						@foreach($orders as $order)
							@include('client.orders.partials.order-row', ['order' => $order])
						@endforeach
					</tbody>
				</table>
			</div>

			<!-- Mobile Card View -->
			<div class="md:hidden divide-y divide-gray-200">
				@foreach($orders as $order)
					@include('client.orders.partials.order-card', ['order' => $order])
				@endforeach
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

@push('scripts')
<script>
	document.addEventListener('DOMContentLoaded', function() {
		// Star rating interaction
		document.querySelectorAll('.star-input').forEach(function(input) {
			input.addEventListener('change', function() {
				const rating = parseInt(this.value);
				const container = this.closest('form');
				const stars = container.querySelectorAll('.star-icon');
				stars.forEach(function(star, index) {
					if (index < rating) {
						star.classList.remove('text-gray-300');
						star.classList.add('text-yellow-400');
					} else {
						star.classList.remove('text-yellow-400');
						star.classList.add('text-gray-300');
					}
				});
			});
		});

		// Modal open/close
		document.querySelectorAll('[data-open-review]').forEach(function(button) {
			button.addEventListener('click', function() {
				const modalId = this.getAttribute('data-open-review');
				document.getElementById(modalId).classList.remove('hidden');
			});
		});

		document.querySelectorAll('[data-close-review]').forEach(function(button) {
			button.addEventListener('click', function() {
				const modalId = this.getAttribute('data-close-review');
				document.getElementById(modalId).classList.add('hidden');
			});
		});

		// Close modal on outside click
		document.querySelectorAll('[id^="review-modal-"]').forEach(function(modal) {
			modal.addEventListener('click', function(e) {
				if (e.target === this) {
					this.classList.add('hidden');
				}
			});
		});
	});
</script>
@endpush
@endsection

