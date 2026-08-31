@extends('admin.layouts.admin')

@php
    use App\Http\Controllers\Admin\DoctorCredentialsController as Creds;

    $l = app()->getLocale() === 'ar';
    $revealedUserId = session(Creds::FLASH_USER);
    $revealedPassword = session(Creds::FLASH_PASSWORD);
@endphp

@section('title', $l ? 'بيانات الدخول' : 'Login cards')
@section('page-title', $l ? 'بيانات الدخول' : 'Login cards')
@section('page-description', $l ? 'بطاقات تسجيل الدخول للطبيب ومساعديه: ' . $doctor->name : 'Login cards for ' . $doctor->name . ' and their assistants')

@section('header-actions')
    <div class="flex flex-wrap items-center gap-2">
        <button type="button" id="cred-print"
                class="px-4 py-2 text-sm font-medium text-white bg-primary rounded-xl hover:bg-teal-700">
            🖨️ {{ $l ? 'PDF / طباعة' : 'PDF / Print' }}
        </button>
        <button type="button" id="cred-image"
                class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 disabled:opacity-50">
            🖼️ {{ $l ? 'تنزيل صورة' : 'Download image' }}
        </button>
        <a href="{{ route('admin.doctors.index') }}" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-xl hover:bg-slate-50">
            {{ $l ? 'رجوع للأطباء' : 'Back to doctors' }}
        </a>
    </div>
@endsection

@push('styles')
<style>
    /* Fixed width so every screenshot comes out the same size. */
    .cred-card { width: 360px; }
    .cred-value { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; direction: ltr; unicode-bidi: plaintext; }

    @media print {
        /* The admin shell (sidebar, header, nav) would otherwise print around the
           cards. Hiding everything and revealing only the print area survives any
           later change to the layout's structure, which a chain of parent
           selectors would not. */
        body * { visibility: hidden; }
        #cred-print-area, #cred-print-area * { visibility: visible; }
        #cred-print-area { position: absolute; inset-inline-start: 0; top: 0; width: 100%; margin: 0; padding: 0; }

        .no-print { display: none !important; }
        .cred-card { break-inside: avoid; box-shadow: none; border-color: #94a3b8; }

        @page { margin: 12mm; }
    }
</style>
@endpush

@section('content')
@php
    /**
     * One card per account. The doctor's own login first, then each assistant.
     * `password` carries the plaintext only for the account just regenerated.
     */
    $cards = collect();

    if ($doctor->user) {
        $cards->push([
            'user' => $doctor->user,
            'role' => $l ? 'طبيب' : 'Doctor',
            'accent' => 'bg-teal-600',
            'context' => $doctor->clinics->pluck('name')->filter()->implode(' · '),
        ]);
    }

    foreach ($assistants as $assistant) {
        $cards->push([
            'user' => $assistant,
            'role' => $l ? 'مساعد' : 'Assistant',
            'accent' => 'bg-slate-700',
            'context' => $assistant->assistantClinic?->name ?? ($l ? 'غير محددة' : 'Unassigned'),
        ]);
    }
@endphp

<div class="space-y-6">
    <div class="no-print rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
        <p class="font-semibold">{{ $l ? 'عن كلمات المرور' : 'About the passwords' }}</p>
        <p class="mt-1">
            {{ $l
                ? 'كلمات المرور مخزّنة مشفّرة ولا يمكن استرجاعها. اضغط «توليد كلمة مرور» لإنشاء كلمة مرور جديدة لهذا الحساب — ستظهر مرة واحدة فقط على البطاقة، فالتقط الصورة قبل تحديث الصفحة. كلمة المرور القديمة تتوقف فوراً.'
                : 'Passwords are stored hashed and cannot be read back. Use “Generate password” to set a new one for that account — it is shown once, so take the screenshot before reloading. The old password stops working immediately.' }}
        </p>
    </div>

    @if($cards->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white p-10 text-center text-slate-500">
            <p>{{ $l ? 'لا توجد حسابات لهذا الطبيب بعد.' : 'This doctor has no accounts yet.' }}</p>
            @if(! $doctor->user_id)
                <a href="{{ route('admin.doctors.account.create', $doctor) }}" class="mt-3 inline-block font-medium text-primary hover:underline">
                    {{ $l ? 'إضافة حساب دخول للطبيب' : 'Add a login account for the doctor' }}
                </a>
            @endif
        </div>
    @else
        <div id="cred-print-area" class="flex flex-wrap gap-6 bg-white">
            @foreach($cards as $card)
                @php
                    $user = $card['user'];
                    $justGenerated = $revealedPassword && (int) $revealedUserId === (int) $user->id;
                @endphp
                <div class="cred-card rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
                    <div class="{{ $card['accent'] }} px-5 py-3 flex items-center gap-3">
                        <img src="{{ asset('images/mo-logo.png') }}" alt="" class="h-8 w-auto object-contain">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-white leading-tight">{{ $l ? 'مستشفى-أون' : 'Mostashfa-on' }}</p>
                            <p class="text-[11px] uppercase tracking-wider text-white/80 leading-tight">{{ $card['role'] }}</p>
                        </div>
                    </div>

                    <div class="p-5 space-y-4">
                        <div>
                            <p class="text-base font-bold text-slate-900 leading-snug">{{ $user->name }}</p>
                            @if($card['context'])
                                <p class="text-xs text-slate-500 mt-0.5">{{ $card['context'] }}</p>
                            @endif
                        </div>

                        <div class="rounded-lg bg-slate-50 border border-slate-200 divide-y divide-slate-200">
                            <div class="px-3 py-2">
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ $l ? 'اسم المستخدم' : 'Username' }}</p>
                                <p class="cred-value text-sm text-slate-900 break-all mt-0.5">{{ $user->email ?: '—' }}</p>
                            </div>
                            @if($user->phone_number)
                                <div class="px-3 py-2">
                                    <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ $l ? 'أو رقم الهاتف' : 'Or phone' }}</p>
                                    <p class="cred-value text-sm text-slate-900 mt-0.5">{{ $user->phone_number }}</p>
                                </div>
                            @endif
                            <div class="px-3 py-2">
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ $l ? 'كلمة المرور' : 'Password' }}</p>
                                @if($justGenerated)
                                    <p class="cred-value text-lg font-bold text-slate-900 tracking-wide mt-0.5">{{ $revealedPassword }}</p>
                                @else
                                    <p class="text-sm text-slate-400 mt-0.5">{{ $l ? 'مخفية — ولّد كلمة مرور جديدة لعرضها' : 'Hidden — generate a new one to show it' }}</p>
                                @endif
                            </div>
                        </div>

                        <p class="text-[11px] text-slate-400 leading-snug">
                            {{ $l ? 'سجّل الدخول من' : 'Sign in at' }}
                            <span class="cred-value">{{ rtrim(config('app.url'), '/') }}/login</span>
                        </p>

                        @unless($user->is_active)
                            <p class="text-[11px] font-semibold text-red-600">
                                {{ $l ? 'تنبيه: هذا الحساب غير مفعّل ولن يتمكن صاحبه من الدخول.' : 'Note: this account is inactive and cannot sign in.' }}
                            </p>
                        @endunless
                    </div>

                    <div class="no-print px-5 pb-5 space-y-2">
                        <button type="button" class="cred-card-image w-full px-4 py-2 text-sm font-medium text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200 disabled:opacity-50"
                                data-filename="{{ \Illuminate\Support\Str::slug($user->name) ?: 'card' }}">
                            🖼️ {{ $l ? 'تنزيل هذه البطاقة' : 'Download this card' }}
                        </button>
                        <form method="POST" action="{{ route('admin.doctors.credentials.password', [$doctor, $user]) }}"
                              onsubmit="return confirm('{{ $l ? 'سيتم تغيير كلمة المرور الحالية فوراً. هل تريد المتابعة؟' : 'This replaces the current password immediately. Continue?' }}');">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-white bg-primary rounded-lg hover:bg-teal-700">
                                {{ $l ? 'توليد كلمة مرور' : 'Generate password' }}
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection


@push('scripts')
{{-- html2canvas paints the DOM onto a canvas so a card can be saved as a file
     rather than screenshotted by hand. Same CDN pattern the admin layout
     already uses for jQuery, Tailwind and toastr. --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    (function () {
        var area = document.getElementById('cred-print-area');
        var printBtn = document.getElementById('cred-print');
        var imageBtn = document.getElementById('cred-image');
        if (!area) return;

        var FAILED = @json($l ? 'تعذّر إنشاء الصورة.' : 'Could not create the image.');
        var BASENAME = @json(\Illuminate\Support\Str::slug($doctor->name) ?: 'doctor');

        // The browser's print dialog is also its "Save as PDF": one control, and
        // the print stylesheet above already drops the buttons and the shell.
        if (printBtn) printBtn.addEventListener('click', function () { window.print(); });

        function save(canvas, filename) {
            var link = document.createElement('a');
            link.download = filename + '.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
        }

        /**
         * Render one element to a PNG. The `no-print` blocks — the generate
         * buttons, the warning banner, these controls — are hidden for the
         * duration so the saved image is the card alone, exactly as the printed
         * page is.
         */
        function capture(node, filename, button) {
            if (typeof html2canvas !== 'function') { alert(FAILED); return; }

            var hidden = Array.prototype.slice.call(node.querySelectorAll('.no-print'));
            hidden.forEach(function (el) { el.style.display = 'none'; });

            // The cards are flex items and stretch to the tallest in the row, so
            // hiding a card's buttons leaves the card its old height and the PNG
            // gains a strip of blank space. Opting this one out of the stretch
            // lets it shrink to its own content first.
            var stretched = node.classList.contains('cred-card');
            if (stretched) node.style.alignSelf = 'flex-start';

            if (button) button.disabled = true;

            html2canvas(node, { backgroundColor: '#ffffff', scale: 2, useCORS: true })
                .then(function (canvas) { save(canvas, filename); })
                .catch(function () { alert(FAILED); })
                .finally(function () {
                    hidden.forEach(function (el) { el.style.display = ''; });
                    if (stretched) node.style.alignSelf = '';
                    if (button) button.disabled = false;
                });
        }

        if (imageBtn) {
            imageBtn.addEventListener('click', function () {
                capture(area, BASENAME + '-login-cards', imageBtn);
            });
        }

        // One card on its own, to hand to the person it belongs to.
        area.addEventListener('click', function (e) {
            var button = e.target.closest('.cred-card-image');
            if (!button) return;
            capture(button.closest('.cred-card'), button.dataset.filename + '-login-card', button);
        });
    })();
</script>
@endpush
