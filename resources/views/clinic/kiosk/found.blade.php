@extends('clinic.layouts.kiosk')
@section('title', __('app.kiosk.title'))

@section('content')
    <div class="text-center mb-8">
        <div class="text-5xl mb-3">👋</div>
        <h1 class="text-3xl font-extrabold text-slate-900">
            {{ __('app.kiosk.hello', ['name' => $patient->name]) }}
        </h1>
        <p class="mt-2 text-lg text-slate-500">{{ __('app.kiosk.no_appointment_today') }}</p>
    </div>

    <form method="POST" action="{{ route('practice.kiosk.book', $clinic) }}">
        @csrf
        <input type="hidden" name="client_id" value="{{ $patient->id }}">

        <p class="text-center text-lg font-semibold text-slate-700 mb-4">{{ __('app.kiosk.choose_type') }}</p>

        <div class="grid sm:grid-cols-2 gap-4">
            <button type="submit" name="type" value="examination"
                    class="py-6 px-4 rounded-2xl border-2 border-indigo-200 bg-indigo-50 hover:bg-indigo-100 active:scale-95 transition text-center">
                <div class="text-3xl mb-2">🩺</div>
                <div class="text-xl font-bold text-indigo-700">{{ __('app.types.examination') }}</div>
            </button>
            <button type="submit" name="type" value="consultation"
                    class="py-6 px-4 rounded-2xl border-2 border-violet-200 bg-violet-50 hover:bg-violet-100 active:scale-95 transition text-center">
                <div class="text-3xl mb-2">💬</div>
                <div class="text-xl font-bold text-violet-700">{{ __('app.types.consultation') }}</div>
            </button>
        </div>
    </form>

    <a href="{{ route('practice.kiosk.welcome', $clinic) }}"
       class="block text-center mt-7 text-slate-400 hover:text-slate-600 text-lg">
        {{ __('app.kiosk.not_me') }}
    </a>
@endsection
