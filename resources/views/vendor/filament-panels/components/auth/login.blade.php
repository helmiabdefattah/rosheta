@php
    /** Prefer a project-provided Blade view for the entire login page if available. **/
@endphp
@if (\Illuminate\Support\Facades\View::exists('auth.login'))
    @include('auth.login')
@else
    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-sky-50 to-cyan-50 relative flex items-center justify-center py-12 px-4">
        <div class="absolute inset-0 pointer-events-none">
            <div class="blob bg-sky-300/25 w-[28rem] h-[28rem] rounded-full absolute -top-24 -left-24 blur-3xl"></div>
            <div class="blob bg-cyan-300/25 w-[28rem] h-[28rem] rounded-full absolute -bottom-24 -right-24 blur-3xl"></div>
        </div>

    <div class="w-full max-w-md relative">
        <div class="bg-white/90 backdrop-blur-xl border border-sky-100 rounded-2xl shadow-xl p-8">
            <div class="flex items-center justify-center gap-3 mb-6">
                <img src="{{ url('/images/mo-logo.png') }}" alt="Mostashfa-on" class="w-20 h-20 rounded-2xl ring-2 ring-sky-200 shadow-md object-contain">
                <div class="text-center leading-tight">
                    <div class="text-2xl font-black text-slate-900">Mostashfa-on</div>
                    <div class="text-xs text-slate-500">Welcome back</div>
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


