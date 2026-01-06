@extends('nurse.dashboard')

@section('content')
	<div class="max-w-2xl mx-auto">
		<div class="bg-white rounded-lg shadow p-6 space-y-6">
			<h2 class="text-lg font-semibold">{{ __('Edit Nursing Offer') }}</h2>

			@if ($errors->any())
				<div class="p-3 rounded bg-red-50 text-red-700">
					<ul class="list-disc list-inside text-sm">
						@foreach ($errors->all() as $error)
							<li>{{ $error }}</li>
						@endforeach
					</ul>
				</div>
			@endif

			<form method="POST" action="{{ route('nurse.offers.update', $offer) }}" class="space-y-4">
				@csrf
				@method('PUT')

				<div>
					<label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Request') }}</label>
					<div class="w-full border rounded-md p-2 bg-slate-50 text-slate-700">
						#{{ $offer->home_nurse_request_id }} — {{ $offer->request?->service_type }}
					</div>
				</div>

				<div>
					<label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Notes (optional)') }}</label>
					<textarea name="notes" rows="3" class="w-full border rounded-md p-2">{{ old('notes', $offer->notes) }}</textarea>
				</div>

				<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
					<div>
						<label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Visit period') }}</label>
						@php $vp = old('visit_period', $offer->visit_period); @endphp
						<select name="visit_period" class="w-full border rounded-md p-2" required>
							<option value="daily" @selected($vp==='daily')>{{ __('Daily') }}</option>
							<option value="every_two_days" @selected($vp==='every_two_days')>{{ __('Every 2 days') }}</option>
							<option value="once_weekly" @selected($vp==='once_weekly')>{{ __('Once weekly') }}</option>
							<option value="twice_weekly" @selected($vp==='twice_weekly')>{{ __('Twice weekly') }}</option>
						</select>
					</div>
					<div>
						<label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Visits count') }}</label>
						<input type="number" min="1" max="60" name="visits_count" class="w-full border rounded-md p-2" value="{{ old('visits_count', $offer->visits_count) }}" required>
					</div>
				</div>

				<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
					<div>
						<label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Visit price (EGP)') }}</label>
						<input type="number" step="0.01" min="0" name="visit_price" class="w-full border rounded-md p-2" value="{{ old('visit_price', $offer->visit_price) }}" required>
					</div>
					<div>
						<label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Total price (EGP)') }}</label>
						<input type="number" step="0.01" min="0" name="total_price" class="w-full border rounded-md p-2" value="{{ old('total_price', $offer->total_price ?? $offer->price) }}" placeholder="{{ __('Auto = visits_count * visit_price') }}">
					</div>
				</div>

				<div class="flex justify-end gap-2">
					<a href="{{ route('nurse.offers.index') }}" class="px-4 py-2 rounded-md border">{{ __('Cancel') }}</a>
					<button type="submit" class="px-4 py-2 rounded-md bg-primary text-white">{{ __('Save') }}</button>
				</div>
			</form>
		</div>
	</div>
@endsection



