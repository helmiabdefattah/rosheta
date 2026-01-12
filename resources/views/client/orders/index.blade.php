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

	<!-- Orders Table -->
	<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
		<div class="p-6 border-b">
			<h3 class="text-lg font-semibold text-slate-900">
				{{ app()->getLocale() === 'ar' ? 'طلباتي' : 'My Orders' }}
			</h3>
		</div>

		@if($orders->count())
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
						<tr class="hover:bg-gray-50">
							<td class="px-4 py-3 text-sm text-gray-700">
								<span class="font-semibold">#{{ $order->id }}</span>
							</td>
							<td class="px-4 py-3 text-sm text-gray-700">
                                @if($order->laboratory_id)
                                    <span class="flex items-center gap-1.5 text-teal-600 font-medium">
                                        <i class="bi bi-hospital text-xs"></i>
                                        {{ $order->laboratory->name ?? 'N/A' }}
                                    </span>
                                @else
                                    {{ $order->pharmacy?->name ?? 'N/A' }}
                                @endif
							</td>
							<td class="px-4 py-3 text-sm text-gray-700">
								<span class="font-semibold">{{ number_format($order->total_price ?? 0, 2) }} {{ app()->getLocale() === 'ar' ? 'ج.م' : 'EGP' }}</span>
							</td>
							<td class="px-4 py-3 text-sm text-gray-700">
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
                                @if($order->laboratory_id && $order->offer && $order->offer->attachments->count() > 0)
                                    <div class="mt-2 text-center">
                                        <a href="{{ route('client.test-results.index') }}" class="text-[10px] font-bold text-primary hover:underline uppercase tracking-tighter">
                                            <i class="bi bi-file-earmark-check me-1"></i>
                                            {{ app()->getLocale() === 'ar' ? 'عرض النتائج' : 'View Results' }}
                                        </a>
                                    </div>
                                @endif
							</td>
							<td class="px-4 py-3 text-sm text-gray-700">
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
							<td class="px-4 py-3 text-sm text-gray-700">
								{{ $order->created_at->format('Y-m-d H:i') }}
							</td>
							<td class="px-4 py-3 text-sm text-gray-700">
								@php
									$existingReview = $order->review ?? null;
								@endphp

								@if($existingReview)
									<div class="text-xs text-gray-600">
										<div class="flex items-center gap-1 mb-1">
											@for($i = 1; $i <= 5; $i++)
												<svg class="w-4 h-4 {{ $i <= (int)$existingReview->rating ? 'text-yellow-400' : 'text-gray-300' }}" viewBox="0 0 20 20" fill="currentColor">
													<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.802 2.036a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.802-2.036a1 1 0 00-1.175 0l-2.802 2.036c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
												</svg>
											@endfor
										</div>
										@if(!empty($existingReview->comment))
											<div class="text-gray-700 mt-1">"{{ $existingReview->comment }}"</div>
										@endif
									</div>
								@else
									<!-- Review Form -->
									<button type="button" 
										class="px-3 py-1.5 rounded border text-xs hover:bg-gray-50" 
										data-open-review="review-modal-{{ $order->id }}">
										{{ app()->getLocale() === 'ar' ? 'إضافة تقييم' : 'Add Review' }}
									</button>

									<!-- Review Modal -->
									<div id="review-modal-{{ $order->id }}" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
										<div class="bg-white rounded-lg max-w-md w-full p-6">
											<h3 class="text-lg font-semibold mb-4">
												{{ app()->getLocale() === 'ar' ? 'تقييم الطلب' : 'Review Order' }}
											</h3>
											<form method="POST" action="{{ route('client.orders.review', $order) }}">
												@csrf
												<div class="mb-4">
													<label class="block text-sm font-medium text-gray-700 mb-2">
														{{ app()->getLocale() === 'ar' ? 'التقييم' : 'Rating' }}
													</label>
													<div class="flex items-center gap-1">
														@for($i = 1; $i <= 5; $i++)
															<label class="cursor-pointer">
																<input type="radio" name="rating" value="{{ $i }}" class="sr-only star-input" required>
																<svg class="w-8 h-8 star-icon text-gray-300 hover:text-yellow-400 transition-colors" viewBox="0 0 20 20" fill="currentColor">
																	<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.802 2.036a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.802-2.036a1 1 0 00-1.175 0l-2.802 2.036c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
																</svg>
															</label>
														@endfor
													</div>
												</div>
												<div class="mb-4">
													<label for="comment-{{ $order->id }}" class="block text-sm font-medium text-gray-700 mb-2">
														{{ app()->getLocale() === 'ar' ? 'تعليق (اختياري)' : 'Comment (Optional)' }}
													</label>
													<textarea 
														id="comment-{{ $order->id }}"
														name="comment" 
														rows="3" 
														class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
														placeholder="{{ app()->getLocale() === 'ar' ? 'اكتب تعليقك هنا...' : 'Write your comment here...' }}"
													>{{ old('comment') }}</textarea>
												</div>
												<div class="flex items-center justify-end gap-3">
													<button type="button" 
														class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200"
														data-close-review="review-modal-{{ $order->id }}">
														{{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
													</button>
													<button type="submit" 
														class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-teal-700 font-medium">
														{{ app()->getLocale() === 'ar' ? 'إرسال' : 'Submit' }}
													</button>
												</div>
											</form>
										</div>
									</div>
								@endif
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>
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

