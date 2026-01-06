@extends('nurse.dashboard')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow p-6 space-y-6">
            <h2 class="text-lg font-semibold">{{ __('Create Nursing Offer') }}</h2>

            @if ($errors->any())
                <div class="p-3 rounded bg-red-50 text-red-700">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('nurse.offers.store') }}" class="space-y-4">
                @csrf

                @php
                    $req = $availableRequests->firstWhere('id', $preselectedRequestId);
                @endphp

                @if(!$req)
                    <div class="text-red-600">Request not found.</div>
                @else

                    <!-- Hidden request ID -->
                    <input type="hidden" name="home_nurse_request_id" value="{{ $req->id }}">

                    <!-- Request info -->
                    <div class="bg-gray-50 p-3 rounded border space-y-1">
                        <p><strong>{{ __('Request ID') }}:</strong> #{{ $req->id }}</p>
                        <p><strong>{{ __('Service') }}:</strong> {{ $req->service_type }}</p>
                        <p><strong>{{ __('Client') }}:</strong> {{ $req->client->name }}</p>
                        <p><strong>{{ __('Address') }}:</strong>
                            @if($req->address)
                                {{ $req->address->address }}
                                @if($req->address->area)
                                    , {{ $req->address->area->name }}
                                @endif
                                @if($req->address->city)
                                    , {{ $req->address->city->name }}
                                @endif
                            @else
                                {{ __('N/A') }}
                            @endif
                        </p>
                        <p><strong>{{ __('Visits') }}:</strong> {{ $req->visits_count }} • {{ ucfirst(str_replace('_',' ', $req->visit_frequency)) }}</p>
                        @if($req->preferred_gender)
                            <span class="inline-block mt-2 text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded">
                            {{ __('Preferred:') }} {{ ucfirst($req->preferred_gender) }}
                        </span>
                        @endif
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Notes (optional)') }}</label>
                        <textarea name="notes" rows="3" class="w-full border rounded-md p-2" placeholder="{{ __('Any notes for the client...') }}">{{ old('notes') }}</textarea>
                    </div>

                    <!-- Visit period and count -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Visit period') }}</label>
                            <select name="visit_period" class="w-full border rounded-md p-2" required>
                                @php $vp = old('visit_period'); @endphp
                                <option value="daily" @selected($vp==='daily')>{{ __('Daily') }}</option>
                                <option value="every_two_days" @selected($vp==='every_two_days')>{{ __('Every 2 days') }}</option>
                                <option value="once_weekly" @selected($vp==='once_weekly')>{{ __('Once weekly') }}</option>
                                <option value="twice_weekly" @selected($vp==='twice_weekly')>{{ __('Twice weekly') }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Visits count') }}</label>
                            <input type="number" min="1" max="60" name="visits_count" id="visits_count" class="w-full border rounded-md p-2" value="{{ old('visits_count', $req->visits_count) }}" required>
                        </div>
                    </div>

					<!-- Visit schedule -->
					<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
						<div>
							<label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Visit start time') }}</label>
							<input type="time" name="visit_start_time" class="w-full border rounded-md p-2" value="{{ old('visit_start_time') }}">
						</div>
						<div>
							<label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Visit duration (hours)') }}</label>
							<input type="number" min="1" max="24" step="1" name="visit_duration" class="w-full border rounded-md p-2" value="{{ old('visit_duration') }}">
						</div>
					</div>

                    <!-- Price -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Visit price (EGP)') }}</label>
                            <input type="number" step="0.01" min="0" name="visit_price" id="visit_price" class="w-full border rounded-md p-2" value="{{ old('visit_price') }}" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Total price (EGP)') }}</label>
                            <input type="number" step="0.01" min="0" id="total_price" class="w-full border rounded-md p-2 bg-gray-100" value="0" readonly>
                        </div>
                    </div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ route('nurse.offers.index') }}" class="px-4 py-2 rounded-md border">{{ __('Cancel') }}</a>
                        <button type="submit" class="px-4 py-2 rounded-md bg-primary text-white">{{ __('Create Offer') }}</button>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Auto calculate total price -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const visitsInput = document.getElementById('visits_count');
            const priceInput = document.getElementById('visit_price');
            const totalInput = document.getElementById('total_price');

            function updateTotal() {
                const visits = parseFloat(visitsInput.value) || 0;
                const price = parseFloat(priceInput.value) || 0;
                totalInput.value = (visits * price).toFixed(2);
            }

            visitsInput.addEventListener('input', updateTotal);
            priceInput.addEventListener('input', updateTotal);

            updateTotal(); // initial calculation
        });
    </script>
@endsection
