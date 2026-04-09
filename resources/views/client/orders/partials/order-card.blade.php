 @php
	$statusLabels = [
		'preparing' => app()->getLocale() === 'ar' ? 'قيد التحضير' : 'Preparing',
		'delivering' => app()->getLocale() === 'ar' ? 'قيد التوصيل' : 'Delivering',
		'delivered' => app()->getLocale() === 'ar' ? 'تم التوصيل' : 'Delivered',
	];
	$statusOrder = ['preparing', 'delivering', 'delivered'];
	$currentStatusIndex = array_search($order->status, $statusOrder);
	$currentStatusIndex = $currentStatusIndex !== false ? $currentStatusIndex : -1;
@endphp

<div class="p-4">
	<!-- Order Header -->
	<div class="flex items-center justify-between mb-4">
		<div>
			<h4 class="text-base font-semibold text-slate-900">
				{{ app()->getLocale() === 'ar' ? 'طلب' : 'Order' }} #{{ $order->id }}
			</h4>
			<p class="text-xs text-gray-500 mt-1">
				{{ $order->created_at->format('Y-m-d H:i') }}
			</p>
		</div>
		@if($order->payed)
			<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
				{{ app()->getLocale() === 'ar' ? 'مدفوع' : 'Paid' }}
			</span>
		@else
			<span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
				{{ app()->getLocale() === 'ar' ? 'غير مدفوع' : 'Unpaid' }}
			</span>
		@endif
	</div>

	<!-- Provider Info -->
	<div class="mb-4">
		@if($order->laboratory_id)
			<div class="flex items-center gap-2 text-sm">
				<i class="bi bi-hospital text-teal-600"></i>
				<span class="text-teal-600 font-medium">{{ $order->laboratory->name ?? 'N/A' }}</span>
			</div>
		@else
			<div class="flex items-center gap-2 text-sm">
				<i class="bi bi-capsule text-blue-600"></i>
				<span class="text-gray-700">{{ $order->pharmacy?->name ?? 'N/A' }}</span>
			</div>
		@endif
	</div>

	<!-- Amazon-style Status Timeline -->
	<div class="mb-4 pb-4 border-b border-gray-200">
		<div class="relative">
			<!-- Background Line -->
			<div class="absolute top-5 left-0 right-0 h-0.5 bg-gray-200"></div>
			@if($currentStatusIndex >= 0)
				<div class="absolute top-5 left-0 h-0.5 bg-primary transition-all" style="width: {{ (($currentStatusIndex + 1) / count($statusOrder)) * 100 }}%"></div>
			@endif
			
			<!-- Status Steps -->
			<div class="flex items-center justify-between relative">
				@foreach($statusOrder as $index => $status)
					@php
						$isActive = $index <= $currentStatusIndex;
					@endphp
					<div class="flex flex-col items-center flex-1">
						<!-- Status Icon -->
						<div class="w-10 h-10 rounded-full flex items-center justify-center mb-2 {{ $isActive ? 'bg-primary text-white' : 'bg-gray-200 text-gray-400' }} transition-all relative z-10">
							@if($status === 'preparing')
								<i class="bi bi-box-seam text-lg"></i>
							@elseif($status === 'delivering')
								<i class="bi bi-truck text-lg"></i>
							@elseif($status === 'delivered')
								<i class="bi bi-check-circle text-lg"></i>
							@endif
						</div>
						<!-- Status Label -->
						<span class="text-xs font-medium text-center {{ $isActive ? 'text-primary' : 'text-gray-400' }}">
							{{ $statusLabels[$status] ?? ucfirst($status) }}
						</span>
					</div>
				@endforeach
			</div>
		</div>
	</div>

	<!-- Total Price -->
	<div class="mb-4">
		<div class="flex items-center justify-between">
			<span class="text-sm text-gray-600">{{ app()->getLocale() === 'ar' ? 'الإجمالي' : 'Total' }}</span>
			<span class="text-lg font-bold text-primary">{{ number_format($order->total_price ?? 0, 2) }} {{ app()->getLocale() === 'ar' ? 'ج.م' : 'EGP' }}</span>
		</div>
	</div>

	<!-- View Results Link (for laboratory orders) -->
	@if($order->laboratory_id && $order->offer && $order->offer->attachments->count() > 0)
		<div class="mb-4">
			<a href="{{ route('client.test-results.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-primary hover:underline">
				<i class="bi bi-file-earmark-check"></i>
				{{ app()->getLocale() === 'ar' ? 'عرض النتائج' : 'View Results' }}
			</a>
		</div>
	@endif

	<!-- Review Section -->
	<div class="pt-4 border-t border-gray-200">
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
			{{-- Review Form --}}
			<div class="pt-3 mt-3 border-t border-gray-200">
				<form method="POST" action="{{ route('client.orders.review', $order) }}" class="flex flex-col gap-3">
					@csrf
					<div class="flex flex-col sm:flex-row sm:items-center gap-2">
						<span class="text-sm text-gray-700">
							{{ app()->getLocale() === 'ar' ? 'تقييم الخدمة:' : 'Rate service:' }}
						</span>
						<div class="star-rating">
							@for($i = 5; $i >= 1; $i--)
								<input
									type="radio"
									id="star{{ $i }}-{{ $order->id }}"
									name="rating"
									value="{{ $i }}"
									{{ $i == 1 ? 'checked' : '' }}
									required
								>
								<label for="star{{ $i }}-{{ $order->id }}">★</label>
							@endfor
						</div>
					</div>
					<textarea
						name="comment"
						rows="2"
						class="w-full border border-gray-300 rounded-lg p-2 text-sm"
						placeholder="{{ app()->getLocale() === 'ar' ? 'اترك تعليقاً (اختياري)' : 'Leave a comment (optional)' }}"
					>{{ old('comment') }}</textarea>
					<div class="md:flex md:justify-end">
						<button type="submit" class="w-full md:w-auto px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
							{{ app()->getLocale() === 'ar' ? 'إرسال التقييم' : 'Submit Review' }}
						</button>
					</div>
				</form>
			</div>
		@endif
	</div>
</div>
