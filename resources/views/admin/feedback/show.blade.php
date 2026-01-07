@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'تفاصيل الملاحظة' : 'Feedback Details')
@section('page-title', app()->getLocale() === 'ar' ? 'تفاصيل الملاحظة' : 'Feedback Details')

@section('content')
<div class="space-y-6">
    <!-- Back Button -->
    <div>
        <a href="{{ route('admin.feedback.index') }}" class="inline-flex items-center text-primary hover:underline">
            <i class="bi bi-arrow-left me-2"></i>
            {{ app()->getLocale() === 'ar' ? 'العودة إلى القائمة' : 'Back to List' }}
        </a>
    </div>

    <!-- Feedback Details -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-start justify-between mb-6">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <span class="px-3 py-1 text-sm font-medium rounded-full 
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
                    <span class="px-3 py-1 text-sm font-medium rounded-full 
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
                    <h2 class="text-2xl font-bold text-slate-900 mb-2">
                        {{ $feedback->subject }}
                    </h2>
                @endif
            </div>
        </div>

        <div class="mb-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">
                {{ app()->getLocale() === 'ar' ? 'الرسالة' : 'Message' }}
            </h3>
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <p class="text-gray-700 whitespace-pre-wrap">{{ $feedback->message }}</p>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4 mb-6">
            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'العميل' : 'Client' }}
                </h3>
                <p class="text-gray-600">
                    {{ $feedback->client->name }}<br>
                    <span class="text-sm text-gray-500">
                        {{ $feedback->client->email ?? $feedback->client->phone_number }}
                    </span>
                </p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'التاريخ' : 'Date' }}
                </h3>
                <p class="text-gray-600">
                    {{ $feedback->created_at->format('Y-m-d H:i') }}
                </p>
            </div>
        </div>

        @if($feedback->admin_response)
            <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <h3 class="text-sm font-semibold text-blue-900 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'رد الإدارة' : 'Admin Response' }}
                </h3>
                <p class="text-blue-800 whitespace-pre-wrap">{{ $feedback->admin_response }}</p>
                @if($feedback->reviewed_at)
                    <p class="text-xs text-blue-600 mt-2">
                        {{ app()->getLocale() === 'ar' ? 'تمت المراجعة في' : 'Reviewed on' }}: 
                        {{ $feedback->reviewed_at->format('Y-m-d H:i') }}
                    </p>
                @endif
            </div>
        @endif

        <!-- Update Form -->
        <form method="POST" action="{{ route('admin.feedback.update', $feedback) }}" class="border-t border-gray-200 pt-6">
            @csrf
            @method('PUT')
            
            <div class="space-y-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}
                    </label>
                    <select 
                        id="status" 
                        name="status" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                    >
                        <option value="pending" {{ $feedback->status === 'pending' ? 'selected' : '' }}>
                            {{ app()->getLocale() === 'ar' ? 'قيد المراجعة' : 'Pending' }}
                        </option>
                        <option value="reviewed" {{ $feedback->status === 'reviewed' ? 'selected' : '' }}>
                            {{ app()->getLocale() === 'ar' ? 'تمت المراجعة' : 'Reviewed' }}
                        </option>
                        <option value="resolved" {{ $feedback->status === 'resolved' ? 'selected' : '' }}>
                            {{ app()->getLocale() === 'ar' ? 'تم الحل' : 'Resolved' }}
                        </option>
                        <option value="closed" {{ $feedback->status === 'closed' ? 'selected' : '' }}>
                            {{ app()->getLocale() === 'ar' ? 'مغلق' : 'Closed' }}
                        </option>
                    </select>
                </div>

                <div>
                    <label for="admin_response" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ app()->getLocale() === 'ar' ? 'رد الإدارة' : 'Admin Response' }}
                        <span class="text-gray-500 text-xs">({{ app()->getLocale() === 'ar' ? 'اختياري' : 'Optional' }})</span>
                    </label>
                    <textarea 
                        id="admin_response" 
                        name="admin_response" 
                        rows="5"
                        maxlength="5000"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                        placeholder="{{ app()->getLocale() === 'ar' ? 'أدخل ردك هنا...' : 'Enter your response here...' }}"
                    >{{ old('admin_response', $feedback->admin_response) }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">
                        {{ app()->getLocale() === 'ar' 
                            ? 'الحد الأقصى: 5000 حرف' 
                            : 'Maximum: 5000 characters' }}
                    </p>
                </div>

                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('admin.feedback.index') }}" class="px-6 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200">
                        {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                    </a>
                    <button 
                        type="submit" 
                        class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-teal-600 transition duration-200 font-medium"
                    >
                        {{ app()->getLocale() === 'ar' ? 'حفظ التغييرات' : 'Save Changes' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
