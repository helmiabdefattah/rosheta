@props(['name', 'options' => [], 'selected' => null, 'required' => false])

<div class="relative">
    <select 
        name="{{ $name }}" 
        id="{{ $name }}" 
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => '
            w-full px-4 py-2.5 
            bg-white border border-slate-200 rounded-xl 
            text-slate-900 
            focus:border-primary-500 focus:ring-2 focus:ring-primary-500/10 focus:outline-none
            transition-all duration-200
            appearance-none
            disabled:bg-slate-50 disabled:text-slate-500
            ' . ($errors->has($name) ? 'border-red-300 focus:border-red-500 focus:ring-red-500/10' : '')
        ]) }}
    >
        @if($attributes->has('placeholder'))
            <option value="" disabled {{ $selected === null ? 'selected' : '' }}>{{ $attributes->get('placeholder') }}</option>
        @endif
        
        @foreach($options as $value => $label)
            <option value="{{ $value }}" {{ $selected == $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
        
        {{ $slot }}
    </select>
    
    <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </div>

    @error($name)
        <p class="mt-1.5 text-xs font-medium text-red-500 flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ $message }}
        </p>
    @enderror
</div>
