@extends('admin.layouts.admin')

@php $l = app()->getLocale() === 'ar'; @endphp

@section('title', 'Subscription Request #' . $req->id)
@section('page-title', ($l ? 'طلب اشتراك رقم ' : 'Subscription Request #') . $req->id)
@section('page-description', $req->created_at->format('Y-m-d H:i'))

@section('header-actions')
    <a href="{{ route('admin.subscription-requests.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg hover:bg-slate-200 transition-all">
        {{ $l ? 'رجوع' : 'Back' }}
    </a>
@endsection

@section('content')
    @php
        $row = function ($label, $value) {
            $value = trim((string) $value);
            return '<div class="flex justify-between gap-4 py-2 border-b border-slate-50 last:border-0"><span class="text-slate-500">' . e($label) . '</span><span class="font-semibold text-slate-800 text-end" dir="auto">' . ($value !== '' ? e($value) : '—') . '</span></div>';
        };
    @endphp

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Main details --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Plan --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <h3 class="font-black text-slate-900 mb-3">{{ $l ? 'الباقة' : 'Plan' }}</h3>
                {!! $row($l ? 'الباقة' : 'Plan', $req->planLabel()) !!}
                {!! $row($l ? 'الفوترة' : 'Billing', $req->billing === 'annual' ? ($l ? 'سنوي (خصم 20%)' : 'Annual (20% off)') : ($l ? 'شهري' : 'Monthly')) !!}
                {!! $row($l ? 'موقع تعريفي' : 'Profile site', $req->with_profile_site ? ($l ? 'نعم' : 'Yes') : ($l ? 'لا' : 'No')) !!}
                @if ($req->with_profile_site && $req->profile_subdomain)
                    {!! $row($l ? 'النطاق' : 'Subdomain', $req->profile_subdomain . '.mostashfaon.com') !!}
                @endif
            </div>

            {{-- Doctor --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <h3 class="font-black text-slate-900 mb-3">{{ $l ? 'حساب الطبيب' : 'Doctor account' }}</h3>
                {!! $row($l ? 'الاسم' : 'Name', $req->doctor_name) !!}
                {!! $row($l ? 'التخصص' : 'Specialty', $req->doctor_specialty) !!}
                {!! $row($l ? 'اسم المستخدم' : 'Username', $req->doctor_username) !!}
                {!! $row($l ? 'الجوال' : 'Phone', $req->doctor_phone) !!}
                {!! $row($l ? 'البريد' : 'Email', $req->doctor_email) !!}
                <div class="flex justify-between gap-4 py-2">
                    <span class="text-slate-500">{{ $l ? 'كلمة المرور' : 'Password' }}</span>
                    <span class="font-mono font-semibold text-slate-800" dir="ltr">{{ $req->doctor_password ?: '—' }}</span>
                </div>
            </div>

            {{-- Clinic --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <h3 class="font-black text-slate-900 mb-3">{{ $l ? 'العيادة' : 'Clinic' }}</h3>
                {!! $row($l ? 'اسم العيادة' : 'Clinic name', $req->clinic_name) !!}
                {!! $row($l ? 'المدينة' : 'City', $req->clinic_city) !!}
                {!! $row($l ? 'العنوان' : 'Address', $req->clinic_address) !!}
                {!! $row($l ? 'الهاتف' : 'Phone', $req->clinic_phone) !!}
            </div>

            {{-- Assistants --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <h3 class="font-black text-slate-900 mb-3">{{ $l ? 'حسابات المساعدين' : 'Assistant accounts' }} ({{ $req->assistantsCount() }})</h3>
                @if ($req->assistantsCount() > 0)
                    <div class="space-y-4">
                        @foreach ($req->assistants as $i => $a)
                            <div class="rounded-xl bg-slate-50 p-4">
                                <p class="text-xs font-bold text-slate-400 mb-2">{{ $l ? 'مساعد' : 'Assistant' }} {{ $i + 1 }}</p>
                                {!! $row($l ? 'الاسم' : 'Name', $a['name'] ?? '') !!}
                                {!! $row($l ? 'اسم المستخدم' : 'Username', $a['username'] ?? '') !!}
                                {!! $row($l ? 'الجوال' : 'Phone', $a['phone'] ?? '') !!}
                                <div class="flex justify-between gap-4 py-2">
                                    <span class="text-slate-500">{{ $l ? 'كلمة المرور' : 'Password' }}</span>
                                    <span class="font-mono font-semibold text-slate-800" dir="ltr">{{ ($a['password'] ?? '') !== '' ? $a['password'] : '—' }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-slate-400 text-sm">{{ $l ? 'لم يُطلب أي مساعد.' : 'No assistants requested.' }}</p>
                @endif
            </div>

            @if ($req->notes)
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                    <h3 class="font-black text-slate-900 mb-2">{{ $l ? 'ملاحظات' : 'Notes' }}</h3>
                    <p class="text-slate-700 whitespace-pre-line">{{ $req->notes }}</p>
                </div>
            @endif
        </div>

        {{-- Sidebar: contact + status + actions --}}
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <h3 class="font-black text-slate-900 mb-3">{{ $l ? 'جهة التواصل' : 'Contact' }}</h3>
                {!! $row($l ? 'الاسم' : 'Name', $req->contact_name) !!}
                {!! $row($l ? 'الهاتف' : 'Phone', $req->contact_phone) !!}
                {!! $row($l ? 'البريد' : 'Email', $req->contact_email) !!}
                <a href="tel:{{ $req->contact_phone }}" class="mt-4 block text-center py-2.5 bg-emerald-500 text-white rounded-xl font-bold hover:bg-emerald-600 transition-all">
                    {{ $l ? 'اتصال' : 'Call' }}
                </a>
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <h3 class="font-black text-slate-900 mb-3">{{ $l ? 'حالة الطلب' : 'Status' }}</h3>
                <form method="POST" action="{{ route('admin.subscription-requests.status', $req) }}" class="flex gap-2">
                    @csrf
                    @method('PATCH')
                    <select name="status" class="flex-1 px-3 py-2 rounded-lg border border-slate-200 outline-none focus:border-primary">
                        <option value="new" @selected($req->status === 'new')>{{ $l ? 'جديدة' : 'New' }}</option>
                        <option value="contacted" @selected($req->status === 'contacted')>{{ $l ? 'تم التواصل' : 'Contacted' }}</option>
                        <option value="activated" @selected($req->status === 'activated')>{{ $l ? 'تم التفعيل' : 'Activated' }}</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg font-semibold hover:opacity-90">{{ $l ? 'حفظ' : 'Save' }}</button>
                </form>
                @if ($req->reviewed_at)
                    <p class="text-xs text-slate-400 mt-2">{{ $l ? 'آخر تحديث:' : 'Updated:' }} {{ $req->reviewed_at->format('Y-m-d H:i') }}</p>
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-3">
                <a href="{{ route('admin.clinic-onboarding.create') }}" class="block text-center py-2.5 bg-slate-900 text-white rounded-xl font-bold hover:bg-slate-800 transition-all">
                    {{ $l ? 'إنشاء الحسابات (إعداد عيادة)' : 'Create accounts (Quick Setup)' }}
                </a>
                <form method="POST" action="{{ route('admin.subscription-requests.destroy', $req) }}" onsubmit="return confirm('{{ $l ? 'حذف هذا الطلب نهائيًا؟' : 'Permanently delete this request?' }}');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-2.5 bg-red-50 text-red-600 rounded-xl font-bold hover:bg-red-100 transition-all">
                        {{ $l ? 'حذف الطلب' : 'Delete request' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
