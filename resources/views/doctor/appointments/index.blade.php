@extends('doctor.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'المواعيد' : 'Appointments')
@section('page-title', app()->getLocale() === 'ar' ? 'المواعيد' : 'Appointments')

@section('content')
<div class="mb-4 flex flex-wrap gap-2">
    <form method="GET" class="flex flex-wrap items-center gap-2">
        <select name="status" class="px-3 py-2 border border-slate-300 rounded-lg text-sm" onchange="this.form.submit()">
            <option value="">{{ app()->getLocale() === 'ar' ? 'كل الحالات' : 'All statuses' }}</option>
            @foreach(\App\Models\Appointment::STATUSES as $k => $v)
                <option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? (['pending'=>'قيد الانتظار','confirmed'=>'مؤكد','completed'=>'منتهي','missed'=>'فائت','cancelled'=>'ملغي'][$k] ?? $v) : ucfirst($v) }}</option>
            @endforeach
        </select>
        <input type="date" name="from_date" value="{{ request('from_date') }}" class="px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="From">
        <input type="date" name="to_date" value="{{ request('to_date') }}" class="px-3 py-2 border border-slate-300 rounded-lg text-sm" placeholder="To">
        <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg text-sm hover:bg-teal-700">{{ app()->getLocale() === 'ar' ? 'بحث' : 'Filter' }}</button>
    </form>
</div>
<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-start text-xs font-medium text-slate-500 uppercase">{{ app()->getLocale() === 'ar' ? 'التاريخ' : 'Date' }}</th>
                    <th class="px-4 py-3 text-start text-xs font-medium text-slate-500 uppercase">{{ app()->getLocale() === 'ar' ? 'الوقت' : 'Time' }}</th>
                    <th class="px-4 py-3 text-start text-xs font-medium text-slate-500 uppercase">{{ app()->getLocale() === 'ar' ? 'العيادة' : 'Clinic' }}</th>
                    <th class="px-4 py-3 text-start text-xs font-medium text-slate-500 uppercase">{{ app()->getLocale() === 'ar' ? 'العميل' : 'Client' }}</th>
                    <th class="px-4 py-3 text-start text-xs font-medium text-slate-500 uppercase">{{ app()->getLocale() === 'ar' ? 'النوع' : 'Type' }}</th>
                    <th class="px-4 py-3 text-start text-xs font-medium text-slate-500 uppercase">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                    <th class="px-4 py-3 text-start text-xs font-medium text-slate-500 uppercase">{{ app()->getLocale() === 'ar' ? 'إجراءات' : 'Actions' }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($appointments as $apt)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-slate-800">{{ $apt->appointment_date->format('Y-m-d') }}</td>
                        <td class="px-4 py-3 text-sm text-slate-800">{{ \Carbon\Carbon::parse($apt->appointment_time)->format('g:i A') }}</td>
                        <td class="px-4 py-3 text-sm text-slate-800">{{ $apt->clinic?->name }}</td>
                        <td class="px-4 py-3 text-sm text-slate-800">{{ $apt->client?->name ?? '–' }}</td>
                        <td class="px-4 py-3 text-sm text-slate-600">{{ $apt->type }}</td>
                        <td class="px-4 py-3"><span class="text-xs px-2 py-1 rounded-full {{ $apt->status === 'confirmed' ? 'bg-green-100 text-green-700' : ($apt->status === 'cancelled' ? 'bg-red-100 text-red-700' : ($apt->status === 'completed' ? 'bg-slate-100 text-slate-700' : ($apt->status === 'missed' ? 'bg-orange-100 text-orange-700' : 'bg-amber-100 text-amber-700'))) }}">{{ app()->getLocale() === 'ar' ? (['pending'=>'قيد الانتظار','confirmed'=>'مؤكد','completed'=>'منتهي','missed'=>'فائت','cancelled'=>'ملغي'][$apt->status] ?? $apt->status) : ucfirst($apt->status) }}</span></td>
                        <td class="px-4 py-3">
                            @if(!in_array($apt->status, ['completed', 'missed', 'cancelled']))
                                <div class="flex flex-wrap gap-1">
                                    @if($apt->status === 'pending')
                                        <form action="{{ route('doctor.appointments.update-status', $apt) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="status" value="confirmed">
                                            <button type="submit" class="text-xs px-2 py-1 rounded bg-green-100 text-green-700 hover:bg-green-200">{{ app()->getLocale() === 'ar' ? 'تأكيد' : 'Confirm' }}</button>
                                        </form>
                                    @endif
                                    <form action="{{ route('doctor.appointments.update-status', $apt) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" class="text-xs px-2 py-1 rounded bg-slate-100 text-slate-700 hover:bg-slate-200">{{ app()->getLocale() === 'ar' ? 'منتهي' : 'Completed' }}</button>
                                    </form>
                                    <form action="{{ route('doctor.appointments.update-status', $apt) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="missed">
                                        <button type="submit" class="text-xs px-2 py-1 rounded bg-orange-100 text-orange-700 hover:bg-orange-200">{{ app()->getLocale() === 'ar' ? 'فائت' : 'Missed' }}</button>
                                    </form>
                                    <form action="{{ route('doctor.appointments.update-status', $apt) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit" class="text-xs px-2 py-1 rounded bg-red-100 text-red-700 hover:bg-red-200">{{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}</button>
                                    </form>
                                </div>
                            @else
                                <span class="text-xs text-slate-400">–</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">{{ app()->getLocale() === 'ar' ? 'لا توجد مواعيد' : 'No appointments.' }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($appointments->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $appointments->links() }}</div>
    @endif
</div>
@endsection
