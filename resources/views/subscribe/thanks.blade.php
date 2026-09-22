@extends('layouts.app')

@section('title', app()->getLocale() === 'ar' ? 'تم استلام طلبك - مستشفي اون' : 'Request received - Mostashfa-on')

@php $isAr = app()->getLocale() === 'ar'; @endphp

@section('content')
    <section class="min-h-screen flex items-center justify-center bg-slate-50 pt-28 pb-20 px-4">
        <div class="max-w-lg w-full text-center bg-white rounded-[2.5rem] border border-slate-100 shadow-xl p-10 sm:p-12">
            <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>

            <h1 class="text-3xl font-black text-slate-900 mb-3">
                {{ $isAr ? 'تم استلام طلبك!' : 'Request received!' }}
            </h1>

            <p class="text-slate-600 leading-relaxed mb-2">
                {{ $isAr
                    ? 'شكرًا لك. سيتواصل معك فريقنا خلال 24 ساعة لتفعيل حسابك وإعداد عيادتك.'
                    : 'Thank you. Our team will contact you within 24 hours to activate your account and set up your clinic.' }}
            </p>
            <p class="text-sm text-slate-400 mb-8">
                {{ $isAr ? 'تأكد من أن رقم تواصلك متاح لاستقبال المكالمة.' : 'Please keep your contact number reachable for our call.' }}
            </p>

            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ url('/') }}" class="px-6 py-3 bg-slate-900 text-white rounded-xl font-bold hover:bg-slate-800 transition-all">
                    {{ $isAr ? 'العودة للرئيسية' : 'Back to home' }}
                </a>
                <a href="{{ route('pricing') }}" class="px-6 py-3 bg-white text-slate-700 border border-slate-200 rounded-xl font-bold hover:bg-slate-50 transition-all">
                    {{ $isAr ? 'عرض الباقات' : 'View plans' }}
                </a>
            </div>
        </div>
    </section>
@endsection
