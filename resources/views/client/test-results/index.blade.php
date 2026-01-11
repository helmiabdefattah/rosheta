@extends('client.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'نتائج التحاليل والأشعة' : 'Test & Radiology Results')
@section('page-title', app()->getLocale() === 'ar' ? 'نتائج التحاليل والأشعة' : 'Test & Radiology Results')
@section('page-description', app()->getLocale() === 'ar' ? 'عرض نتائج التحاليل والأشعة المرفوعة من قبل المعامل' : 'View test results and reports uploaded by laboratories')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="text-lg font-bold text-slate-800">
                {{ app()->getLocale() === 'ar' ? 'النتائج والتقارير' : 'Results & Reports' }}
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 font-bold">{{ app()->getLocale() === 'ar' ? 'رقم الطلب' : 'Request #' }}</th>
                        <th class="px-6 py-4 font-bold">{{ app()->getLocale() === 'ar' ? 'المعمل' : 'Laboratory' }}</th>
                        <th class="px-6 py-4 font-bold">{{ app()->getLocale() === 'ar' ? 'نوع الطلب' : 'Type' }}</th>
                        <th class="px-6 py-4 font-bold">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                        <th class="px-6 py-4 font-bold">{{ app()->getLocale() === 'ar' ? 'التاريخ' : 'Date' }}</th>
                        <th class="px-6 py-4 font-bold">{{ app()->getLocale() === 'ar' ? 'النتائج' : 'Results' }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($results as $offer)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 font-semibold text-slate-900">#{{ $offer->request->id }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-lg bg-teal-100 flex items-center justify-center text-teal-600">
                                        <i class="bi bi-hospital"></i>
                                    </div>
                                    <span class="font-medium text-gray-700">{{ $offer->laboratory->name ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-md text-xs font-semibold {{ $offer->request_type === 'test' ? 'bg-blue-50 text-blue-600' : 'bg-purple-50 text-purple-600' }}">
                                    {{ $offer->request_type === 'test' ? (app()->getLocale() === 'ar' ? 'تحاليل' : 'Test') : (app()->getLocale() === 'ar' ? 'أشعة' : 'Radiology') }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusClass = match($offer->vendor_status) {
                                        'test_completed' => 'bg-green-100 text-green-700',
                                        'sample_collected' => 'bg-blue-100 text-blue-700',
                                        default => 'bg-amber-100 text-amber-700'
                                    };
                                    $statusLabel = match($offer->vendor_status) {
                                        'test_completed' => (app()->getLocale() === 'ar' ? 'تم الانتهاء' : 'Completed'),
                                        'sample_collected' => (app()->getLocale() === 'ar' ? 'تم سحب العينة' : 'Sample Collected'),
                                        default => (app()->getLocale() === 'ar' ? 'جاري التحضير' : 'In Preparation')
                                    };
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ $offer->updated_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4">
                                @if($offer->attachments->count() > 0)
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($offer->attachments as $attachment)
                                            <a href="{{ $attachment->url }}" target="_blank" 
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-primary/10 text-primary hover:bg-primary hover:text-white rounded-lg text-xs font-bold transition-all shadow-sm"
                                               title="{{ $attachment->description ?? $attachment->file_name }}">
                                                @if($attachment->isPdf())
                                                    <i class="bi bi-file-earmark-pdf"></i>
                                                @elseif($attachment->isImage())
                                                    <i class="bi bi-image"></i>
                                                @else
                                                    <i class="bi bi-paperclip"></i>
                                                @endif
                                                {{ app()->getLocale() === 'ar' ? 'عرض النتيجة' : 'View Result' }}
                                            </a>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 italic">
                                        {{ app()->getLocale() === 'ar' ? 'بانتظار رفع النتائج' : 'Waiting for results' }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4 text-gray-300">
                                        <i class="bi bi-file-earmark-medical text-3xl"></i>
                                    </div>
                                    <p class="text-lg font-medium">{{ app()->getLocale() === 'ar' ? 'لا توجد نتائج اختبار متاحة بعد' : 'No test results available yet' }}</p>
                                    <p class="text-sm">{{ app()->getLocale() === 'ar' ? 'ستظهر نتائجك هنا بمجرد انتهائها' : 'Your results will appear here once they are completed by the lab' }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($results->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
                {{ $results->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
