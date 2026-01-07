@extends('client.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'ملاحظاتي' : 'My Feedback')

@section('page-title', app()->getLocale() === 'ar' ? 'ملاحظاتي' : 'My Feedback')
@section('page-description', app()->getLocale() === 'ar' ? 'عرض جميع الملاحظات التي أرسلتها' : 'View all feedback you have submitted')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">
                {{ app()->getLocale() === 'ar' ? 'ملاحظاتي' : 'My Feedback' }}
            </h2>
            <p class="text-sm text-gray-600 mt-1">
                {{ app()->getLocale() === 'ar' 
                    ? 'جميع الملاحظات التي أرسلتها' 
                    : 'All feedback you have submitted' }}
            </p>
        </div>
        <a href="{{ route('client.feedback.create') }}" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-teal-700 transition duration-200 text-sm font-medium">
            <i class="bi bi-plus-circle me-1"></i>
            {{ app()->getLocale() === 'ar' ? 'إرسال ملاحظة جديدة' : 'Submit New Feedback' }}
        </a>
    </div>

    @if($feedbacks->count() > 0)
        <div class="space-y-4">
            @foreach($feedbacks as $feedback)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="px-3 py-1 text-xs font-medium rounded-full 
                                    @if($feedback->type === 'bug') bg-red-100 text-red-800
                                    @elseif($feedback->type === 'suggestion') bg-blue-100 text-blue-800
                                    @elseif($feedback->type === 'complaint') bg-orange-100 text-orange-800
                                    @elseif($feedback->type === 'compliment') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    @if($feedback->type === 'bug')
                                        {{ app()->getLocale() === 'ar' ? 'بلاغ عن خطأ' : 'Bug Report' }}
                                    @elseif($feedback->type === 'suggestion')
                                        {{ app()->getLocale() === 'ar' ? 'اقتراح' : 'Suggestion' }}
                                    @elseif($feedback->type === 'complaint')
                                        {{ app()->getLocale() === 'ar' ? 'شكوى' : 'Complaint' }}
                                    @elseif($feedback->type === 'compliment')
                                        {{ app()->getLocale() === 'ar' ? 'إشادة' : 'Compliment' }}
                                    @else
                                        {{ app()->getLocale() === 'ar' ? 'أخرى' : 'Other' }}
                                    @endif
                                </span>
                                <span class="px-3 py-1 text-xs font-medium rounded-full 
                                    @if($feedback->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($feedback->status === 'reviewed') bg-blue-100 text-blue-800
                                    @elseif($feedback->status === 'resolved') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    @if($feedback->status === 'pending')
                                        {{ app()->getLocale() === 'ar' ? 'قيد المراجعة' : 'Pending' }}
                                    @elseif($feedback->status === 'reviewed')
                                        {{ app()->getLocale() === 'ar' ? 'تمت المراجعة' : 'Reviewed' }}
                                    @elseif($feedback->status === 'resolved')
                                        {{ app()->getLocale() === 'ar' ? 'تم الحل' : 'Resolved' }}
                                    @else
                                        {{ app()->getLocale() === 'ar' ? 'مغلق' : 'Closed' }}
                                    @endif
                                </span>
                            </div>
                            @if($feedback->subject)
                                <h3 class="text-lg font-semibold text-slate-900 mb-2">
                                    {{ $feedback->subject }}
                                </h3>
                            @endif
                            <p class="text-sm text-gray-700 mb-3">
                                {{ $feedback->message }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ app()->getLocale() === 'ar' ? 'تم الإرسال في' : 'Submitted on' }}: 
                                {{ $feedback->created_at->format('Y-m-d H:i') }}
                            </p>
                        </div>
                    </div>

                    @if($feedback->admin_response)
                        <div class="mt-4 pt-4 border-t border-gray-200 bg-blue-50 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-blue-900 mb-2">
                                {{ app()->getLocale() === 'ar' ? 'رد الإدارة' : 'Admin Response' }}
                            </h4>
                            <p class="text-sm text-blue-800">
                                {{ $feedback->admin_response }}
                            </p>
                            @if($feedback->reviewed_at)
                                <p class="text-xs text-blue-600 mt-2">
                                    {{ app()->getLocale() === 'ar' ? 'تمت المراجعة في' : 'Reviewed on' }}: 
                                    {{ $feedback->reviewed_at->format('Y-m-d H:i') }}
                                </p>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $feedbacks->links() }}
        </div>
    @else
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
            <i class="bi bi-chat-left-text text-gray-400 text-6xl mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">
                {{ app()->getLocale() === 'ar' ? 'لا توجد ملاحظات' : 'No Feedback' }}
            </h3>
            <p class="text-gray-500 mb-6">
                {{ app()->getLocale() === 'ar' 
                    ? 'لم تقم بإرسال أي ملاحظات بعد.' 
                    : 'You haven\'t submitted any feedback yet.' }}
            </p>
            <a href="{{ route('client.feedback.create') }}" class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-lg hover:bg-teal-700 transition duration-200 font-medium">
                <i class="bi bi-plus-circle me-2"></i>
                {{ app()->getLocale() === 'ar' ? 'إرسال ملاحظة جديدة' : 'Submit New Feedback' }}
            </a>
        </div>
    @endif
</div>
@endsection
