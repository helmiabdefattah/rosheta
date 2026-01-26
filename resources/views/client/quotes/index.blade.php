@extends('client.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'استفساراتي' : 'My Quotes')

@section('page-title', app()->getLocale() === 'ar' ? 'استفساراتي' : 'My Quotes')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-2xl font-bold text-slate-800 mb-2">
                {{ app()->getLocale() === 'ar' ? 'استفساراتي' : 'My Quotes' }}
            </h2>
            <p class="text-slate-600">
                {{ app()->getLocale() === 'ar'
                    ? 'عرض جميع الاستفسارات التي أرسلتها للمعامل والصيدليات'
                    : 'View all quotes you sent to laboratories and pharmacies' }}
            </p>
        </div>

        {{-- Quotes List --}}
        @if($quotes->count() > 0)
            <div class="space-y-4">
                @foreach($quotes as $quote)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <div class="p-6">
                            {{-- Quote Header --}}
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        @if($quote->model_type === 'App\Models\Laboratory')
                                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                                </svg>
                                            </div>
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                                                <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                                </svg>
                                            </div>
                                        @endif
                                        <div>
                                            <h3 class="text-lg font-semibold text-slate-800">
{{--                                                {{dd($quote->model->name)}}--}}
                                                @if($quote->model)
                                                    {{ $quote->model->name ?? 'N/A' }}
                                                @else
                                                    {{ app()->getLocale() === 'ar' ? 'غير متوفر' : 'Not Available' }}
                                                @endif
                                            </h3>
                                            <p class="text-sm text-slate-500">
                                                @if($quote->model_type === 'App\Models\Laboratory')
                                                    {{ app()->getLocale() === 'ar' ? 'مختبر' : 'Laboratory' }}
                                                @else
                                                    {{ app()->getLocale() === 'ar' ? 'صيدلية' : 'Pharmacy' }}
                                                @endif
                                                • {{ $quote->created_at->format('Y-m-d H:i') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                @if($quote->reply)
                                    <span class="px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                        {{ app()->getLocale() === 'ar' ? 'تم الرد' : 'Replied' }}
                                    </span>
                                @else
                                    <span class="px-3 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                                        {{ app()->getLocale() === 'ar' ? 'في انتظار الرد' : 'Pending Reply' }}
                                    </span>
                                @endif
                            </div>

                            {{-- Quote Content --}}
                            <div class="mb-4">
                                <p class="text-sm font-medium text-slate-600 mb-2">
                                    {{ app()->getLocale() === 'ar' ? 'استفساري:' : 'My Quote:' }}
                                </p>
                                <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                                    <p class="text-slate-800 whitespace-pre-wrap">{{ $quote->quote }}</p>
                                </div>
                            </div>

                            {{-- Reply Section --}}
                            @if($quote->reply)
                                <div class="mb-4">
                                    <p class="text-sm font-medium text-slate-600 mb-2">
                                        {{ app()->getLocale() === 'ar' ? 'الرد:' : 'Reply:' }}
                                    </p>
                                    <div class="bg-primary-50 rounded-lg p-4 border border-primary-200">
                                        <p class="text-slate-800 whitespace-pre-wrap">{{ $quote->reply }}</p>
                                        <p class="text-xs text-slate-500 mt-2">
                                            {{ app()->getLocale() === 'ar' ? 'تم الرد في:' : 'Replied at:' }}
                                            {{ $quote->updated_at->format('Y-m-d H:i') }}
                                        </p>
                                    </div>
                                </div>
                            @else
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <p class="text-sm text-yellow-800">
                                        <svg class="w-4 h-4 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ app()->getLocale() === 'ar'
                                            ? 'لم يتم الرد على استفسارك بعد. سيتم إشعارك عند الرد.'
                                            : 'No reply yet. You will be notified when a reply is sent.' }}
                                    </p>
                                </div>
                            @endif

                            {{-- Contact Info if available --}}
                            @if($quote->model)
                                <div class="mt-4 pt-4 border-t border-gray-200">
{{--                                    <div class="flex flex-wrap gap-4 text-sm text-slate-600">--}}
{{--                                        @if($quote->model->phone)--}}
{{--                                            <div class="flex items-center gap-2">--}}
{{--                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">--}}
{{--                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>--}}
{{--                                                </svg>--}}
{{--                                                <span>{{ $quote->model->phone }}</span>--}}
{{--                                            </div>--}}
{{--                                        @endif--}}
{{--                                        @if($quote->model->email)--}}
{{--                                            <div class="flex items-center gap-2">--}}
{{--                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">--}}
{{--                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>--}}
{{--                                                </svg>--}}
{{--                                                <span>{{ $quote->model->email }}</span>--}}
{{--                                            </div>--}}
{{--                                        @endif--}}
{{--                                    </div>--}}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $quotes->links() }}
            </div>
        @else
            <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
                <p class="text-gray-500 text-lg mb-4">
                    {{ app()->getLocale() === 'ar' ? 'لم تقم بإرسال أي استفسارات بعد' : 'You haven\'t sent any quotes yet' }}
                </p>
                <a href="{{ route('client.laboratories.index') }}" class="inline-block px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    {{ app()->getLocale() === 'ar' ? 'تصفح المعامل' : 'Browse Laboratories' }}
                </a>
            </div>
        @endif
    </div>
@endsection
