@php
    $localeAr = app()->getLocale() === 'ar';
    $account = $doctor->user;
    $active = (bool) $account?->is_active;
@endphp
@if(! $account)
    <span class="text-xs text-slate-400">{{ $localeAr ? 'بدون حساب' : 'No account' }}</span>
@else
    <div class="doctor-active-toggle flex items-center gap-2"
         data-toggle-url="{{ route('admin.doctors.toggle-active', $doctor) }}">
        <label class="inline-flex items-center cursor-pointer relative">
            <input type="checkbox"
                   class="sr-only peer doctor-active-toggle-input"
                   {{ $active ? 'checked' : '' }}
                   aria-label="{{ $localeAr ? 'تفعيل أو إيقاف حساب الطبيب' : 'Toggle doctor account active status' }}">
            <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary/30 peer-checked:bg-emerald-500 after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border after:border-slate-300 after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-disabled:opacity-50"></div>
        </label>
        <span class="doctor-active-toggle-label text-xs font-semibold {{ $active ? 'text-emerald-700' : 'text-red-600' }}">
            {{ $active ? ($localeAr ? 'مفعّل' : 'Active') : ($localeAr ? 'غير مفعّل' : 'Inactive') }}
        </span>
    </div>
@endif
