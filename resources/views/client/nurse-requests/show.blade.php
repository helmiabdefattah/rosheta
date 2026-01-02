@extends('client.layouts.dashboard')

@section('title', __('Nursing Request #') . $request->id)
@section('page-title', __('Nursing Request #') . $request->id)

@section('content')
	<div class="space-y-6">
		<a href="{{ route('client.nurse-requests.index') }}" class="inline-block px-4 py-2 bg-gray-200 rounded">{{ __('Back') }}</a>

		<div class="bg-white rounded-lg shadow p-6">
			<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
				<div>
					<div class="text-xs text-slate-500">{{ __('Service') }}</div>
					<div class="font-semibold">{{ $request->service_type }}</div>
				</div>
				<div>
					<div class="text-xs text-slate-500">{{ __('Status') }}</div>
					<div><span class="px-2 py-1 text-xs rounded bg-slate-100">{{ ucfirst($request->status) }}</span></div>
				</div>
				<div>
					<div class="text-xs text-slate-500">{{ __('Start') }}</div>
					<div>{{ $request->visit_start_date?->format('Y-m-d') }} {{ $request->visit_time }}</div>
				</div>
				<div>
					<div class="text-xs text-slate-500">{{ __('Visits') }}</div>
					<div>{{ $request->visits_count }} / {{ __($request->visit_frequency) }}</div>
				</div>
				<div class="md:col-span-2">
					<div class="text-xs text-slate-500">{{ __('Medical notes') }}</div>
					<div>{{ $request->medical_last ?? '-' }}</div>
				</div>
			</div>
		</div>

		<div class="bg-white rounded-lg shadow overflow-hidden">
			<div class="px-6 py-4 border-b font-semibold">{{ __('Scheduled Visits') }}</div>
			<table class="min-w-full">
				<thead class="bg-slate-100 text-left text-sm">
					<tr>
						<th class="px-4 py-3">{{ __('#') }}</th>
						<th class="px-4 py-3">{{ __('Date & Time') }}</th>
						<th class="px-4 py-3">{{ __('Nurse') }}</th>
						<th class="px-4 py-3">{{ __('Status') }}</th>
						<th class="px-4 py-3">{{ __('Notes') }}</th>
					</tr>
				</thead>
                <tbody class="divide-y">
                @forelse($request->getRelation('visits') as $idx => $v)
                    <tr>
                        <td class="px-4 py-3">{{ $idx + 1 }}</td>
                        <td class="px-4 py-3">{{ $v->visit_datetime->format('Y-m-d H:i') }}</td>
                        <td class="px-4 py-3">{{ optional($v->nurse?->client)->name ?? '-' }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-1 text-xs rounded bg-slate-100">{{ ucfirst($v->status) }}</span></td>
                        <td class="px-4 py-3">{{ $v->notes ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">{{ __('No visits scheduled.') }}</td>
                    </tr>
                @endforelse
                </tbody>
			</table>
		</div>
	</div>
@endsection


