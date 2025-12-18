@props(['for', 'required' => false])

<label for="{{ $for }}" {{ $attributes->merge(['class' => 'block text-sm font-semibold text-slate-700 mb-1.5']) }}>
    {{ $slot }}
    @if($required)
        <span class="text-red-500">*</span>
    @endif
</label>
