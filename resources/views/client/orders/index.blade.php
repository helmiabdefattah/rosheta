@extends('client.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'تتبع الطلبات' : 'Track Orders')
@section('page-title', app()->getLocale() === 'ar' ? 'تتبع الطلبات' : 'Track Orders')
@section('page-description', app()->getLocale() === 'ar' ? 'عرض ومتابعة جميع طلباتك' : 'View and track all your orders')

@push('styles')
<style>
	.review-modal {
		display: none;
	}
	.review-modal.show {
		display: none; /* Hidden on mobile */
	}
	@media (min-width: 768px) {
		.review-modal.show {
			display: flex !important; /* Show on desktop */
		}
	}
	.star-rating {
		display: inline-flex;
		direction: ltr;
	}
	.star-rating input[type="radio"] {
		display: none;
	}
	.star-rating label {
		cursor: pointer;
		font-size: 1.25rem;
		color: #cbd5e1; /* slate-300 */
		margin-inline: 2px;
	}
	.star-rating input[type="radio"]:checked ~ label {
		color: #fbbf24; /* amber-400 */
	}
	.star-rating label:hover,
	.star-rating label:hover ~ label {
		color: #facc15; /* amber-300 */
	}
	.refresh-indicator {
		display: inline-block;
		animation: spin 1s linear infinite;
	}
	@keyframes spin {
		from { transform: rotate(0deg); }
		to { transform: rotate(360deg); }
	}
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
	<div class="flex items-center justify-between mb-6">
		<div>
			<h2 class="text-2xl font-bold text-slate-900">
				{{ app()->getLocale() === 'ar' ? 'طلباتي' : 'My Orders' }}
			</h2>
			<p class="text-sm text-gray-600 mt-1">
				{{ app()->getLocale() === 'ar'
					? 'سيتم تحديث القائمة تلقائياً كل 20 ثانية'
					: 'List will auto-refresh every 20 seconds' }}
			</p>
		</div>
		<div class="flex items-center gap-3">
			<span id="lastRefresh" class="text-xs text-gray-500"></span>
			<span id="refreshIndicator" class="refresh-indicator hidden">
				<i class="bi bi-arrow-clockwise text-primary"></i>
			</span>
		</div>
	</div>
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
			<button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm font-medium">
				{{ app()->getLocale() === 'ar' ? 'تصفية' : 'Filter' }}
			</button>
		</form>
	</div>

	<!-- Orders -->
	<div id="ordersContainer" class="bg-white rounded-lg shadow-sm border border-gray-200">
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
(function() {
	'use strict';

	// Wait for jQuery to be available
	if (typeof jQuery === 'undefined') {
		console.error('jQuery is not loaded');
	} else {
		(function($) {
			'use strict';

			let refreshInterval;
			const refreshIntervalMs = 20000; // 20 seconds

			function updateLastRefreshTime() {
				const now = new Date();
				const timeStr = now.toLocaleTimeString();
				$('#lastRefresh').text('{{ app()->getLocale() === "ar" ? "آخر تحديث:" : "Last refresh:" }} ' + timeStr);
			}

			function refreshOrders() {
				$('#refreshIndicator').removeClass('hidden');

				// Get current filter values
				const status = $('select[name="status"]').val() || '';
				const paid = $('select[name="paid"]').val() || '';
				const search = $('input[name="search"]').val() || '';

				$.ajax({
					url: '{{ route("client.orders.index") }}',
					method: 'GET',
					data: {
						status: status,
						paid: paid,
						search: search
					},
					headers: {
						'X-Requested-With': 'XMLHttpRequest',
						'Accept': 'text/html'
					},
					success: function(html) {
						// Extract the orders container from the response
						const $temp = $('<div>').html(html);
						const $newContent = $temp.find('#ordersContainer').html();
						if ($newContent) {
							$('#ordersContainer').html($newContent);
						}
						updateLastRefreshTime();
					},
					error: function(xhr, status, error) {
						console.error('Failed to refresh orders:', error);
					},
					complete: function() {
						$('#refreshIndicator').addClass('hidden');
					}
				});
			}

			// Star rating initialization (same as offers page)
			document.addEventListener('DOMContentLoaded', function () {
				document.querySelectorAll('.star-rating').forEach(function(group) {
					const radios = group.querySelectorAll('input[type="radio"]');
					radios.forEach(function(radio) {
						radio.addEventListener('change', function() {
							// No-op, CSS sibling selector handles color
						});
					});
				});
			});

			$(document).ready(function() {
				// Initial setup
				updateLastRefreshTime();

				// Start auto-refresh
				refreshInterval = setInterval(refreshOrders, refreshIntervalMs);

				// Refresh on page visibility change (when user comes back to tab)
				document.addEventListener('visibilitychange', function() {
					if (!document.hidden) {
						refreshOrders();
					}
				});
			});

			// Cleanup on page unload
			$(window).on('beforeunload', function() {
				if (refreshInterval) {
					clearInterval(refreshInterval);
				}
			});
		})(jQuery);
	}
})();
</script>
@endpush
@endsection

