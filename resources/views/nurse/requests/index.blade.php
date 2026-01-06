@extends('nurse.layouts.dashboard')

@section('content')
    <div class="space-y-4">

        @forelse($requests as $request)
            <div class="bg-white p-5 rounded-lg shadow flex flex-col md:flex-row md:justify-between md:items-center gap-4">

                {{-- Request Info --}}
                <div class="space-y-1">
                    <h4 class="font-semibold text-lg text-gray-800">
                        {{ $request->service_type }}
                    </h4>

                    <p class="text-sm text-gray-600">
                        <strong>Client:</strong> {{ $request->client->name }}
                    </p>

                    <p class="text-sm text-gray-600">
                        <strong>Visits:</strong> {{ $request->visits_count }}
                        • {{ ucfirst(str_replace('_',' ', $request->visit_frequency)) }}
                    </p>

                    <p class="text-sm text-gray-600">
                        <strong>Start Date:</strong> {{ $request->visit_start_date->format('Y-m-d') }}
                        • <strong>Time:</strong> {{ $request->visit_time }}
                    </p>

                    @if($request->preferred_gender)
                        <span class="inline-block text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded">
                        Preferred Gender: {{ ucfirst($request->preferred_gender) }}
                    </span>
                    @endif

                    @if($request->medical_notes)
                        <p class="text-xs text-gray-500 mt-1">
                            <strong>Notes:</strong> {{ $request->medical_notes }}
                        </p>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex gap-2">


                    <a href="{{ route('nurse.offers.create', ['request_id' => $request->id]) }}"
                       class="px-4 py-2 text-sm rounded bg-blue-600 text-white hover:bg-blue-700">
                        Make Offer
                    </a>
                </div>

            </div>
        @empty
            <div class="text-center text-gray-500 py-10">
                No requests available
            </div>
        @endforelse

        <div class="pt-4">
            {{ $requests->links() }}
        </div>

    </div>
@endsection
