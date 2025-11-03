<x-filament::page>
    {{-- Default Filament view content --}}
    <div class="space-y-4">
        <h2 class="text-xl font-semibold">{{ $this->record->client->name ?? '' }}</h2>

        {{-- Your content / table / details go here --}}
    </div>

    {{-- 👇 Include footer --}}
    @include('footer')
</x-filament::page>
