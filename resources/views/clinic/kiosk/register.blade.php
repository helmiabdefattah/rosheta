@extends('clinic.layouts.kiosk')
@section('title', __('app.kiosk.register_title'))

@section('content')
    <div class="text-center mb-8">
        <div class="text-5xl mb-3">📝</div>
        <h1 class="text-3xl font-extrabold text-slate-900">{{ __('app.kiosk.new_patient') }}</h1>
        <p class="mt-2 text-lg text-slate-500">{{ __('app.kiosk.register_intro') }}</p>
    </div>

    <form method="POST" action="{{ route('practice.kiosk.store', $clinic) }}" class="space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-semibold text-slate-600 mb-1">{{ __('app.appointment.patient_name') }}</label>
            <input type="text" name="name" value="{{ old('name') }}" required autofocus
                   class="w-full text-xl py-4 px-4 rounded-2xl border-2 border-slate-200 focus:border-indigo-500 focus:ring-0 outline-none">
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-600 mb-1">{{ __('app.common.phone') }}</label>
            <input type="tel" name="phone" value="{{ old('phone', $phone) }}" required dir="ltr"
                   class="w-full text-xl py-4 px-4 rounded-2xl border-2 border-slate-200 focus:border-indigo-500 focus:ring-0 outline-none">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-600 mb-1">{{ __('app.common.gender') }}</label>
                <select name="gender"
                        class="w-full text-xl py-4 px-4 rounded-2xl border-2 border-slate-200 focus:border-indigo-500 focus:ring-0 outline-none bg-white">
                    <option value="">—</option>
                    <option value="male" @selected(old('gender') === 'male')>{{ __('app.genders.male') }}</option>
                    <option value="female" @selected(old('gender') === 'female')>{{ __('app.genders.female') }}</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-600 mb-1">{{ __('app.kiosk.age') }}</label>
                <input type="number" name="age" value="{{ old('age') }}" min="0" max="150" inputmode="numeric"
                       class="w-full text-xl py-4 px-4 rounded-2xl border-2 border-slate-200 focus:border-indigo-500 focus:ring-0 outline-none">
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-600 mb-2">{{ __('app.kiosk.choose_type') }}</label>
            <div class="grid grid-cols-2 gap-3">
                <label class="flex items-center gap-3 py-4 px-4 rounded-2xl border-2 border-slate-200 cursor-pointer has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50">
                    <input type="radio" name="type" value="examination" class="h-5 w-5" checked>
                    <span class="text-lg font-semibold">🩺 {{ __('app.types.examination') }}</span>
                </label>
                <label class="flex items-center gap-3 py-4 px-4 rounded-2xl border-2 border-slate-200 cursor-pointer has-[:checked]:border-violet-500 has-[:checked]:bg-violet-50">
                    <input type="radio" name="type" value="consultation" class="h-5 w-5">
                    <span class="text-lg font-semibold">💬 {{ __('app.types.consultation') }}</span>
                </label>
            </div>
        </div>

        <button type="submit"
                class="w-full mt-2 py-5 text-2xl font-extrabold rounded-2xl bg-indigo-600 text-white hover:bg-indigo-700 active:scale-95 transition">
            {{ __('app.kiosk.register_book') }} →
        </button>
    </form>

    <a href="{{ route('practice.kiosk.welcome', $clinic) }}"
       class="block text-center mt-6 text-slate-400 hover:text-slate-600 text-lg">
        {{ __('app.common.back') }}
    </a>
@endsection
