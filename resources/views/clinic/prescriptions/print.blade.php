<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <title>{{ __('app.print.title', ['code' => $prescription->code]) }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white; }
        }
    </style>
</head>
<body class="bg-slate-100 p-6 text-slate-800">
<div class="max-w-2xl mx-auto">
    <div class="no-print mb-4 flex justify-between">
        <a href="{{ url()->previous() }}" class="text-indigo-600 text-sm">{{ __('app.common.back') }}</a>
        <button onclick="window.print()" class="bg-purple-600 text-white px-4 py-2 rounded text-sm">{{ __('app.common.print') }}</button>
    </div>

    <div class="bg-white shadow-sm rounded-xl p-8 border-t-4 border-indigo-600">
        {{-- Header --}}
        <div class="flex items-center justify-between border-b pb-4 mb-4">
            <div>
                <div class="text-2xl font-bold">🩺 {{ $prescription->appointment->clinic->name ?? config('app.name') }}</div>
                <div class="text-sm text-slate-500">{{ $prescription->appointment->clinic->address ?? '' }}</div>
                <div class="text-sm text-slate-500">{{ $prescription->appointment->clinic->phone_number ?? '' }}</div>
            </div>
            <div class="text-end text-sm">
                <div class="font-mono bg-slate-100 px-2 py-1 rounded">{{ $prescription->code }}</div>
                <div class="text-slate-500 mt-1">{{ $prescription->created_at->translatedFormat('d M Y') }}</div>
            </div>
        </div>

        {{-- Doctor / patient --}}
        <div class="grid grid-cols-2 gap-4 text-sm mb-6">
            <div>
                <div class="text-slate-400 text-xs uppercase">{{ __('app.print.doctor') }}</div>
                <div class="font-semibold">{{ $prescription->doctor->name ?? '—' }}</div>
                <div class="text-slate-500">{{ ($prescription->doctor->specialization->name ?? '') }}</div>
            </div>
            <div>
                <div class="text-slate-400 text-xs uppercase">{{ __('app.print.patient') }}</div>
                <div class="font-semibold">{{ $prescription->client->name }}</div>
                <div class="text-slate-500">
                    {{ $prescription->client->gender ? __('app.genders.'.$prescription->client->gender) : '' }}
                    @if($prescription->client->age) &middot; {{ $prescription->client->age }} {{ __('app.common.yrs') }} @endif
                </div>
            </div>
        </div>

        @if ($prescription->diagnosis)
            <div class="mb-4 text-sm">
                <div class="text-slate-400 text-xs uppercase">{{ __('app.common.diagnosis') }}</div>
                <div>{{ $prescription->diagnosis->diagnosis }}</div>
            </div>
        @endif

        {{-- Rx symbol + medicines --}}
        <div class="text-4xl font-serif italic text-indigo-600 mb-2">℞</div>
        <table class="w-full text-sm border-t border-b">
            <thead class="text-start text-slate-400 text-xs">
                <tr>
                    <th class="py-2">#</th>
                    <th class="py-2">{{ __('app.print.medicine') }}</th>
                    <th class="py-2">{{ __('app.print.dose') }}</th>
                    <th class="py-2">{{ __('app.print.frequency') }}</th>
                    <th class="py-2">{{ __('app.print.duration') }}</th>
                    <th class="py-2">{{ __('app.print.instructions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($prescription->items as $i => $item)
                    <tr>
                        <td class="py-2">{{ $i + 1 }}</td>
                        <td class="py-2 font-medium">{{ $item->medicine_name }}</td>
                        <td class="py-2">{{ $item->dose ?? '—' }}</td>
                        <td class="py-2">{{ $item->frequency ?? '—' }}</td>
                        <td class="py-2">{{ $item->duration ?? '—' }}</td>
                        <td class="py-2">{{ $item->instructions ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($prescription->notes)
            <div class="mt-4 text-sm">
                <div class="text-slate-400 text-xs uppercase">{{ __('app.common.notes') }}</div>
                <div>{{ $prescription->notes }}</div>
            </div>
        @endif

        <div class="mt-10 flex justify-end">
            <div class="text-center text-sm">
                <div class="border-t border-slate-400 w-48 pt-1">{{ __('app.print.signature') }}</div>
            </div>
        </div>
    </div>
</div>
<script>
    // Auto-open the print dialog when arriving with ?auto=1
    if (new URLSearchParams(location.search).get('auto')) window.print();
</script>
</body>
</html>
