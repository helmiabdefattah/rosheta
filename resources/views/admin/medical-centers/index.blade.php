@extends('admin.layouts.admin')

@php $l = app()->getLocale() === 'ar'; @endphp

@section('title', 'Medical Centers')
@section('page-title', $l ? 'المراكز الطبية' : 'Medical Centers')
@section('page-description', $l ? 'مراكز تضم عدة عيادات' : 'Centers grouping multiple clinics')

@section('header-actions')
    <a href="{{ route('admin.medical-centers.create') }}" class="px-4 py-2 bg-primary text-white rounded-lg hover:opacity-90 transition-all inline-flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        {{ $l ? 'إضافة مركز' : 'Add Center' }}
    </a>
@endsection

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-start">{{ $l ? 'الاسم' : 'Name' }}</th>
                    <th class="px-4 py-3 text-start">{{ $l ? 'الموقع' : 'Location' }}</th>
                    <th class="px-4 py-3 text-center">{{ $l ? 'عيادات' : 'Clinics' }}</th>
                    <th class="px-4 py-3 text-start">{{ $l ? 'الحالة' : 'Status' }}</th>
                    <th class="px-4 py-3 text-end">{{ $l ? 'إجراءات' : 'Actions' }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($centers as $center)
                    <tr class="hover:bg-slate-50/60">
                        <td class="px-4 py-3 font-semibold text-slate-800">{{ $center->name }}</td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $center->city?->name ?? '—' }}@if($center->governorate), {{ $center->governorate->name }}@endif
                        </td>
                        <td class="px-4 py-3 text-center">{{ $center->clinics_count }}</td>
                        <td class="px-4 py-3">
                            @if($center->is_active)
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">{{ $l ? 'نشط' : 'Active' }}</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500">{{ $l ? 'غير نشط' : 'Inactive' }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-end whitespace-nowrap">
                            <a href="{{ route('admin.medical-centers.edit', $center) }}" class="text-primary font-semibold hover:underline">{{ $l ? 'تعديل' : 'Edit' }}</a>
                            <form method="POST" action="{{ route('admin.medical-centers.destroy', $center) }}" class="inline" onsubmit="return confirm('{{ $l ? 'حذف هذا المركز؟ ستصبح عياداته مستقلة.' : 'Delete this center? Its clinics become standalone.' }}');">
                                @csrf @method('DELETE')
                                <button type="submit" class="ms-3 text-red-600 font-semibold hover:underline">{{ $l ? 'حذف' : 'Delete' }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-12 text-center text-slate-400">{{ $l ? 'لا توجد مراكز بعد.' : 'No centers yet.' }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($centers->hasPages())
        <div class="p-4 border-t border-slate-100">{{ $centers->links() }}</div>
    @endif
</div>
@endsection
