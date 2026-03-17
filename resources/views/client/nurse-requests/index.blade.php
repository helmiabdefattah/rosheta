@extends('client.layouts.dashboard')

@section('title', __('My Nursing Requests'))
@section('page-title', __('My Nursing Requests'))

@php
	$visitPeriodLabels = [
		'daily' => __('Daily'),
		'every_two_days' => __('Every two days'),
		'once_weekly' => __('Once weekly'),
		'twice_weekly' => __('Twice weekly'),
		'weekly' => __('Weekly'),
		'custom' => __('Custom'),
	];
@endphp

@section('content')
	<div class="flex justify-end mb-4">
		<a href="{{ route('client.nurse-requests.create') }}" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">{{ __('New Request') }}</a>
	</div>

	@forelse($requests as $r)
		<div class="bg-white rounded-lg shadow p-4 md:p-6 mb-4">
			<div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
				<div class="flex-1">
					<div class="flex items-center gap-3">
						<span class="text-sm text-slate-500">#{{ $r->id }}</span>
						<span class="px-2 py-1 text-xs rounded bg-slate-100 text-slate-700">{{ __(strtolower((string) $r->status)) }}</span>
					</div>
					<h3 class="text-lg font-semibold text-slate-900 mt-2">{{ $r->getTranslatedServiceType() }}</h3>
					<div class="mt-2 text-sm text-slate-600">
						<div class="mb-1">
							<strong>{{ __('Start') }}:</strong> {{ optional($r->visit_start_date)->format('Y-m-d') }} {{ $r->visit_time }}
						</div>
						<div class="mb-1">
							<strong>{{ __('Visits') }}:</strong> {{ $r->visits_count }} / {{ $visitPeriodLabels[$r->visit_frequency] ?? $r->visit_frequency }}
						</div>
						@if(!empty($r->preferred_gender))
							<div class="mb-1">
								<strong>{{ __('Preferred gender') }}:</strong>
								@switch($r->preferred_gender)
									@case('male') {{ __('Male') }} @break
									@case('female') {{ __('Female') }} @break
									@default -
								@endswitch
							</div>
						@endif
						@if($r->patient_age)
							<div class="mb-1">
								<strong>{{ __('Patient age') }}:</strong> {{ $r->patient_age }} {{ __('years') }}
							</div>
						@endif
						@if($r->medical_condition)
							<div class="mb-1">
								<strong>{{ __('Medical condition') }}:</strong> {{ $r->medical_condition }}
							</div>
						@endif
						@if($r->address)
							<div class="mb-1">
								<strong>{{ __('Address') }}:</strong> {{ $r->address->address }}
								@if($r->address->area) - {{ $r->address->area->name }} @endif
							</div>
						@endif
					</div>
				</div>
				<div class="flex flex-wrap gap-2">
					<a href="{{ route('client.nurse-requests.edit', $r) }}" class="px-3 py-1 text-sm bg-blue-600 text-white rounded">{{ __('Edit') }}</a>
					<a href="{{ route('client.nurse-requests.create') }}" class="px-3 py-1 text-sm bg-green-600 text-white rounded">{{ __('Create') }}</a>
					<a href="{{ route('client.nurse-requests.show', $r) }}" class="px-3 py-1 text-sm bg-slate-200 text-slate-800 rounded">{{ __('View') }}</a>
				</div>
			</div>

			<div class="mt-4 border-t pt-4">
				<div class="flex items-center justify-between mb-2">
					<h4 class="text-sm font-semibold text-slate-800">{{ __('Nurse Offers') }}</h4>
				</div>

				@if($r->offers->isEmpty())
					<p class="text-sm text-slate-500">{{ __('No offers yet.') }}</p>
				@else
					<div class="space-y-2">
						@foreach($r->offers as $offer)
							@php $modalId = 'nurse-offer-modal-'.$offer->id; @endphp
							<div class="p-3 border rounded">
								<div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
									<div>
										<div class="font-medium">
											{{ $offer->nurse?->user?->name ?? __('Nurse') }}
										</div>
										<div class="text-sm text-slate-600">
											<strong>{{ __('Price') }}:</strong>
											{{ number_format($offer->price, 2) }} {{ __('EGP') }}
										</div>
										<div class="text-sm text-slate-600 mt-1">
											<strong>{{ __('Visit') }}:</strong>
											<span class="inline-block">
												{{ $visitPeriodLabels[$offer->visit_period] ?? '-' }}
											</span>
											<span class="inline-block">
												@ {{ $offer->visit_start_time ? substr($offer->visit_start_time, 0, 5) : '-' }}
											</span>
											<span class="inline-block">
												( {{ $offer->visit_duration ? $offer->visit_duration . ' ' . __('hrs') : '-' }} )
											</span>
										</div>
									</div>
									<div class="flex flex-col md:flex-row items-start md:items-center gap-2">
										<div class="flex items-center gap-2">
											<button type="button" class="px-3 py-1 text-sm bg-slate-100 text-slate-800 rounded"
											        data-modal-open="{{ $modalId }}">{{ __('Profile') }}</button>
											<span class="px-2 py-1 text-xs rounded bg-slate-100 text-slate-700">{{ __(strtolower((string) $offer->status)) }}</span>
										</div>
										@if($offer->status === 'pending')
											<div class="flex flex-col md:flex-row gap-2 w-full md:w-auto">
												<form action="{{ route('client.nurse-offers.accept', $offer) }}" method="POST" onsubmit="return confirm(@json(__('Accept this offer?')));" class="w-full md:w-auto">
													@csrf
													@method('PUT')
													<button type="submit" class="w-full md:w-auto px-3 py-1 text-sm bg-green-600 text-white rounded">{{ __('Accept') }}</button>
												</form>
												<form action="{{ route('client.nurse-offers.reject', $offer) }}" method="POST" onsubmit="return confirm(@json(__('Reject this offer?')));" class="w-full md:w-auto">
													@csrf
													@method('PUT')
													<button type="submit" class="w-full md:w-auto px-3 py-1 text-sm bg-red-600 text-white rounded">{{ __('Reject') }}</button>
												</form>
											</div>
										@endif
									</div>
								</div>

								<!-- Modal -->
								<div id="{{ $modalId }}" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
									<div class="bg-white rounded-lg shadow max-w-xl w-full mx-4">
										<div class="flex items-center justify-between p-4 border-b">
											<h5 class="font-semibold text-slate-800">
												{{ __('Nurse Profile') }}
											</h5>
											<button type="button" class="text-slate-500 hover:text-slate-700" data-modal-close="{{ $modalId }}">&times;</button>
										</div>
										<div class="p-4 space-y-3">
                                            <div class="flex items-center gap-3">
                                                @if($offer->nurse?->user?->hasMedia('avatar'))
                                                    <img src="{{ $offer->nurse->user->getFirstMediaUrl('avatar') }}" class="w-12 h-12 rounded-full object-cover border" alt="{{ __('Avatar') }}">
                                                @else
                                                    <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center text-lg text-gray-600">
                                                        {{ strtoupper(mb_substr($offer->nurse?->user?->name ?? 'N', 0, 1)) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="font-medium">{{ $offer->nurse?->user?->name ?? '-' }}</div>
                                                    <div class="text-sm text-slate-600">
                                                        {{ $offer->nurse?->user?->email ?? $offer->nurse?->user?->phone_number }}
                                                    </div>
                                                </div>
                                            </div>
											<div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
												<div>
													<div class="text-slate-500">{{ __('Gender') }}</div>
													<div class="font-medium">
														@switch($offer->nurse?->gender)
															@case('male') {{ __('Male') }} @break
															@case('female') {{ __('Female') }} @break
															@default -
														@endswitch
													</div>
												</div>
												<div>
													<div class="text-slate-500">{{ __('Experience (years)') }}</div>
													<div class="font-medium">{{ $offer->nurse?->years_of_experience ?? '-' }}</div>
												</div>
												<div class="md:col-span-2">
													<div class="text-slate-500">{{ __('Address') }}</div>
													<div class="font-medium">{{ $offer->nurse?->address ?? '-' }}</div>
												</div>
												<div>
													<div class="text-slate-500">{{ __('Qualification') }}</div>
													<div class="font-medium">{{ ucfirst(str_replace('_',' ', $offer->nurse?->qualification ?? '-')) }}</div>
												</div>
												<div>
													<div class="text-slate-500">{{ __('Education place') }}</div>
													<div class="font-medium">{{ $offer->nurse?->education_place ?? '-' }}</div>
												</div>
												<div class="md:col-span-2">
													<div class="text-slate-500">{{ __('Covered Areas') }}</div>
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
													<div class="text-slate-500">{{ __('Certifications') }}</div>
													<div class="font-medium">
														@php $certs = (array)($offer->nurse?->certifications ?? []); @endphp
														{{ empty($certs) ? '-' : implode('، ', $certs) }}
													</div>
												</div>
												<div class="md:col-span-2">
													<div class="text-slate-500">{{ __('Skills') }}</div>
													<div class="font-medium">
														@php $skills = (array)($offer->nurse?->skills ?? []); @endphp
														{{ empty($skills) ? '-' : implode('، ', $skills) }}
													</div>
												</div>
											</div>
											<div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
												<div>
													<div class="text-slate-500">{{ __('Visit Period') }}</div>
													<div class="font-medium">
														{{ $visitPeriodLabels[$offer->visit_period] ?? '-' }}
													</div>
												</div>
												<div>
													<div class="text-slate-500">{{ __('Start Time') }}</div>
													<div class="font-medium">
														{{ $offer->visit_start_time ? substr($offer->visit_start_time, 0, 5) : '-' }}
													</div>
												</div>
												<div>
													<div class="text-slate-500">{{ __('Visit Duration') }}</div>
													<div class="font-medium">
														{{ $offer->visit_duration ? $offer->visit_duration . ' ' . __('hrs') : '-' }}
													</div>
												</div>
											</div>
										</div>
										<div class="p-4 border-t flex justify-end">
											<button type="button" class="px-4 py-2 bg-slate-800 text-white rounded" data-modal-close="{{ $modalId }}">
												{{ __('Close') }}
											</button>
										</div>
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
