@extends('clinic.layouts.kiosk')
@section('title', __('app.kiosk.title'))

@section('content')
    <div class="text-center mb-8 short:mb-3">
        <div class="text-5xl mb-3 short:hidden">👋</div>
        <h1 class="text-3xl short:text-xl font-extrabold text-slate-900">
            {{ __('app.kiosk.hello', ['name' => $patient->name]) }}
        </h1>
        <p class="mt-2 short:mt-0.5 text-lg short:text-sm text-slate-500">{{ __('app.kiosk.no_appointment_today') }}</p>
    </div>

    <form method="POST" action="{{ route('practice.kiosk.book', $clinic) }}">
        @csrf
        <input type="hidden" name="client_id" value="{{ $patient->id }}">

        <p class="text-center text-lg short:text-base font-semibold text-slate-700 mb-4 short:mb-2">{{ __('app.kiosk.choose_type') }}</p>

        <div class="grid sm:grid-cols-2 gap-4 short:gap-3">
            <button type="submit" name="type" value="examination"
                    class="py-6 short:py-4 px-4 rounded-2xl border-2 border-indigo-200 bg-indigo-50 hover:bg-indigo-100 active:scale-95 transition text-center">
                <div class="text-3xl short:text-2xl mb-2 short:mb-1">🩺</div>
                <div class="text-xl short:text-lg font-bold text-indigo-700">{{ __('app.types.examination') }}</div>
            </button>
            <button type="submit" name="type" value="consultation"
                    class="py-6 short:py-4 px-4 rounded-2xl border-2 border-violet-200 bg-violet-50 hover:bg-violet-100 active:scale-95 transition text-center">
                <div class="text-3xl short:text-2xl mb-2 short:mb-1">💬</div>
                <div class="text-xl short:text-lg font-bold text-violet-700">{{ __('app.types.consultation') }}</div>
            </button>
        </div>
    </form>

    <a href="{{ route('practice.kiosk.welcome', $clinic) }}"
       class="block text-center mt-7 short:mt-3 text-slate-400 hover:text-slate-600 text-lg short:text-sm">
        {{ __('app.kiosk.not_me') }}
    </a>
@endsection
