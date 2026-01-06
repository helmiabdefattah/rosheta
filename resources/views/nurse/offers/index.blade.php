@extends('nurse.dashboard')

@section('content')
	<div class="max-w-5xl mx-auto">
		<div class="flex justify-end mb-4">
			<a href="{{ route('nurse.offers.create') }}" class="px-4 py-2 bg-primary text-white rounded-md">{{ __('New Offer') }}</a>
		</div>

		<div class="bg-white rounded-lg shadow p-6">
			<h2 class="text-lg font-semibold mb-4">{{ __('My Nursing Offers') }}</h2>

			@if(session('success'))
				<div class="mb-3 p-3 rounded bg-green-50 text-green-700">{{ session('success') }}</div>
			@endif
			@if(session('error'))
				<div class="mb-3 p-3 rounded bg-red-50 text-red-700">{{ session('error') }}</div>
			@endif

			@if($offers->isEmpty())
				<p class="text-slate-500">{{ __('No offers yet.') }}</p>
			@else
				<div class="overflow-x-auto">
					<table class="min-w-full text-sm">
						<thead class="border-b bg-slate-50">
						<tr>
							<th class="text-left p-2">#</th>
							<th class="text-left p-2">{{ __('Request') }}</th>
							<th class="text-left p-2">{{ __('Visits') }}</th>
							<th class="text-left p-2">{{ __('Visit price') }}</th>
							<th class="text-left p-2">{{ __('Total') }}</th>
							<th class="text-left p-2">{{ __('Status') }}</th>
							<th class="text-left p-2">{{ __('Actions') }}</th>
						</tr>
						</thead>
						<tbody>
						@foreach($offers as $offer)
							<tr class="border-b">
								<td class="p-2 text-slate-500">#{{ $offer->id }}</td>
								<td class="p-2">
									<div class="font-medium">#{{ $offer->home_nurse_request_id }}</div>
									<div class="text-xs text-slate-500">{{ $offer->request?->service_type }}</div>
								</td>
								<td class="p-2">{{ $offer->visits_count }} / {{ str_replace('_',' ', $offer->visit_period ?? '-') }}</td>
								<td class="p-2">{{ number_format($offer->visit_price ?? 0, 2) }}</td>
								<td class="p-2 font-semibold">{{ number_format($offer->total_price ?? $offer->price, 2) }}</td>
								<td class="p-2">
									<span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-xs">{{ ucfirst($offer->status) }}</span>
								</td>
								<td class="p-2">
									<div class="flex gap-2">
										@if($offer->status === 'pending')
											<a href="{{ route('nurse.offers.edit', $offer) }}" class="px-3 py-1 bg-blue-600 text-white rounded text-xs">{{ __('Edit') }}</a>
											<form method="POST" action="{{ route('nurse.offers.destroy', $offer) }}" onsubmit="return confirm('{{ __('Delete this offer?') }}')">
												@csrf
												@method('DELETE')
												<button type="submit" class="px-3 py-1 bg-red-600 text-white rounded text-xs">{{ __('Delete') }}</button>
											</form>
										@else
											<span class="text-xs text-slate-400">{{ __('No actions') }}</span>
										@endif
									</div>
								</td>
							</tr>
						@endforeach
						</tbody>
					</table>
				</div>
				<div class="mt-4">
					{{ $offers->links() }}
				</div>
			@endif
		</div>
	</div>
@endsection



