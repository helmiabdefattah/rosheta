<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-sky-50 to-cyan-50 relative flex items-center justify-center py-12 px-4">
    <div class="absolute inset-0 pointer-events-none">
        <div class="blob bg-sky-300/25 w-[30rem] h-[30rem] rounded-full absolute -top-28 -left-28 blur-3xl"></div>
        <div class="blob bg-cyan-300/25 w-[30rem] h-[30rem] rounded-full absolute -bottom-28 -right-28 blur-3xl"></div>
    </div>

    <div class="w-full max-w-md relative">
        <div class="bg-white/90 backdrop-blur-xl border border-sky-100 rounded-2xl shadow-xl p-8">
            <div class="flex items-center justify-center gap-4 mb-8">
                <img src="{{ url('/images/mo-logo.png') }}" alt="Mostashfa-on" class="w-24 h-24 rounded-2xl ring-2 ring-sky-200 shadow-md object-contain">
                <div class="text-center leading-tight">
                    <div class="text-3xl font-black text-slate-900">Mostashfa-on</div>
                    <div class="text-xs text-slate-500">{{ __('Welcome back') }}</div>
                </div>
            </div>

            <div class="space-y-6">
                {{ $slot }}
            </div>
        </div>

        <div class="text-center text-xs text-slate-500 mt-6">
            © {{ date('Y') }} Mostashfa-on
        </div>
    </div>
</div>


