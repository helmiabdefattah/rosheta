@php
    $title = app()->getLocale() === 'ar' ? 'تفاصيل الطلب' : 'Request Details';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} #{{ $clientRequest->id }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
<div class="max-w-5xl mx-auto p-6">
    <div class="mb-6">
        <a href="{{ route('admin.client-requests.index') }}" class="text-blue-600 hover:underline">
            {{ app()->getLocale() === 'ar' ? 'رجوع' : 'Back' }}
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between mb-4">
            <h1 class="text-2xl font-bold">{{ $title }} #{{ $clientRequest->id }}</h1>
            <span class="px-2 py-1 text-xs rounded-full bg-slate-100">
                {{ ucfirst($clientRequest->status) }}
            </span>
        </div>

        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <p class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'العميل' : 'Client' }}</p>
                <p class="font-semibold">{{ $clientRequest->client->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'العنوان' : 'Address' }}</p>
                <p class="font-semibold">{{ $clientRequest->address->address ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'النوع' : 'Type' }}</p>
                <p class="font-semibold">{{ $clientRequest->type ?? '-' }}</p>
            </div>
            <div>
                <p class="text-sm text-slate-500">{{ app()->getLocale() === 'ar' ? 'ملاحظات' : 'Notes' }}</p>
                <p class="font-semibold">{{ $clientRequest->note ?? '-' }}</p>
            </div>
        </div>
    </div>
    @php
        use Illuminate\Support\Js;
    @endphp

    @if(!empty($clientRequest->images) && count($clientRequest->images))
        <div class="mt-6 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-xl font-bold mb-4">{{ app()->getLocale() === 'ar' ? 'الصور المرفقة' : 'Attached Images' }}</h2>

            @if(count($clientRequest->images) === 1)
                @php $img = $clientRequest->images[0]; @endphp
                <a href="{{ $img }}" target="_blank" class="block rounded-2xl border border-slate-200 bg-slate-100 overflow-hidden">
                    <img src="{{ $img }}" alt="attachment"
                         class="w-full h-[75vh] object-contain bg-slate-900">
                </a>
            @else
                <div x-data="{ i: 0, imgs: {{ Js::from($clientRequest->images) }} }" class="relative w-full max-w-5xl mx-auto">
                    <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-slate-900">
                        <template x-for="(src, idx) in imgs" :key="idx">
                            <img x-show="i === idx"
                                 :src="src"
                                 :alt="'attachment-' + (idx+1)"
                                 class="w-full h-[70vh] md:h-[78vh] object-contain transition-opacity duration-300"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 @click.prevent="window.open(src, '_blank')">
                        </template>

                        <!-- Prev / Next -->
                        <button type="button"
                                class="absolute left-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-slate-800 rounded-full p-2 shadow"
                                @click="i = (i - 1 + imgs.length) % imgs.length"
                                aria-label="Prev">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <button type="button"
                                class="absolute right-2 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-slate-800 rounded-full p-2 shadow"
                                @click="i = (i + 1) % imgs.length"
                                aria-label="Next">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>

                    <!-- Dots -->
                    <div class="flex justify-center gap-2 mt-4">
                        <template x-for="(src, idx) in imgs" :key="'dot-' + idx">
                            <button class="w-3 h-3 rounded-full transition-all"
                                    :class="i === idx ? 'bg-sky-600 w-6' : 'bg-slate-300 hover:bg-slate-400'"
                                    @click="i = idx"
                                    :aria-label="'Go to slide ' + (idx+1)"></button>
                        </template>
                    </div>
                </div>
            @endif
        </div>
    @endif


    @if($clientRequest->type == "medicine")
        <div class="mt-6 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-xl font-bold mb-4">{{ app()->getLocale() === 'ar' ? 'الأدوية' : 'Medicines' }}</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                    <tr class="text-left text-slate-500">
                        <th class="py-2 pr-4">{{ app()->getLocale() === 'ar' ? 'الدواء' : 'Medicine' }}</th>
                        <th class="py-2 pr-4">{{ app()->getLocale() === 'ar' ? 'الكمية' : 'Quantity' }}</th>
                        <th class="py-2 pr-4">{{ app()->getLocale() === 'ar' ? 'الوحدة' : 'Unit' }}</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    @foreach($clientRequest->lines as $line)
                        <tr>
                            <td class="py-2 pr-4">{{ $line->medicine->name ?? '-' }}</td>
                            <td class="py-2 pr-4">{{ $line->quantity }}</td>
                            <td class="py-2 pr-4">{{ $line->unit }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
    @if($clientRequest->type == "test" )
        <div class="mt-6 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-xl font-bold mb-4">{{ app()->getLocale() === 'ar' ? 'التحاليل' : 'Medical Tests' }}</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                    <tr class="text-left text-slate-500">
                        <th class="py-2 pr-4">{{ app()->getLocale() === 'ar' ? 'التحليل' : 'Test' }}</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    @foreach($clientRequest->testLines as $line)
                        <tr>
                            <td class="py-2 pr-4">{{ $line->medicalTest->test_name_en ?? $line->medicalTest->test_name_ar ?? '-' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
</body>
</html>



