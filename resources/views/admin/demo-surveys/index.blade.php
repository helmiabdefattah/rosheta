@extends('admin.layouts.admin')

@php($ar = app()->getLocale() === 'ar')

@section('title', $ar ? 'آراء المجربين' : 'Demo Feedback')
@section('page-title', $ar ? 'آراء المجربين' : 'Demo Feedback')
@section('page-description', $ar ? 'ما قاله زوار بيئة التجربة عند إنهائها' : 'What demo visitors said when they ended the trial')

@section('content')
@if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <p class="text-sm text-green-800">{{ session('success') }}</p>
    </div>
@endif

<!-- The numbers worth seeing before reading a single answer -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
        <div class="text-xs font-semibold text-slate-500 mb-1">{{ $ar ? 'إجمالي الردود' : 'Responses' }}</div>
        <div class="text-2xl font-black text-slate-900">{{ number_format($stats['total']) }}</div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
        <div class="text-xs font-semibold text-slate-500 mb-1">{{ $ar ? 'قالوا مفيد' : 'Said useful' }}</div>
        <div class="text-2xl font-black text-emerald-600">{{ number_format($stats['useful']) }}</div>
        @if($stats['total'] > 0)
            <div class="text-xs text-slate-400 mt-1">{{ round($stats['useful'] / $stats['total'] * 100) }}%</div>
        @endif
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
        <div class="text-xs font-semibold text-slate-500 mb-1">{{ $ar ? 'قالوا غير مفيد' : 'Said not useful' }}</div>
        <div class="text-2xl font-black text-rose-600">{{ number_format($stats['not_useful']) }}</div>
        @if($stats['total'] > 0)
            <div class="text-xs text-slate-400 mt-1">{{ round($stats['not_useful'] / $stats['total'] * 100) }}%</div>
        @endif
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
        <div class="text-xs font-semibold text-slate-500 mb-1">{{ $ar ? 'كتبوا ملاحظات' : 'Wrote comments' }}</div>
        <div class="text-2xl font-black text-sky-600">{{ number_format($stats['with_text']) }}</div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <!-- Filters -->
    <div class="p-6 border-b border-slate-200">
        <form method="GET" action="{{ route('admin.demo-surveys.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="{{ $ar ? 'ابحث في الملاحظات...' : 'Search comments...' }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                >
            </div>
            <div>
                <select name="useful" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    <option value="">{{ $ar ? 'كل الردود' : 'All answers' }}</option>
                    <option value="yes" {{ request('useful') === 'yes' ? 'selected' : '' }}>
                        {{ $ar ? 'مفيد' : 'Useful' }}
                    </option>
                    <option value="no" {{ request('useful') === 'no' ? 'selected' : '' }}>
                        {{ $ar ? 'غير مفيد' : 'Not useful' }}
                    </option>
                </select>
            </div>
            <div>
                <select name="has_text" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    <option value="">{{ $ar ? 'بملاحظات وبدونها' : 'With and without comments' }}</option>
                    <option value="1" {{ request('has_text') === '1' ? 'selected' : '' }}>
                        {{ $ar ? 'الذين كتبوا ملاحظات فقط' : 'Only with comments' }}
                    </option>
                </select>
            </div>
            <div>
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200">
                    {{ $ar ? 'تصفية' : 'Filter' }}
                </button>
            </div>
            @if(request()->hasAny(['search', 'useful', 'has_text']))
                <div>
                    <a href="{{ route('admin.demo-surveys.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition duration-200">
                        {{ $ar ? 'إعادة تعيين' : 'Reset' }}
                    </a>
                </div>
            @endif
        </form>
    </div>

    <!-- Responses -->
    <div class="p-6">
        @if($surveys->count() > 0)
            <div class="space-y-4">
                @foreach($surveys as $survey)
                    <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between gap-4 mb-4">
                            <div class="flex flex-wrap items-center gap-3">
                                @if($survey->was_useful)
                                    <span class="px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                        👍 {{ $ar ? 'النظام مفيد' : 'Useful' }}
                                    </span>
                                @else
                                    <span class="px-3 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">
                                        👎 {{ $ar ? 'غير مفيد' : 'Not useful' }}
                                    </span>
                                @endif

                                @if($survey->role)
                                    <span class="px-3 py-1 text-xs font-medium rounded-full bg-slate-100 text-slate-700">
                                        {{ $survey->role === 'assistant' ? ($ar ? 'جرّب كمساعد' : 'As assistant') : ($ar ? 'جرّب كطبيب' : 'As doctor') }}
                                    </span>
                                @endif

                                @if($survey->specialty)
                                    <span class="px-3 py-1 text-xs font-medium rounded-full bg-sky-100 text-sky-800">
                                        {{ $survey->specialty }}
                                    </span>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('admin.demo-surveys.destroy', $survey) }}"
                                  onsubmit="return confirm('{{ $ar ? 'سيتم حذف هذا الرأي نهائياً. متابعة؟' : 'This response will be deleted permanently. Continue?' }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 text-xs font-semibold text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition duration-200">
                                    {{ $ar ? 'حذف' : 'Delete' }}
                                </button>
                            </form>
                        </div>

                        @if($survey->wants_added || $survey->wants_removed)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div class="rounded-lg bg-emerald-50/60 border border-emerald-100 p-4">
                                    <div class="text-xs font-bold text-emerald-800 mb-1.5">
                                        {{ $ar ? 'يريد إضافة' : 'Would add' }}
                                    </div>
                                    <p class="text-sm text-slate-700 whitespace-pre-line">
                                        {{ $survey->wants_added ?: ($ar ? '— لم يكتب شيئاً' : '— nothing written') }}
                                    </p>
                                </div>

                                <div class="rounded-lg bg-rose-50/60 border border-rose-100 p-4">
                                    <div class="text-xs font-bold text-rose-800 mb-1.5">
                                        {{ $ar ? 'لم يعجبه / يريد حذفه' : 'Disliked / would remove' }}
                                    </div>
                                    <p class="text-sm text-slate-700 whitespace-pre-line">
                                        {{ $survey->wants_removed ?: ($ar ? '— لم يكتب شيئاً' : '— nothing written') }}
                                    </p>
                                </div>
                            </div>
                        @else
                            <p class="text-sm text-gray-400 mb-4">
                                {{ $ar ? 'أجاب على السؤال الأول فقط دون كتابة ملاحظات.' : 'Answered the first question only, with no written comments.' }}
                            </p>
                        @endif

                        <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500">
                            <span>
                                <strong>{{ $ar ? 'التاريخ:' : 'Date:' }}</strong>
                                {{ $survey->created_at->format('Y-m-d H:i') }}
                            </span>

                            @if($survey->session)
                                <span>
                                    <strong>{{ $ar ? 'مدة التجربة:' : 'Trial length:' }}</strong>
                                    @if($survey->session->started_at && $survey->session->ended_at)
                                        {{ $survey->session->started_at->diffInMinutes($survey->session->ended_at) }}
                                        {{ $ar ? 'دقيقة' : 'min' }}
                                    @else
                                        —
                                    @endif
                                </span>
                                @if($survey->session->utm_source)
                                    <span>
                                        <strong>{{ $ar ? 'المصدر:' : 'Source:' }}</strong>
                                        {{ $survey->session->utm_source }}
                                    </span>
                                @endif
                            @else
                                <span class="text-gray-400">
                                    {{ $ar ? 'رأي غير مرتبط بجلسة تجربة' : 'Not linked to a demo session' }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $surveys->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <div class="text-5xl mb-4">🧪</div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">
                    {{ $ar ? 'لا توجد آراء بعد' : 'No responses yet' }}
                </h3>
                <p class="text-gray-500">
                    {{ $ar
                        ? 'تظهر هنا إجابات الزوار على الأسئلة الثلاثة عند إنهاء بيئة التجربة.'
                        : 'Answers to the three questions asked when a demo ends will appear here.' }}
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
