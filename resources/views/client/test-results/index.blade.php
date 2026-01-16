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

        <!-- Desktop Table View -->
        <div class="hidden md:block overflow-x-auto">
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
                        @include('client.test-results.partials.result-row', ['offer' => $offer])
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

        <!-- Mobile Card View -->
        <div class="md:hidden divide-y divide-gray-100">
            @forelse($results as $offer)
                @include('client.test-results.partials.result-card', ['offer' => $offer])
            @empty
                <div class="p-6 text-center">
                    <div class="flex flex-col items-center justify-center text-gray-400">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4 text-gray-300">
                            <i class="bi bi-file-earmark-medical text-3xl"></i>
                        </div>
                        <p class="text-lg font-medium">{{ app()->getLocale() === 'ar' ? 'لا توجد نتائج اختبار متاحة بعد' : 'No test results available yet' }}</p>
                        <p class="text-sm">{{ app()->getLocale() === 'ar' ? 'ستظهر نتائجك هنا بمجرد انتهائها' : 'Your results will appear here once they are completed by the lab' }}</p>
                    </div>
                </div>
            @endforelse
        </div>

        @if($results->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
                {{ $results->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
