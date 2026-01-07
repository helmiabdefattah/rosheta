@extends('client.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'إرسال ملاحظة' : 'Submit Feedback')

@section('page-title', app()->getLocale() === 'ar' ? 'إرسال ملاحظة' : 'Submit Feedback')
@section('page-description', app()->getLocale() === 'ar' ? 'شاركنا آراءك واقتراحاتك لتحسين الخدمة' : 'Share your thoughts and suggestions to improve our service')

@section('content')
<div class="max-w-4xl mx-auto">
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <p class="text-sm text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 lg:p-8">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-slate-900">
                {{ app()->getLocale() === 'ar' ? 'إرسال ملاحظة' : 'Submit Feedback' }}
            </h2>
            <p class="text-sm text-gray-600 mt-2">
                {{ app()->getLocale() === 'ar' 
                    ? 'نقدر ملاحظاتك واقتراحاتك. يساعدنا رأيك في تحسين خدماتنا.' 
                    : 'We value your feedback and suggestions. Your opinion helps us improve our services.' }}
            </p>
        </div>

        <form method="POST" action="{{ route('client.feedback.store') }}" class="space-y-6">
            @csrf

            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'نوع الملاحظة' : 'Feedback Type' }}
                    <span class="text-red-500">*</span>
                </label>
                <select 
                    id="type" 
                    name="type" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition duration-200 outline-none"
                >
                    <option value="">{{ app()->getLocale() === 'ar' ? 'اختر النوع' : 'Select Type' }}</option>
                    <option value="bug" {{ old('type') === 'bug' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'بلاغ عن خطأ' : 'Bug Report' }}
                    </option>
                    <option value="suggestion" {{ old('type') === 'suggestion' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'اقتراح' : 'Suggestion' }}
                    </option>
                    <option value="complaint" {{ old('type') === 'complaint' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'شكوى' : 'Complaint' }}
                    </option>
                    <option value="compliment" {{ old('type') === 'compliment' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'إشادة' : 'Compliment' }}
                    </option>
                    <option value="other" {{ old('type') === 'other' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'أخرى' : 'Other' }}
                    </option>
                </select>
                @error('type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'الموضوع' : 'Subject' }}
                    <span class="text-gray-500 text-xs">({{ app()->getLocale() === 'ar' ? 'اختياري' : 'Optional' }})</span>
                </label>
                <input 
                    id="subject" 
                    type="text" 
                    name="subject" 
                    value="{{ old('subject') }}" 
                    maxlength="255"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition duration-200 outline-none"
                    placeholder="{{ app()->getLocale() === 'ar' ? 'أدخل موضوع الملاحظة (اختياري)' : 'Enter feedback subject (optional)' }}"
                >
                @error('subject')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'الرسالة' : 'Message' }}
                    <span class="text-red-500">*</span>
                </label>
                <textarea 
                    id="message" 
                    name="message" 
                    rows="8"
                    required
                    minlength="10"
                    maxlength="5000"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition duration-200 outline-none"
                    placeholder="{{ app()->getLocale() === 'ar' ? 'أدخل ملاحظتك هنا... (حد أدنى 10 أحرف)' : 'Enter your feedback here... (minimum 10 characters)' }}"
                >{{ old('message') }}</textarea>
                <p class="mt-1 text-xs text-gray-500">
                    {{ app()->getLocale() === 'ar' 
                        ? 'الحد الأدنى: 10 أحرف | الحد الأقصى: 5000 حرف' 
                        : 'Minimum: 10 characters | Maximum: 5000 characters' }}
                </p>
                @error('message')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-4 pt-4 border-t border-gray-200">
                <a href="{{ route('client.dashboard') }}" class="px-6 py-3 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200">
                    {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                </a>
                <button 
                    type="submit" 
                    class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-teal-700 transition duration-200 font-medium"
                >
                    <i class="bi bi-send me-2"></i>
                    {{ app()->getLocale() === 'ar' ? 'إرسال الملاحظة' : 'Submit Feedback' }}
                </button>
            </div>
        </form>
    </div>

    <!-- View Previous Feedback -->
    <div class="mt-6 text-center">
        <a href="{{ route('client.feedback.index') }}" class="text-primary hover:underline text-sm">
            {{ app()->getLocale() === 'ar' ? 'عرض الملاحظات السابقة' : 'View Previous Feedback' }}
        </a>
    </div>
</div>
@endsection
