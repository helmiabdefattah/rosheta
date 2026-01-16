@php
	$statusClass = match($offer->vendor_status) {
		'test_completed' => 'bg-green-100 text-green-700',
		'sample_collected' => 'bg-blue-100 text-blue-700',
		default => 'bg-amber-100 text-amber-700'
	};
	$statusLabel = match($offer->vendor_status) {
		'test_completed' => (app()->getLocale() === 'ar' ? 'تم الانتهاء' : 'Completed'),
		'sample_collected' => (app()->getLocale() === 'ar' ? 'تم سحب العينة' : 'Sample Collected'),
		default => (app()->getLocale() === 'ar' ? 'جاري التحضير' : 'In Preparation')
	};
@endphp

<div class="p-4">
	<!-- Header -->
	<div class="flex items-start justify-between mb-3">
		<div>
			<h4 class="text-base font-semibold text-slate-900 mb-1">
				{{ app()->getLocale() === 'ar' ? 'طلب' : 'Request' }} #{{ $offer->request->id }}
			</h4>
			<p class="text-xs text-gray-500">
				{{ $offer->updated_at->format('M d, Y') }}
			</p>
		</div>
		<span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $statusClass }}">
			{{ $statusLabel }}
		</span>
	</div>

	<!-- Laboratory Info -->
	<div class="flex items-center gap-2 mb-3">
		<div class="w-10 h-10 rounded-lg bg-teal-100 flex items-center justify-center text-teal-600">
			<i class="bi bi-hospital"></i>
		</div>
		<div>
			<p class="text-sm font-medium text-gray-700">{{ $offer->laboratory->name ?? 'N/A' }}</p>
			<span class="px-2 py-0.5 rounded-md text-xs font-semibold {{ $offer->request_type === 'test' ? 'bg-blue-50 text-blue-600' : 'bg-purple-50 text-purple-600' }}">
				{{ $offer->request_type === 'test' ? (app()->getLocale() === 'ar' ? 'تحاليل' : 'Test') : (app()->getLocale() === 'ar' ? 'أشعة' : 'Radiology') }}
			</span>
		</div>
	</div>

	<!-- Results Section -->
	<div class="pt-3 border-t border-gray-100">
		<p class="text-xs font-semibold text-gray-600 mb-2">
			{{ app()->getLocale() === 'ar' ? 'النتائج' : 'Results' }}
		</p>
		@if($offer->attachments->count() > 0)
			<div class="flex flex-col gap-2">
				@foreach($offer->attachments as $attachment)
					<a href="{{ $attachment->url }}" target="_blank" 
					   class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-primary/10 text-primary hover:bg-primary hover:text-white rounded-lg text-sm font-bold transition-all shadow-sm"
					   title="{{ $attachment->description ?? $attachment->file_name }}">
						@if($attachment->isPdf())
							<i class="bi bi-file-earmark-pdf text-lg"></i>
						@elseif($attachment->isImage())
							<i class="bi bi-image text-lg"></i>
						@else
							<i class="bi bi-paperclip text-lg"></i>
						@endif
						{{ app()->getLocale() === 'ar' ? 'عرض النتيجة' : 'View Result' }}
					</a>
				@endforeach
			</div>
		@else
			<div class="text-center py-4">
				<span class="text-xs text-gray-400 italic">
					{{ app()->getLocale() === 'ar' ? 'بانتظار رفع النتائج' : 'Waiting for results' }}
				</span>
			</div>
		@endif
	</div>
</div>
