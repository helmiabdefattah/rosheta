@extends('doctor.layouts.dashboard')

@section('title', __('app.chat.title'))
@section('page-title', __('app.chat.title'))
@section('page-description', __('app.chat.doctor_subtitle'))

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- The switch that governs the whole feature for this doctor. --}}
    <form method="POST" action="{{ route('doctor.chat.settings') }}"
          class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        @csrf
        @method('PUT')

        <h2 class="text-lg font-bold text-slate-800 mb-1">{{ __('app.chat.settings_title') }}</h2>
        <p class="text-sm text-slate-500 mb-5">{{ __('app.chat.settings_hint') }}</p>

        <label class="flex items-start gap-3 cursor-pointer mb-5">
            <input type="checkbox" name="chat_enabled" value="1" @checked($doctor->chat_enabled)
                   class="mt-1 w-5 h-5 rounded border-slate-300 text-primary focus:ring-primary">
            <span>
                <span class="block font-semibold text-slate-800">{{ __('app.chat.enable_label') }}</span>
                <span class="block text-sm text-slate-500">{{ __('app.chat.enable_hint') }}</span>
            </span>
        </label>

        <div class="mb-5">
            <label for="chat_window_days" class="block font-semibold text-slate-800 mb-1">
                {{ __('app.chat.window_label') }}
            </label>
            <p class="text-sm text-slate-500 mb-2">{{ __('app.chat.window_hint') }}</p>
            <div class="flex items-center gap-2">
                <input type="number" id="chat_window_days" name="chat_window_days" min="1" max="365"
                       value="{{ old('chat_window_days', $doctor->chatWindowDays()) }}"
                       class="w-28 rounded-lg border border-slate-200 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary/30 focus:border-primary">
                <span class="text-sm text-slate-500">{{ __('app.chat.days') }}</span>
            </div>
        </div>

        <button type="submit" class="px-5 py-2.5 rounded-lg bg-primary text-white font-semibold hover:bg-primary/90 transition-colors">
            {{ __('app.chat.save') }}
        </button>
    </form>

    {{-- Inbox. Rows hand the thread id to the header widget, which owns the modal. --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-800">{{ __('app.chat.inbox') }}</h2>
            <span class="text-sm text-slate-400">{{ $threads->count() }}</span>
        </div>

        @forelse($threads as $thread)
            <x-chat-thread-row :thread="$thread" />
        @empty
            <p class="px-6 py-10 text-center text-slate-400">{{ __('app.chat.empty') }}</p>
        @endforelse
    </div>
</div>
@endsection
