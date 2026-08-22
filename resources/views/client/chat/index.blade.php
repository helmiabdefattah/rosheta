@extends('client.layouts.dashboard')

@section('title', __('app.chat.title'))
@section('page-title', __('app.chat.title'))
@section('page-description', __('app.chat.client_subtitle'))

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Doctors the patient may start a new thread with (window still open). --}}
    @if($availableDoctors->isNotEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-slate-800 mb-1">{{ __('app.chat.start_new') }}</h2>
            <p class="text-sm text-slate-500 mb-4">{{ __('app.chat.start_new_hint') }}</p>

            <div class="grid gap-3 sm:grid-cols-2">
                @foreach($availableDoctors as $available)
                    <form method="POST" action="{{ route('client.chat.start', $available) }}">
                        @csrf
                        <button type="submit"
                                class="w-full text-start flex items-center gap-3 p-3 rounded-xl border border-slate-200 hover:border-primary hover:bg-primary/5 transition-colors">
                            <img src="{{ $available->getFirstMediaUrl('profile_image') ?: 'https://ui-avatars.com/api/?name=' . urlencode($available->name) . '&background=0d9488&color=fff' }}"
                                 alt="" class="w-10 h-10 rounded-full object-cover shrink-0">
                            <span class="min-w-0">
                                <span class="block font-semibold text-slate-800 truncate">{{ $available->name }}</span>
                                <span class="block text-xs text-slate-500 truncate">{{ $available->specialization?->name }}</span>
                            </span>
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Threads. Rows hand the id to the header widget, which owns the modal. --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-lg font-bold text-slate-800">{{ __('app.chat.inbox') }}</h2>
        </div>

        @forelse($threads as $thread)
            <x-chat-thread-row :thread="$thread" />
        @empty
            <p class="px-6 py-10 text-center text-slate-400">{{ __('app.chat.empty_client') }}</p>
        @endforelse
    </div>
</div>
@endsection
