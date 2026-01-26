@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'ملاحظات العملاء' : 'Client Feedback')
@section('page-title', app()->getLocale() === 'ar' ? 'ملاحظات العملاء' : 'Client Feedback')
@section('page-description', app()->getLocale() === 'ar' ? 'عرض وإدارة ملاحظات العملاء' : 'View and manage client feedback')

@section('content')
@if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <p class="text-sm text-green-800">{{ session('success') }}</p>
    </div>
@endif

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <!-- Filters -->
    <div class="p-6 border-b border-slate-200">
        <form method="GET" action="{{ route('admin.feedback.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    placeholder="{{ app()->getLocale() === 'ar' ? 'بحث...' : 'Search...' }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                >
            </div>
            <div>
                <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    <option value="">{{ app()->getLocale() === 'ar' ? 'جميع الحالات' : 'All Statuses' }}</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'قيد المراجعة' : 'Pending' }}
                    </option>
                    <option value="reviewed" {{ request('status') === 'reviewed' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'تمت المراجعة' : 'Reviewed' }}
                    </option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'تم الحل' : 'Resolved' }}
                    </option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'مغلق' : 'Closed' }}
                    </option>
                </select>
            </div>
            <div>
                <select name="type" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    <option value="">{{ app()->getLocale() === 'ar' ? 'جميع الأنواع' : 'All Types' }}</option>
                    <option value="bug" {{ request('type') === 'bug' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'بلاغ عن خطأ' : 'Bug Report' }}
                    </option>
                    <option value="suggestion" {{ request('type') === 'suggestion' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'اقتراح' : 'Suggestion' }}
                    </option>
                    <option value="complaint" {{ request('type') === 'complaint' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'شكوى' : 'Complaint' }}
                    </option>
                    <option value="compliment" {{ request('type') === 'compliment' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'إشادة' : 'Compliment' }}
                    </option>
                    <option value="other" {{ request('type') === 'other' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'أخرى' : 'Other' }}
                    </option>
                </select>
            </div>
            <div>
                <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200">
                    {{ app()->getLocale() === 'ar' ? 'تصفية' : 'Filter' }}
                </button>
            </div>
            @if(request()->has('search') || request()->has('status') || request()->has('type'))
                <div>
                    <a href="{{ route('admin.feedback.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition duration-200">
                        {{ app()->getLocale() === 'ar' ? 'إعادة تعيين' : 'Reset' }}
                    </a>
                </div>
            @endif
        </form>
    </div>

    <!-- Feedback List -->
    <div class="p-6">
        @if($feedbacks->count() > 0)
            <div class="space-y-4">
                @foreach($feedbacks as $feedback)
                    <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
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
                                <p class="text-sm text-gray-700 mb-3 line-clamp-2">
                                    {{ \Illuminate\Support\Str::limit($feedback->message, 150) }}
                                </p>
                                <div class="flex items-center gap-4 text-xs text-gray-500">
                                    <span>
                                        <strong>{{ app()->getLocale() === 'ar' ? 'العميل:' : 'Client:' }}</strong> 
                                        {{ $feedback->client->name }} ({{ $feedback->client->email ?? $feedback->client->phone_number }})
                                    </span>
                                    <span>
                                        <strong>{{ app()->getLocale() === 'ar' ? 'التاريخ:' : 'Date:' }}</strong> 
                                        {{ $feedback->created_at->format('Y-m-d H:i') }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('admin.feedback.show', $feedback) }}" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-teal-600 transition duration-200 text-sm">
                                    {{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $feedbacks->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="bi bi-chat-left-text text-gray-400 text-6xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'لا توجد ملاحظات' : 'No Feedback' }}
                </h3>
                <p class="text-gray-500">
                    {{ app()->getLocale() === 'ar' 
                        ? 'لا توجد ملاحظات لعرضها.' 
                        : 'No feedback to display.' }}
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
