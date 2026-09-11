{{--
    Sign-in page notices. Extracted from the login card because the card is
    hidden inside a running demo, while these messages — "demo unavailable",
    "your demo expired", a flashed info line — still have to reach the visitor.

    Each block carries its own bottom margin so the partial can sit either
    inside the card or bare on the page.
--}}
@if (session('info'))
    <div class="mb-6 p-4 bg-sky-50 border-s-4 border-sky-500 rounded-lg">
        <p class="text-sm font-medium text-sky-900">{{ session('info') }}</p>
    </div>
@endif

@if ($errors->any())
    <div class="mb-6 p-4 bg-red-50 border-s-4 border-red-500 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ms-3">
                <h3 class="text-sm font-bold text-red-800">
                    {{ app()->getLocale() === 'ar' ? 'فشل تسجيل الدخول' : 'Login Failed' }}
                </h3>
                <div class="mt-2 text-sm text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endif
