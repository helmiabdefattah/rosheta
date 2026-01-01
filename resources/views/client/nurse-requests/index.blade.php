@extends('client.layouts.dashboard')

@section('title', __('My Nursing Requests'))
@section('page-title', __('My Nursing Requests'))

@section('content')
	<div class="flex justify-end mb-4">
		<a href="{{ route('client.nurse-requests.create') }}" class="px-4 py-2 bg-primary text-white rounded-md">{{ __('New Request') }}</a>
	</div>

	<div class="bg-white rounded-lg shadow overflow-hidden">
		<table class="min-w-full">
			<thead class="bg-slate-100 text-left text-sm">
				<tr>
					<th class="px-4 py-3">{{ __('ID') }}</th>
					<th class="px-4 py-3">{{ __('Service') }}</th>
					<th class="px-4 py-3">{{ __('Start') }}</th>
					<th class="px-4 py-3">{{ __('Visits') }}</th>
					<th class="px-4 py-3">{{ __('Status') }}</th>
					<th class="px-4 py-3 text-right">{{ __('Actions') }}</th>
				</tr>
			</thead>
			<tbody class="divide-y">
			@forelse($requests as $r)
				<tr class="hover:bg-slate-50">
					<td class="px-4 py-3">#{{ $r->id }}</td>
					<td class="px-4 py-3">{{ $r->service_type }}</td>
					<td class="px-4 py-3">{{ optional($r->visit_start_date)->format('Y-m-d') }} {{ $r->visit_time }}</td>
					<td class="px-4 py-3">{{ $r->visits_count }}</td>
					<td class="px-4 py-3">
						<span class="px-2 py-1 text-xs rounded bg-slate-100">{{ ucfirst($r->status) }}</span>
					</td>
					<td class="px-4 py-3 text-right">
						<a href="{{ route('client.nurse-requests.show', $r) }}" class="text-primary hover:underline">{{ __('View') }}</a>
					</td>
				</tr>
			@empty
				<tr>
					<td colspan="6" class="px-4 py-6 text-center text-slate-500">
						{{ __('No requests yet.') }}
					</td>
				</tr>
			@endforelse
			</tbody>
		</table>
	</div>

	<div class="mt-4">
		{{ $requests->links() }}
	</div>
@endsection


