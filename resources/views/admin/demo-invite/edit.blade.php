@extends('admin.layouts.admin')

@php($ar = app()->getLocale() === 'ar')

@section('title', $ar ? 'دعوة التجربة المجانية' : 'Free Trial Invitation')
@section('page-title', $ar ? 'دعوة التجربة المجانية' : 'Free Trial Invitation')
@section('page-description', $ar
    ? 'زر "جرّب مجاناً" الذي يظهر في الصفحة الرئيسية وصفحة تسجيل الدخول'
    : 'The "Try it free" button shown on the landing page and the login page')

@section('content')
@if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <p class="text-sm text-green-800">{{ session('success') }}</p>
    </div>
@endif

@if($errors->any())
    <div class="bg-rose-50 border border-rose-200 rounded-lg p-4 mb-6">
        <ul class="text-sm text-rose-800 space-y-1 list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- On the demo deployment itself the button is pointless — the visitor is
     already inside the thing it links to — so it is hidden there whatever
     these settings say. Worth stating on screen, so nobody spends an
     afternoon wondering why the switch appears to do nothing. --}}
@if(config('demo.enabled'))
    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
        <p class="text-sm text-amber-800">
            {{ $ar
                ? 'هذه النسخة هي نظام التجربة نفسه (DEMO_ENABLED=true)، لذلك لن يظهر زر "جرّب مجاناً" هنا مهما كانت الإعدادات. اضبط هذه الإعدادات من لوحة تحكم النظام الأساسي.'
                : 'This installation IS the demo system (DEMO_ENABLED=true), so the "Try it free" button stays hidden here whatever you set. Change these settings from the production admin panel instead.' }}
        </p>
    </div>
@endif

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 max-w-2xl">
    <form method="POST" action="{{ route('admin.demo-invite.update') }}" class="p-6 space-y-6">
        @csrf
        @method('PUT')

        <div class="flex items-start gap-3">
            <input
                type="checkbox" name="enabled" id="enabled" value="1"
                {{ old('enabled', $enabled) ? 'checked' : '' }}
                class="mt-1 h-5 w-5 text-primary focus:ring-primary border-gray-300 rounded cursor-pointer"
            >
            <label for="enabled" class="cursor-pointer select-none">
                <span class="block text-sm font-bold text-slate-900">
                    {{ $ar ? 'إظهار زر "جرّب مجاناً"' : 'Show the "Try it free" button' }}
                </span>
                <span class="block text-xs text-slate-500 mt-1 leading-relaxed">
                    {{ $ar
                        ? 'يظهر في الصفحة الرئيسية وصفحة تسجيل الدخول، ويفتح نظام التجربة في نافذة جديدة.'
                        : 'Appears on the landing page and the login page, and opens the demo system in a new tab.' }}
                </span>
            </label>
        </div>

        <div>
            <label for="url" class="block text-sm font-semibold text-slate-700 mb-2">
                {{ $ar ? 'رابط نظام التجربة' : 'Demo system URL' }}
            </label>
            <input
                type="url" name="url" id="url" dir="ltr"
                value="{{ old('url', $url) }}"
                placeholder="https://demo.example.com"
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
            >
            <p class="mt-2 text-xs text-slate-400 leading-relaxed">
                {{ $ar
                    ? 'عنوان النسخة التي تعمل بوضع التجربة. الزر لا يظهر إذا كان الحقل فارغاً.'
                    : 'Address of the installation running in demo mode. The button stays hidden while this is empty.' }}
            </p>
        </div>

        <div class="flex items-center gap-3 pt-2 border-t border-slate-100">
            <button type="submit" class="px-6 py-2.5 bg-primary text-white rounded-lg font-bold text-sm hover:bg-blue-700 transition-colors">
                {{ $ar ? 'حفظ' : 'Save' }}
            </button>

            @if($url)
                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                   class="px-6 py-2.5 border border-slate-200 text-slate-700 rounded-lg font-bold text-sm hover:bg-slate-50 transition-colors">
                    {{ $ar ? 'فتح نظام التجربة' : 'Open the demo system' }}
                </a>
            @endif
        </div>
    </form>
</div>
@endsection
