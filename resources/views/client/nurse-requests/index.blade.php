@extends('client.layouts.dashboard')

@section('title', __('My Nursing Requests'))
@section('page-title', __('My Nursing Requests'))

@section('content')
	<div class="flex justify-end mb-4">
		<a href="{{ route('client.nurse-requests.create') }}" class="px-4 py-2 bg-primary text-white rounded-md">{{ __('New Request') }}</a>
	</div>

	@forelse($requests as $r)
		<div class="bg-white rounded-lg shadow p-6 mb-4">
			<div class="flex items-start justify-between">
				<div>
					<div class="flex items-center gap-3">
						<span class="text-sm text-slate-500">#{{ $r->id }}</span>
						<span class="px-2 py-1 text-xs rounded bg-slate-100 text-slate-700">{{ ucfirst($r->status) }}</span>
					</div>
					<h3 class="text-lg font-semibold text-slate-900 mt-2">{{ $r->service_type }}</h3>
					<div class="mt-2 text-sm text-slate-600">
						<div class="mb-1">
							<strong>{{ __('Start') }}:</strong> {{ optional($r->visit_start_date)->format('Y-m-d') }} {{ $r->visit_time }}
						</div>
						<div class="mb-1">
							<strong>{{ __('Visits') }}:</strong> {{ $r->visits_count }} / {{ $r->visit_frequency }}
						</div>
						@if($r->address)
							<div class="mb-1">
								<strong>{{ __('Address') }}:</strong> {{ $r->address->address }}
								@if($r->address->area) - {{ $r->address->area->name }} @endif
							</div>
						@endif
					</div>
				</div>
				<div class="flex gap-2">
					<a href="{{ route('client.nurse-requests.edit', $r) }}" class="px-3 py-1 text-sm bg-blue-600 text-white rounded">{{ __('Edit') }}</a>
					<a href="{{ route('client.nurse-requests.create') }}" class="px-3 py-1 text-sm bg-green-600 text-white rounded">{{ __('Create') }}</a>
					<a href="{{ route('client.nurse-requests.show', $r) }}" class="px-3 py-1 text-sm bg-slate-200 text-slate-800 rounded">{{ __('View') }}</a>
				</div>
			</div>

			<div class="mt-4 border-t pt-4">
				<div class="flex items-center justify-between mb-2">
					<h4 class="text-sm font-semibold text-slate-800">{{ app()->getLocale() === 'ar' ? 'عروض التمريض' : 'Nurse Offers' }}</h4>
				</div>

				@if($r->offers->isEmpty())
					<p class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'لا توجد عروض بعد.' : 'No offers yet.' }}</p>
				@else
					<div class="space-y-2">
						@foreach($r->offers as $offer)
							@php $modalId = 'nurse-offer-modal-'.$offer->id; @endphp
							<div class="p-3 border rounded">
								<div class="flex items-start justify-between">
									<div>
										<div class="font-medium">
											{{ $offer->nurse?->client?->name ?? (app()->getLocale() === 'ar' ? 'ممرض/ـة' : 'Nurse') }}
										</div>
										<div class="text-sm text-slate-600">
											<strong>{{ app()->getLocale() === 'ar' ? 'السعر' : 'Price' }}:</strong>
											{{ number_format($offer->price, 2) }} {{ app()->getLocale() === 'ar' ? 'ج.م' : 'EGP' }}
										</div>
									</div>
									<div class="flex items-center gap-2">
										<button type="button" class="px-3 py-1 text-sm bg-slate-100 text-slate-800 rounded"
										        data-modal-open="{{ $modalId }}">{{ app()->getLocale() === 'ar' ? 'الملف' : 'Profile' }}</button>
										<span class="px-2 py-1 text-xs rounded bg-slate-100 text-slate-700">{{ ucfirst($offer->status) }}</span>
										@if($offer->status === 'pending')
											<form action="{{ route('client.nurse-offers.accept', $offer) }}" method="POST" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'تأكيد قبول العرض؟' : 'Accept this offer?' }}');">
												@csrf
												@method('PUT')
												<button type="submit" class="px-3 py-1 text-sm bg-green-600 text-white rounded">{{ app()->getLocale() === 'ar' ? 'قبول' : 'Accept' }}</button>
											</form>
											<form action="{{ route('client.nurse-offers.reject', $offer) }}" method="POST" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'تأكيد رفض العرض؟' : 'Reject this offer?' }}');">
												@csrf
												@method('PUT')
												<button type="submit" class="px-3 py-1 text-sm bg-red-600 text-white rounded">{{ app()->getLocale() === 'ar' ? 'رفض' : 'Reject' }}</button>
											</form>
										@endif
									</div>
								</div>

								<!-- Modal -->
								<div id="{{ $modalId }}" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
									<div class="bg-white rounded-lg shadow max-w-xl w-full mx-4">
										<div class="flex items-center justify-between p-4 border-b">
											<h5 class="font-semibold text-slate-800">
												{{ app()->getLocale() === 'ar' ? 'ملف الممرض/ـة' : 'Nurse Profile' }}
											</h5>
											<button type="button" class="text-slate-500 hover:text-slate-700" data-modal-close="{{ $modalId }}">&times;</button>
										</div>
										<div class="p-4 space-y-3">
											<div class="flex items-center gap-3">
												@if($offer->nurse?->client?->avatar)
													<img src="{{ Storage::url($offer->nurse->client->avatar) }}" class="w-12 h-12 rounded-full object-cover border" alt="avatar">
												@else
													<div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center text-lg text-gray-600">
														{{ strtoupper(mb_substr($offer->nurse?->client?->name ?? 'N',0,1)) }}
													</div>
												@endif
												<div>
													<div class="font-medium">{{ $offer->nurse?->client?->name ?? '-' }}</div>
													<div class="text-sm text-slate-600">
														{{ $offer->nurse?->client?->email ?? $offer->nurse?->client?->phone_number }}
													</div>
												</div>
											</div>
											<div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
												<div>
													<div class="text-slate-500">{{ app()->getLocale() === 'ar' ? 'النوع' : 'Gender' }}</div>
													<div class="font-medium">
														@switch($offer->nurse?->gender)
															@case('male') {{ app()->getLocale() === 'ar' ? 'ذكر' : 'Male' }} @break
															@case('female') {{ app()->getLocale() === 'ar' ? 'أنثى' : 'Female' }} @break
															@default -
														@endswitch
													</div>
												</div>
												<div>
													<div class="text-slate-500">{{ app()->getLocale() === 'ar' ? 'سنوات الخبرة' : 'Experience (years)' }}</div>
													<div class="font-medium">{{ $offer->nurse?->years_of_experience ?? '-' }}</div>
												</div>
												<div class="md:col-span-2">
													<div class="text-slate-500">{{ app()->getLocale() === 'ar' ? 'العنوان' : 'Address' }}</div>
													<div class="font-medium">{{ $offer->nurse?->address ?? '-' }}</div>
												</div>
												<div>
													<div class="text-slate-500">{{ app()->getLocale() === 'ar' ? 'المؤهل' : 'Qualification' }}</div>
													<div class="font-medium">{{ ucfirst(str_replace('_',' ', $offer->nurse?->qualification ?? '-')) }}</div>
												</div>
												<div>
													<div class="text-slate-500">{{ app()->getLocale() === 'ar' ? 'جهة التعليم' : 'Education place' }}</div>
													<div class="font-medium">{{ $offer->nurse?->education_place ?? '-' }}</div>
												</div>
												<div class="md:col-span-2">
													<div class="text-slate-500">{{ app()->getLocale() === 'ar' ? 'المناطق المغطاة' : 'Covered Areas' }}</div>
													@php
														$ids = is_array($offer->nurse?->area_ids ?? null) ? $offer->nurse->area_ids : [];
														$labels = collect($ids)->map(function($id) use ($areaMap) {
															$area = $areaMap[$id] ?? null;
															if (!$area) return null;
															$city = $area->city->name ?? '';
															$gov = $area->city->governorate->name ?? '';
															return trim($area->name . ($city ? ' - '.$city : '') . ($gov ? ' ('.$gov.')' : ''));
														})->filter()->values();
													@endphp
													@if($labels->isEmpty())
														<div class="text-slate-600">-</div>
													@else
														<div class="flex flex-wrap gap-1">
															@foreach($labels as $label)
																<span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700">{{ $label }}</span>
															@endforeach
														</div>
													@endif
												</div>
												<div class="md:col-span-2">
													<div class="text-slate-500">{{ app()->getLocale() === 'ar' ? 'الشهادات' : 'Certifications' }}</div>
													<div class="font-medium">
														@php $certs = (array)($offer->nurse?->certifications ?? []); @endphp
														{{ empty($certs) ? '-' : implode('، ', $certs) }}
													</div>
												</div>
												<div class="md:col-span-2">
													<div class="text-slate-500">{{ app()->getLocale() === 'ar' ? 'المهارات' : 'Skills' }}</div>
													<div class="font-medium">
														@php $skills = (array)($offer->nurse?->skills ?? []); @endphp
														{{ empty($skills) ? '-' : implode('، ', $skills) }}
													</div>
												</div>
											</div>
										</div>
										<div class="p-4 border-t flex justify-end">
											<button type="button" class="px-4 py-2 bg-slate-800 text-white rounded" data-modal-close="{{ $modalId }}">
												{{ app()->getLocale() === 'ar' ? 'إغلاق' : 'Close' }}
											</button>
										</div>
									</div>
								</div>
							@endforeach
						</div>
				@endif
			</div>
		</div>
	@empty
		<div class="bg-white rounded-lg shadow p-6 text-center text-slate-500">
			{{ __('No requests yet.') }}
		</div>
	@endforelse

	<div class="mt-4">
		{{ $requests->links() }}
	</div>
@endsection

@push('scripts')
<script>
	// Simple modal open/close handler using data attributes
	document.addEventListener('click', function (e) {
		const openTarget = e.target.closest('[data-modal-open]');
		if (openTarget) {
			const id = openTarget.getAttribute('data-modal-open');
			const modal = document.getElementById(id);
			if (modal) {
				modal.classList.remove('hidden');
				modal.classList.add('flex');
			}
		}
		const closeTarget = e.target.closest('[data-modal-close]');
		if (closeTarget) {
			const id = closeTarget.getAttribute('data-modal-close');
			const modal = document.getElementById(id);
			if (modal) {
				modal.classList.add('hidden');
				modal.classList.remove('flex');
			}
		}
		// Close when clicking outside the modal content
		if (e.target.classList.contains('bg-black/50')) {
			e.target.classList.add('hidden');
			e.target.classList.remove('flex');
		}
	}, false);
</script>
@endpush

