@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'نقاط المكافآت' : 'Bonus Points')
@section('page-title', app()->getLocale() === 'ar' ? 'نقاط المكافآت' : 'Bonus Points')
@section('page-description', app()->getLocale() === 'ar' ? 'قائمة نقاط المكافآت للعملاء' : 'List of client bonus points')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="p-6 border-b">
        <h3 class="text-lg font-semibold text-slate-900">
            {{ app()->getLocale() === 'ar' ? 'جميع نقاط المكافآت' : 'All Bonus Points' }}
        </h3>
    </div>

    <div class="p-6 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'العميل' : 'Client' }}</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'المصدر' : 'Source' }}</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'المعرف' : 'Source ID' }}</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'النقاط' : 'Points' }}</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'تاريخ الإنشاء' : 'Created At' }}</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            @forelse($points as $bp)
                <tr>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $bp->id }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                        {{ $bp->client->name ?? 'N/A' }}
                        <div class="text-xs text-slate-500">{{ $bp->client->email ?? '' }}</div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">
                        <span class="px-2 py-1 text-xs rounded-full 
                            {{ $bp->source_type === 'order' ? 'bg-indigo-100 text-indigo-800' : ($bp->source_type === 'nurse_visit' ? 'bg-teal-100 text-teal-800' : 'bg-amber-100 text-amber-800') }}">
                            {{ str_replace('_', ' ', ucfirst($bp->source_type)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-700">#{{ $bp->source_id }}</td>
                    <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ number_format($bp->points) }}</td>
                    <td class="px-4 py-3 text-sm text-gray-700">{{ $bp->created_at->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">
                        {{ app()->getLocale() === 'ar' ? 'لا توجد سجلات' : 'No records found.' }}
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <div class="px-2 py-4">
            {{ $points->links() }}
        </div>
    </div>
</div>
@endsection



