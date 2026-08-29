{{--
    Examinations, lab tests and radiology ordered during the visit, grouped by
    type. Shared by the browser print sheet and the mPDF sheet so the paper and
    the PDF can never disagree.

    Expects: $prescription. Renders nothing when the visit ordered none.
--}}
@php
    $requests = $prescription->appointment?->medicalRequests ?? collect();
@endphp

@if ($requests->isNotEmpty())
    <div class="requests">
        <div class="requests-title">🔬 {{ __('app.print.requests_title') }}</div>
        @foreach ($requests->groupBy('type') as $type => $group)
            <div class="requests-group">
                <div class="requests-type">{{ $group->first()->typeLabel() }}</div>
                <ul>
                    @foreach ($group as $request)
                        <li>
                            {{ $request->name }}
                            @if ($request->notes)
                                <span class="requests-note">— {{ $request->notes }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
@endif
