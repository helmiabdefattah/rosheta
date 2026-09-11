@extends('admin.layouts.admin')

@php
    $ar = app()->getLocale() === 'ar';

    // Seconds -> "12د 30ث". Written here rather than as a helper because it is
    // the only place in the application that needs it.
    $dur = function (?int $seconds) use ($ar) {
        if ($seconds === null) {
            return '—';
        }

        $m = intdiv($seconds, 60);
        $s = $seconds % 60;

        if ($m >= 60) {
            return intdiv($m, 60).($ar ? 'س ' : 'h ').($m % 60).($ar ? 'د' : 'm');
        }

        return $m > 0 ? $m.($ar ? 'د ' : 'm ').$s.($ar ? 'ث' : 's') : $s.($ar ? 'ث' : 's');
    };

    $pct = fn ($part, $whole) => $whole > 0 ? round($part / $whole * 100) : 0;

    $reasonBadge = [
        'user_ended' => ['bg-slate-100 text-slate-700', $ar ? 'أنهاها بنفسه' : 'Ended it'],
        'expired' => ['bg-amber-100 text-amber-800', $ar ? 'انتهى الوقت' : 'Timed out'],
        'idle' => ['bg-orange-100 text-orange-800', $ar ? 'تركها' : 'Went idle'],
        'converted' => ['bg-emerald-100 text-emerald-800', $ar ? 'أنشأ حساباً' : 'Converted'],
        'purged' => ['bg-slate-100 text-slate-500', $ar ? 'أُغلقت' : 'Closed'],
    ];

    $ladderLabels = [
        'opened' => $ar ? 'دخل النظام' : 'Opened',
        'browsed' => $ar ? 'فتح ملف مريض' : 'Opened a patient',
        'booked' => $ar ? 'حجز موعداً' : 'Booked',
        'examined' => $ar ? 'كشف وشخّص' : 'Examined',
        'prescribed' => $ar ? 'كتب روشتة' : 'Prescribed',
        'ordered' => $ar ? 'طلب تحاليل' : 'Ordered tests',
        'billed' => $ar ? 'حصّل' : 'Took payment',
        'printed' => $ar ? 'طبع' : 'Printed',
        'configured' => $ar ? 'عدّل الإعدادات' : 'Configured',
    ];
@endphp

@section('title', $ar ? 'تحليل تجارب المنتج' : 'Demo Analytics')
@section('page-title', $ar ? 'تحليل تجارب المنتج' : 'Demo Analytics')
@section('page-description', $ar
    ? 'كل تجربة: ماذا فعل الزائر، إلى أين وصل، وماذا قال — حتى لو لم يكتب شيئاً'
    : 'Every trial run: what the visitor did, how far they got, and what they said — comment or no comment')

@section('content')
@if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <p class="text-sm text-green-800">{{ session('success') }}</p>
    </div>
@endif

<!-- The numbers worth seeing before reading a single run -->
<div class="grid grid-cols-2 lg:grid-cols-6 gap-4 mb-6">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
        <div class="text-xs font-semibold text-slate-500 mb-1">{{ $ar ? 'عدد التجارب' : 'Trial runs' }}</div>
        <div class="text-2xl font-black text-slate-900">{{ number_format($stats['runs']) }}</div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
        <div class="text-xs font-semibold text-slate-500 mb-1">{{ $ar ? 'جرّبوا فعلاً' : 'Actually used it' }}</div>
        <div class="text-2xl font-black text-emerald-600">{{ number_format($stats['engaged']) }}</div>
        <div class="text-xs text-slate-400 mt-1">{{ $pct($stats['engaged'], $stats['runs']) }}% {{ $ar ? 'نفّذوا إجراءً' : 'did something' }}</div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
        <div class="text-xs font-semibold text-slate-500 mb-1">{{ $ar ? 'متوسط الإجراءات' : 'Avg actions' }}</div>
        <div class="text-2xl font-black text-sky-600">{{ $stats['avg_actions'] }}</div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
        <div class="text-xs font-semibold text-slate-500 mb-1">{{ $ar ? 'متوسط وقت الاستخدام' : 'Avg active time' }}</div>
        <div class="text-2xl font-black text-indigo-600">{{ $stats['avg_active_minutes'] }}<span class="text-sm font-bold">{{ $ar ? ' د' : 'm' }}</span></div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
        <div class="text-xs font-semibold text-slate-500 mb-1">{{ $ar ? 'تركوا رأياً' : 'Left feedback' }}</div>
        <div class="text-2xl font-black text-slate-900">{{ number_format($stats['surveys']) }}</div>
        <div class="text-xs text-slate-400 mt-1">{{ $pct($stats['surveys'], $stats['runs']) }}% {{ $ar ? 'من التجارب' : 'of runs' }}</div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
        <div class="text-xs font-semibold text-slate-500 mb-1">{{ $ar ? 'قالوا مفيد' : 'Said useful' }}</div>
        <div class="text-2xl font-black text-emerald-600">{{ number_format($stats['useful']) }}</div>
        @if($stats['surveys'] > 0)
            <div class="text-xs text-slate-400 mt-1">{{ $pct($stats['useful'], $stats['surveys']) }}% · {{ $stats['not_useful'] }} {{ $ar ? 'غير مفيد' : 'not useful' }}</div>
        @endif
    </div>
</div>

<!-- Where people stop. The one chart that decides what to build next. -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h3 class="text-sm font-black text-slate-900 mb-1">{{ $ar ? 'إلى أين يصل المجرِّبون' : 'How far visitors get' }}</h3>
        <p class="text-xs text-slate-500 mb-4">
            {{ $ar ? 'عدد التجارب التي وصلت لكل مرحلة — المرحلة التي يسقط عندها الناس هي التي تحتاج عملاً' : 'Runs that reached each step — the step where people drop is the one that needs work' }}
        </p>

        <div class="space-y-2">
            @foreach($stats['ladder'] as $key => $count)
                <div class="flex items-center gap-3">
                    <div class="w-28 shrink-0 text-xs font-semibold text-slate-600">{{ $ladderLabels[$key] ?? $key }}</div>
                    <div class="flex-1 h-6 bg-slate-100 rounded-lg overflow-hidden">
                        <div class="h-full bg-gradient-to-l from-emerald-500 to-emerald-400 rounded-lg"
                             style="width: {{ max($pct($count, $stats['runs']), $count > 0 ? 2 : 0) }}%"></div>
                    </div>
                    <div class="w-20 shrink-0 text-xs font-bold text-slate-700 text-end">
                        {{ $count }} <span class="text-slate-400 font-normal">({{ $pct($count, $stats['runs']) }}%)</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h3 class="text-sm font-black text-slate-900 mb-1">{{ $ar ? 'أكثر أجزاء النظام استخداماً' : 'Most-used parts' }}</h3>
        <p class="text-xs text-slate-500 mb-4">{{ $ar ? 'بعدد التجارب التي فتحتها' : 'By number of runs that touched it' }}</p>

        @forelse($stats['features'] as $feature)
            <div class="flex items-center justify-between py-1.5 border-b border-slate-50 last:border-0">
                <span class="text-xs font-semibold text-slate-700">
                    {{ \App\Demo\DemoActivityCatalog::featureLabel($feature->feature, $ar) }}
                </span>
                <span class="text-xs text-slate-500">
                    {{ $feature->runs }} {{ $ar ? 'تجربة' : 'runs' }}
                    <span class="text-slate-300">·</span>
                    {{ $feature->events }}
                </span>
            </div>
        @empty
            <p class="text-xs text-slate-400">{{ $ar ? 'لا توجد بيانات بعد' : 'Nothing recorded yet' }}</p>
        @endforelse
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <!-- Filters -->
    <div class="p-6 border-b border-slate-200">
        <form method="GET" action="{{ route('admin.demo-surveys.index') }}" class="flex flex-wrap gap-3">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="{{ $ar ? 'ابحث في الملاحظات أو التخصص أو المصدر...' : 'Search comments, specialty, source...' }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
            </div>

            <select name="engagement" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">{{ $ar ? 'كل التجارب' : 'All runs' }}</option>
                <option value="engaged" {{ request('engagement') === 'engaged' ? 'selected' : '' }}>
                    {{ $ar ? 'التي نفّذت إجراءات' : 'Did something' }}
                </option>
                <option value="idle" {{ request('engagement') === 'idle' ? 'selected' : '' }}>
                    {{ $ar ? 'التي لم تفعل شيئاً' : 'Did nothing' }}
                </option>
            </select>

            <select name="useful" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">{{ $ar ? 'الرأي: الكل' : 'Feedback: any' }}</option>
                <option value="yes" {{ request('useful') === 'yes' ? 'selected' : '' }}>{{ $ar ? 'قال مفيد' : 'Said useful' }}</option>
                <option value="no" {{ request('useful') === 'no' ? 'selected' : '' }}>{{ $ar ? 'قال غير مفيد' : 'Said not useful' }}</option>
                <option value="none" {{ request('useful') === 'none' ? 'selected' : '' }}>{{ $ar ? 'بلا رأي' : 'No feedback' }}</option>
            </select>

            <select name="role" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">{{ $ar ? 'الدور: الكل' : 'Role: any' }}</option>
                <option value="doctor" {{ request('role') === 'doctor' ? 'selected' : '' }}>{{ $ar ? 'بدأ كطبيب' : 'Started as doctor' }}</option>
                <option value="assistant" {{ request('role') === 'assistant' ? 'selected' : '' }}>{{ $ar ? 'بدأ كمساعد' : 'Started as assistant' }}</option>
            </select>

            <select name="reason" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">{{ $ar ? 'النهاية: الكل' : 'Ending: any' }}</option>
                @foreach($reasonBadge as $key => $badge)
                    <option value="{{ $key }}" {{ request('reason') === $key ? 'selected' : '' }}>{{ $badge[1] }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200">
                {{ $ar ? 'تصفية' : 'Filter' }}
            </button>

            @if(request()->hasAny(['search', 'useful', 'has_text', 'engagement', 'role', 'reason']))
                <a href="{{ route('admin.demo-surveys.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    {{ $ar ? 'إعادة تعيين' : 'Reset' }}
                </a>
            @endif
        </form>
    </div>

    <!-- Runs -->
    <div class="p-6">
        @if($sessions->count() > 0)
            <div class="space-y-5">
                @foreach($sessions as $session)
                    @php
                        $analysis = $analyses[$session->id] ?? [];
                        $events = $timelines[$session->id] ?? collect();
                        $survey = $session->survey;
                        $badge = $reasonBadge[$session->end_reason] ?? null;
                        $created = array_filter((array) ($analysis['created'] ?? []));
                        $counts = $analysis['events'] ?? ['actions' => 0, 'pages' => 0, 'total' => 0];
                    @endphp

                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                        <!-- Who, when, how it ended -->
                        <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3 bg-slate-50 border-b border-gray-200">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-xs font-bold text-slate-900">
                                    {{ $session->started_at?->format('Y-m-d H:i') ?? '—' }}
                                </span>

                                @if($session->isRunning())
                                    <span class="px-2 py-0.5 text-[11px] font-bold rounded-full bg-emerald-100 text-emerald-800">
                                        ● {{ $ar ? 'جارية الآن' : 'Running now' }}
                                    </span>
                                @elseif($badge)
                                    <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full {{ $badge[0] }}">{{ $badge[1] }}</span>
                                @endif

                                <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-slate-200 text-slate-700">
                                    {{ $session->started_role === 'assistant' ? ($ar ? 'بدأ كمساعد' : 'As assistant') : ($ar ? 'بدأ كطبيب' : 'As doctor') }}
                                </span>

                                @if($session->specialty)
                                    <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-sky-100 text-sky-800">{{ $session->specialty }}</span>
                                @endif

                                @if($session->device)
                                    <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-slate-100 text-slate-600">
                                        {{ $session->device === 'mobile' ? ($ar ? 'موبايل' : 'Mobile') : ($ar ? 'كمبيوتر' : 'Desktop') }}
                                    </span>
                                @endif

                                @if($session->utm_source)
                                    <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-violet-100 text-violet-800">
                                        {{ $ar ? 'من' : 'via' }} {{ $session->utm_source }}
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center gap-3 text-[11px] text-slate-500">
                                <span>
                                    <strong class="text-slate-700">{{ $ar ? 'طول الجلسة:' : 'Session:' }}</strong>
                                    {{ $dur($session->durationSeconds()) }}
                                </span>
                                <span>
                                    <strong class="text-slate-700">{{ $ar ? 'وقت الاستخدام الفعلي:' : 'Active:' }}</strong>
                                    {{ $dur($analysis['active_seconds'] ?? $session->active_seconds) }}
                                </span>

                                <form method="POST" action="{{ route('admin.demo-runs.destroy', $session) }}"
                                      onsubmit="return confirm('{{ $ar ? 'سيتم حذف التجربة وكل سجلها نهائياً. متابعة؟' : 'The run and its whole record will be deleted. Continue?' }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2 py-1 text-[11px] font-semibold text-red-600 border border-red-200 rounded-lg hover:bg-red-50">
                                        {{ $ar ? 'حذف' : 'Delete' }}
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="p-5 space-y-4">
                            <!-- What they did, in one line -->
                            <div class="flex flex-wrap items-center gap-4 text-xs">
                                <span class="font-bold text-slate-900">
                                    {{ $counts['actions'] ?? 0 }} <span class="font-normal text-slate-500">{{ $ar ? 'إجراء' : 'actions' }}</span>
                                </span>
                                <span class="font-bold text-slate-900">
                                    {{ $counts['pages'] ?? 0 }} <span class="font-normal text-slate-500">{{ $ar ? 'شاشة' : 'screens' }}</span>
                                </span>
                                @if(($analysis['roles']['assistant'] ?? 0) > 0 && ($analysis['roles']['doctor'] ?? 0) > 0)
                                    <span class="text-slate-500">{{ $ar ? 'جرّب الدورين معاً' : 'Tried both roles' }}</span>
                                @endif
                                @if(($counts['actions'] ?? 0) === 0)
                                    <span class="px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 font-semibold">
                                        {{ $ar ? 'تصفّح فقط ولم ينفّذ أي إجراء' : 'Browsed only — changed nothing' }}
                                    </span>
                                @endif
                            </div>

                            <!-- How far up the ladder -->
                            @if(!empty($analysis['ladder']))
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($analysis['ladder'] as $key => $rung)
                                        <span class="px-2 py-1 text-[11px] font-semibold rounded-lg border
                                            {{ ($rung['reached'] ?? false)
                                                ? 'bg-emerald-50 border-emerald-200 text-emerald-800'
                                                : 'bg-slate-50 border-slate-200 text-slate-400' }}">
                                            {{ ($rung['reached'] ?? false) ? '✓' : '·' }}
                                            {{ $ar ? ($rung['ar'] ?? $key) : ($rung['en'] ?? $key) }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <!-- What they created inside the clinic -->
                            @if(!empty($created))
                                <div>
                                    <div class="text-[11px] font-bold text-slate-500 mb-1.5">
                                        {{ $ar ? 'أنشأ داخل العيادة' : 'Created inside the clinic' }}
                                        @if(($analysis['tenant_counted'] ?? false) === false)
                                            <span class="font-normal text-slate-400">({{ $ar ? 'حتى الآن' : 'so far' }})</span>
                                        @endif
                                    </div>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($created as $row)
                                            <span class="px-2.5 py-1 text-[11px] font-semibold rounded-lg bg-indigo-50 text-indigo-800 border border-indigo-100">
                                                {{ $row['count'] }} — {{ $ar ? ($row['ar'] ?? '') : ($row['en'] ?? '') }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @elseif(!($analysis['tenant_counted'] ?? true))
                                <p class="text-[11px] text-slate-400">
                                    {{ $ar ? 'التجربة ما زالت جارية — يُحسب ما أنشأه عند انتهائها.' : 'Run still in progress — what they created is counted when it ends.' }}
                                </p>
                            @endif

                            <!-- The individual things they did, most-repeated first -->
                            @if(!empty($analysis['top_actions']))
                                <div>
                                    <div class="text-[11px] font-bold text-slate-500 mb-1.5">{{ $ar ? 'ماذا فعل بالتحديد' : 'What exactly they did' }}</div>
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($analysis['top_actions'] as $action)
                                            <span class="px-2.5 py-1 text-[11px] rounded-lg bg-white border border-slate-200 text-slate-700">
                                                {{ $ar ? ($action['ar'] ?? $action['action']) : ($action['en'] ?? $action['action']) }}
                                                @if(($action['count'] ?? 1) > 1)
                                                    <span class="font-bold text-slate-900">×{{ $action['count'] }}</span>
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- What they said, if anything -->
                            @if($survey)
                                <div class="rounded-xl border border-slate-200 p-4 bg-slate-50/60">
                                    <div class="flex items-center gap-2 mb-3">
                                        @if($survey->was_useful)
                                            <span class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-green-100 text-green-800">
                                                👍 {{ $ar ? 'قال إن النظام مفيد' : 'Said it is useful' }}
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 text-[11px] font-bold rounded-full bg-red-100 text-red-800">
                                                👎 {{ $ar ? 'قال إنه غير مفيد' : 'Said it is not useful' }}
                                            </span>
                                        @endif

                                        <form method="POST" action="{{ route('admin.demo-surveys.destroy', $survey) }}" class="ms-auto"
                                              onsubmit="return confirm('{{ $ar ? 'سيتم حذف هذا الرأي فقط. متابعة؟' : 'Only this comment will be deleted. Continue?' }}');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-[11px] font-semibold text-red-600 hover:underline">
                                                {{ $ar ? 'حذف الرأي' : 'Delete comment' }}
                                            </button>
                                        </form>
                                    </div>

                                    @if($survey->wants_added || $survey->wants_removed)
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <div class="rounded-lg bg-emerald-50/60 border border-emerald-100 p-3">
                                                <div class="text-[11px] font-bold text-emerald-800 mb-1">{{ $ar ? 'يريد إضافة' : 'Would add' }}</div>
                                                <p class="text-sm text-slate-700 whitespace-pre-line">
                                                    {{ $survey->wants_added ?: ($ar ? '— لم يكتب شيئاً' : '— nothing written') }}
                                                </p>
                                            </div>
                                            <div class="rounded-lg bg-rose-50/60 border border-rose-100 p-3">
                                                <div class="text-[11px] font-bold text-rose-800 mb-1">{{ $ar ? 'لم يعجبه / يريد حذفه' : 'Disliked / would remove' }}</div>
                                                <p class="text-sm text-slate-700 whitespace-pre-line">
                                                    {{ $survey->wants_removed ?: ($ar ? '— لم يكتب شيئاً' : '— nothing written') }}
                                                </p>
                                            </div>
                                        </div>
                                    @else
                                        <p class="text-xs text-slate-400">{{ $ar ? 'أجاب على السؤال الأول فقط.' : 'Answered the first question only.' }}</p>
                                    @endif
                                </div>
                            @else
                                <p class="text-xs text-slate-400 border-t border-dashed border-slate-200 pt-3">
                                    {{ $ar
                                        ? 'لم يترك رأياً — التحليل أعلاه هو كل ما نعرفه عن هذه التجربة.'
                                        : 'Left no feedback — the analysis above is everything this run told us.' }}
                                </p>
                            @endif

                            <!-- The journey itself -->
                            @if($events->isNotEmpty())
                                <details class="group">
                                    <summary class="cursor-pointer text-xs font-bold text-slate-600 hover:text-slate-900 select-none">
                                        {{ $ar ? 'عرض الرحلة كاملة' : 'Show the full journey' }}
                                        <span class="font-normal text-slate-400">({{ $events->count() }} {{ $ar ? 'خطوة' : 'steps' }})</span>
                                    </summary>

                                    <ol class="mt-3 border-s-2 border-slate-100 ps-4 space-y-1.5">
                                        @foreach($events as $event)
                                            <li class="flex flex-wrap items-baseline gap-2 text-[11px]">
                                                <span class="font-mono text-slate-400 tabular-nums">{{ $event->occurred_at?->format('H:i:s') }}</span>

                                                <span class="px-1.5 py-0.5 rounded
                                                    {{ $event->kind === 'action' ? 'bg-emerald-50 text-emerald-700'
                                                        : ($event->kind === 'system' ? 'bg-violet-50 text-violet-700' : 'bg-slate-50 text-slate-500') }}">
                                                    {{ $event->kind === 'action' ? ($ar ? 'إجراء' : 'action')
                                                        : ($event->kind === 'system' ? ($ar ? 'النظام' : 'system') : ($ar ? 'شاشة' : 'screen')) }}
                                                </span>

                                                @if($event->role)
                                                    <span class="text-slate-400">{{ $event->role === 'assistant' ? ($ar ? 'مساعد' : 'assistant') : ($ar ? 'طبيب' : 'doctor') }}</span>
                                                @endif

                                                <span class="text-slate-800 font-medium">{{ $event->sentence($ar) }}</span>

                                                @if($event->path)
                                                    <span class="font-mono text-slate-300">/{{ $event->path }}</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ol>

                                    @if($events->count() >= 250)
                                        <p class="mt-2 text-[11px] text-slate-400">
                                            {{ $ar ? 'عُرضت أول ٢٥٠ خطوة فقط.' : 'Only the first 250 steps are shown.' }}
                                        </p>
                                    @endif
                                </details>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6">
                {{ $sessions->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <div class="text-5xl mb-4">🧪</div>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">{{ $ar ? 'لا توجد تجارب بعد' : 'No trial runs yet' }}</h3>
                <p class="text-gray-500">
                    {{ $ar
                        ? 'تظهر هنا كل تجربة يفتحها زائر من صفحة الدخول، بما فعله بالتفصيل.'
                        : 'Every trial a visitor opens from the login page appears here, with everything they did.' }}
                </p>
            </div>
        @endif
    </div>
</div>

<!-- Answers that arrived with no usable token: kept, attached to no run. -->
@if($orphanSurveys->isNotEmpty())
    <div class="mt-6 bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <h3 class="text-sm font-black text-slate-900 mb-1">{{ $ar ? 'آراء غير مرتبطة بتجربة' : 'Feedback with no run attached' }}</h3>
        <p class="text-xs text-slate-500 mb-4">
            {{ $ar ? 'وصلت بدون رمز صالح، فلا يمكن معرفة أي تجربة تخصّ.' : 'Arrived without a valid token, so there is no run to attach them to.' }}
        </p>

        <div class="space-y-3">
            @foreach($orphanSurveys as $survey)
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2 py-0.5 text-[11px] font-bold rounded-full {{ $survey->was_useful ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $survey->was_useful ? ($ar ? 'مفيد' : 'Useful') : ($ar ? 'غير مفيد' : 'Not useful') }}
                        </span>
                        <span class="text-[11px] text-slate-400">{{ $survey->created_at?->format('Y-m-d H:i') }}</span>

                        <form method="POST" action="{{ route('admin.demo-surveys.destroy', $survey) }}" class="ms-auto"
                              onsubmit="return confirm('{{ $ar ? 'سيتم حذف هذا الرأي. متابعة؟' : 'This comment will be deleted. Continue?' }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-[11px] font-semibold text-red-600 hover:underline">{{ $ar ? 'حذف' : 'Delete' }}</button>
                        </form>
                    </div>

                    @if($survey->wants_added)
                        <p class="text-xs text-slate-700"><strong>{{ $ar ? 'يريد إضافة:' : 'Would add:' }}</strong> {{ $survey->wants_added }}</p>
                    @endif
                    @if($survey->wants_removed)
                        <p class="text-xs text-slate-700"><strong>{{ $ar ? 'لم يعجبه:' : 'Disliked:' }}</strong> {{ $survey->wants_removed }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif
@endsection
