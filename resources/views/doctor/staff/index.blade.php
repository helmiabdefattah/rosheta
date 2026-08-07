@extends('doctor.layouts.dashboard')

@php $ar = app()->getLocale() === 'ar'; @endphp

@section('title', $ar ? 'المساعدون' : 'Assistants')
@section('page-title', $ar ? 'المساعدون' : 'Assistants')
@section('page-description', $ar ? 'حسابات المساعدين في عياداتك' : 'Assistant accounts for your clinics')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-full bg-teal-100 flex items-center justify-center text-teal-600 shrink-0">
                <i class="bi bi-people text-xl"></i>
            </div>
            <div>
                <p class="font-semibold text-slate-800">
                    {{ $ar ? "يمكنك إضافة حتى {$limit} مساعدين لكل عيادة" : "You can add up to {$limit} assistants per clinic" }}
                </p>
                <p class="text-sm text-slate-500 mt-1">
                    {{ $ar
                        ? 'يسجّل المساعد الدخول بنفس صفحة الدخول، ويفتح شاشة المساعد الخاصة بعيادته. لرفع هذا العدد تواصل مع الإدارة.'
                        : 'An assistant signs in on the same login page and lands in the assistant workspace of their clinic. Contact the administration to raise this number.' }}
                </p>
            </div>
        </div>
    </div>

    @forelse($clinics as $clinic)
        @php
            $clinicAssistants = $assistantsByClinic[$clinic->id] ?? collect();
            $remaining = max(0, $limit - $clinicAssistants->count());
        @endphp
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between gap-3 flex-wrap">
                <div>
                    <h3 class="font-bold text-slate-800">{{ $clinic->name }}</h3>
                    <p class="text-sm text-slate-500">{{ $clinic->address ?: ($ar ? 'بدون عنوان' : 'No address') }}</p>
                </div>
                <span class="text-xs font-semibold px-3 py-1 rounded-full {{ $remaining > 0 ? 'bg-teal-50 text-teal-700' : 'bg-amber-50 text-amber-700' }}">
                    {{ $clinicAssistants->count() }} / {{ $limit }}
                    {{ $ar ? 'مساعد' : 'assistants' }}
                </span>
            </div>

            <div class="divide-y divide-gray-100">
                @forelse($clinicAssistants as $assistant)
                    @include('doctor.staff.partials.assistant-row', ['assistant' => $assistant, 'clinics' => $clinics])
                @empty
                    <p class="px-5 py-6 text-sm text-slate-500">{{ $ar ? 'لا يوجد مساعدون في هذه العيادة بعد.' : 'No assistants at this clinic yet.' }}</p>
                @endforelse
            </div>

            <div class="px-5 py-4 bg-gray-50 border-t border-gray-100">
                @if($remaining > 0)
                    <button type="button" class="inline-flex items-center gap-2 px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 text-sm font-medium"
                            onclick="document.getElementById('add-assistant-{{ $clinic->id }}').classList.toggle('hidden')">
                        <i class="bi bi-plus-lg"></i>
                        {{ $ar ? 'إضافة مساعد' : 'Add assistant' }}
                        <span class="text-xs opacity-80">({{ $ar ? "متبقٍ {$remaining}" : "$remaining left" }})</span>
                    </button>

                    <form id="add-assistant-{{ $clinic->id }}" method="POST" action="{{ route('doctor.staff.store') }}"
                          class="hidden mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @csrf
                        <input type="hidden" name="clinic_id" value="{{ $clinic->id }}">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ $ar ? 'الاسم' : 'Name' }}</label>
                            <input type="text" name="name" value="{{ old('clinic_id') == $clinic->id ? old('name') : '' }}" required
                                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ $ar ? 'رقم الهاتف' : 'Phone' }} <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="phone_number" value="{{ old('clinic_id') == $clinic->id ? old('phone_number') : '' }}" required placeholder="01xxxxxxxxx"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500">
                            <p class="mt-1 text-xs text-slate-500">{{ $ar ? 'يسجّل المساعد الدخول برقم الهاتف وكلمة المرور.' : 'The assistant signs in with this phone number and their password.' }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                {{ $ar ? 'البريد الإلكتروني (اختياري)' : 'Email (optional)' }}
                            </label>
                            <input type="email" name="email" value="{{ old('clinic_id') == $clinic->id ? old('email') : '' }}" autocomplete="off"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ $ar ? 'كلمة المرور' : 'Password' }}</label>
                            <input type="password" name="password" required autocomplete="new-password"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ $ar ? 'تأكيد كلمة المرور' : 'Confirm password' }}</label>
                            <input type="password" name="password_confirmation" required autocomplete="new-password"
                                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500">
                        </div>
                        <div class="sm:col-span-2 flex justify-end gap-3">
                            <button type="button" class="px-4 py-2 text-sm text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50"
                                    onclick="document.getElementById('add-assistant-{{ $clinic->id }}').classList.add('hidden')">
                                {{ $ar ? 'إلغاء' : 'Cancel' }}
                            </button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-teal-600 rounded-lg hover:bg-teal-700">
                                {{ $ar ? 'حفظ المساعد' : 'Save assistant' }}
                            </button>
                        </div>
                    </form>
                @else
                    <p class="text-sm text-amber-700">
                        {{ $ar
                            ? "لقد وصلت إلى الحد الأقصى ({$limit}) من المساعدين لهذه العيادة. تواصل مع الإدارة لرفع الحد."
                            : "You have reached the limit of {$limit} assistants for this clinic. Contact the administration to raise it." }}
                    </p>
                @endif
            </div>
        </div>
    @empty
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center text-slate-500">
            {{ $ar ? 'أضف عيادة أولاً حتى تتمكن من إضافة مساعدين.' : 'Add a clinic first, then you can add assistants to it.' }}
            <a href="{{ route('doctor.clinics.create') }}" class="block mt-3 text-teal-600 font-medium hover:underline">{{ $ar ? 'إضافة عيادة' : 'Add clinic' }}</a>
        </div>
    @endforelse

    @if($unassigned->isNotEmpty() && $clinics->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-amber-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-amber-100 bg-amber-50">
                <h3 class="font-bold text-amber-800">{{ $ar ? 'مساعدون بدون عيادة محددة' : 'Assistants without a clinic' }}</h3>
                <p class="text-sm text-amber-700">{{ $ar ? 'اختر العيادة التي يعمل بها كل مساعد.' : 'Pick the clinic each of them works at.' }}</p>
            </div>
            <div class="divide-y divide-gray-100">
                @foreach($unassigned as $assistant)
                    @include('doctor.staff.partials.assistant-row', ['assistant' => $assistant, 'clinics' => $clinics])
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
