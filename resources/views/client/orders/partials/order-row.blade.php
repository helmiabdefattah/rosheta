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
			<div class="pt-2">
				<form method="POST" action="{{ route('client.orders.review', $order) }}" class="flex flex-col gap-2">
					@csrf
					<div class="flex flex-col gap-2">
						<div class="flex items-center gap-2">
							<span class="text-xs text-gray-700">
								{{ app()->getLocale() === 'ar' ? 'تقييم:' : 'Rate:' }}
							</span>
							<div class="star-rating">
								@for($i = 5; $i >= 1; $i--)
									<input
										type="radio"
										id="star-row-{{ $i }}-{{ $order->id }}"
										name="rating"
										value="{{ $i }}"
										{{ $i == 1 ? 'checked' : '' }}
										required
									>
									<label for="star-row-{{ $i }}-{{ $order->id }}">★</label>
								@endfor
							</div>
						</div>
						<textarea
							name="comment"
							rows="2"
							class="w-full border border-gray-300 rounded-lg p-2 text-xs"
							placeholder="{{ app()->getLocale() === 'ar' ? 'تعليق (اختياري)' : 'Comment (optional)' }}"
						>{{ old('comment') }}</textarea>
						<button type="submit" class="w-full px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 text-xs">
							{{ app()->getLocale() === 'ar' ? 'إرسال' : 'Submit' }}
						</button>
					</div>
				</form>
			</div>
		@endif
	</td>
</tr>
