@extends('admin.layouts.admin')

@php $l = app()->getLocale() === 'ar'; @endphp

@section('title', 'Subscription Requests')
@section('page-title', $l ? 'طلبات الاشتراك' : 'Subscription Requests')
@section('page-description', $l ? 'الطلبات الواردة من صفحة الأسعار' : 'Requests submitted from the pricing page')

@section('content')
    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @php
            $cards = [
                ['label' => $l ? 'إجمالي الطلبات' : 'Total', 'value' => $stats['total'], 'color' => 'text-slate-900'],
                ['label' => $l ? 'جديدة' : 'New', 'value' => $stats['new'], 'color' => 'text-blue-600'],
                ['label' => $l ? 'تم التواصل' : 'Contacted', 'value' => $stats['contacted'], 'color' => 'text-amber-600'],
                ['label' => $l ? 'تم التفعيل' : 'Activated', 'value' => $stats['activated'], 'color' => 'text-emerald-600'],
            ];
        @endphp
        @foreach ($cards as $c)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <p class="text-sm text-slate-500">{{ $c['label'] }}</p>
                <p class="text-3xl font-black {{ $c['color'] }} mt-1">{{ $c['value'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[180px]">
            <label class="block text-xs font-bold text-slate-500 mb-1">{{ $l ? 'بحث' : 'Search' }}</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ $l ? 'الاسم، الهاتف، العيادة...' : 'name, phone, clinic...' }}" class="w-full px-3 py-2 rounded-lg border border-slate-200 outline-none focus:border-primary">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 mb-1">{{ $l ? 'الحالة' : 'Status' }}</label>
            <select name="status" class="px-3 py-2 rounded-lg border border-slate-200 outline-none focus:border-primary">
                <option value="">{{ $l ? 'الكل' : 'All' }}</option>
                <option value="new" @selected(request('status') === 'new')>{{ $l ? 'جديدة' : 'New' }}</option>
                <option value="contacted" @selected(request('status') === 'contacted')>{{ $l ? 'تم التواصل' : 'Contacted' }}</option>
                <option value="activated" @selected(request('status') === 'activated')>{{ $l ? 'تم التفعيل' : 'Activated' }}</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 mb-1">{{ $l ? 'الباقة' : 'Plan' }}</label>
            <select name="plan" class="px-3 py-2 rounded-lg border border-slate-200 outline-none focus:border-primary">
                <option value="">{{ $l ? 'الكل' : 'All' }}</option>
                <option value="starter" @selected(request('plan') === 'starter')>Starter</option>
                <option value="equipped" @selected(request('plan') === 'equipped')>Equipped</option>
                <option value="multi" @selected(request('plan') === 'multi')>Multi-Clinic</option>
            </select>
        </div>
        <button type="submit" class="px-5 py-2 bg-primary text-white rounded-lg font-semibold hover:opacity-90">{{ $l ? 'تصفية' : 'Filter' }}</button>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-start">#</th>
                        <th class="px-4 py-3 text-start">{{ $l ? 'التاريخ' : 'Submitted' }}</th>
                        <th class="px-4 py-3 text-start">{{ $l ? 'الباقة' : 'Plan' }}</th>
                        <th class="px-4 py-3 text-start">{{ $l ? 'جهة التواصل' : 'Contact' }}</th>
                        <th class="px-4 py-3 text-start">{{ $l ? 'الطبيب / العيادة' : 'Doctor / Clinic' }}</th>
                        <th class="px-4 py-3 text-center">{{ $l ? 'مساعدون' : 'Assistants' }}</th>
                        <th class="px-4 py-3 text-start">{{ $l ? 'الحالة' : 'Status' }}</th>
                        <th class="px-4 py-3 text-end">{{ $l ? 'إجراء' : 'Action' }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($requests as $r)
                        <tr class="hover:bg-slate-50/60">
                            <td class="px-4 py-3 font-mono text-slate-400">{{ $r->id }}</td>
                            <td class="px-4 py-3 text-slate-600 whitespace-nowrap">{{ $r->created_at->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3">
                                <span class="font-semibold text-slate-800">{{ $r->planLabel() }}</span>
                                <span class="block text-xs text-slate-400">{{ $r->billing === 'annual' ? ($l ? 'سنوي' : 'Annual') : ($l ? 'شهري' : 'Monthly') }}@if($r->with_profile_site) · {{ $l ? 'موقع' : 'Site' }}@endif</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-semibold text-slate-800">{{ $r->contact_name }}</span>
                                <span class="block text-xs text-slate-500" dir="ltr">{{ $r->contact_phone }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-slate-800">{{ $r->doctor_name ?: '—' }}</span>
                                <span class="block text-xs text-slate-400">{{ $r->clinic_name ?: ($r->clinic_city ?: '') }}</span>
                            </td>
                            <td class="px-4 py-3 text-center text-slate-600">{{ $r->assistantsCount() }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $badge = match ($r->status) {
                                        'activated' => 'bg-emerald-100 text-emerald-700',
                                        'contacted' => 'bg-amber-100 text-amber-700',
                                        default => 'bg-blue-100 text-blue-700',
                                    };
                                    $statusLabel = match ($r->status) {
                                        'activated' => $l ? 'تم التفعيل' : 'Activated',
                                        'contacted' => $l ? 'تم التواصل' : 'Contacted',
                                        default => $l ? 'جديدة' : 'New',
                                    };
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $badge }}">{{ $statusLabel }}</span>
                            </td>
                            <td class="px-4 py-3 text-end">
                                <a href="{{ route('admin.subscription-requests.show', $r) }}" class="text-primary font-semibold hover:underline">{{ $l ? 'عرض' : 'View' }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-slate-400">{{ $l ? 'لا توجد طلبات بعد.' : 'No requests yet.' }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($requests->hasPages())
            <div class="p-4 border-t border-slate-100">{{ $requests->links() }}</div>
        @endif
    </div>
@endsection
