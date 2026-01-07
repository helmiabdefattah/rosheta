@extends('laboratories.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'إنشاء تذكرة دعم' : 'Create Support Ticket')

@section('page-title', app()->getLocale() === 'ar' ? 'إنشاء تذكرة دعم' : 'Create Support Ticket')
@section('page-description', app()->getLocale() === 'ar' ? 'إنشاء تذكرة دعم فني جديدة' : 'Create a new support ticket')

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
                {{ app()->getLocale() === 'ar' ? 'إنشاء تذكرة دعم' : 'Create Support Ticket' }}
            </h2>
            <p class="text-sm text-gray-600 mt-2">
                {{ app()->getLocale() === 'ar' 
                    ? 'يرجى وصف مشكلتك أو طلبك بالتفصيل. سنقوم بالرد في أقرب وقت ممكن.' 
                    : 'Please describe your issue or request in detail. We will respond as soon as possible.' }}
            </p>
        </div>

        <form method="POST" action="{{ route('laboratories.support-tickets.store') }}" class="space-y-6">
            @csrf

            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ app()->getLocale() === 'ar' ? 'نوع التذكرة' : 'Ticket Type' }}
                        <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="type" 
                        name="type" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition duration-200 outline-none"
                    >
                        <option value="">{{ app()->getLocale() === 'ar' ? 'اختر النوع' : 'Select Type' }}</option>
                        <option value="technical" {{ old('type') === 'technical' ? 'selected' : '' }}>
                            {{ app()->getLocale() === 'ar' ? 'مشكلة تقنية' : 'Technical Issue' }}
                        </option>
                        <option value="billing" {{ old('type') === 'billing' ? 'selected' : '' }}>
                            {{ app()->getLocale() === 'ar' ? 'الفوترة' : 'Billing' }}
                        </option>
                        <option value="feature_request" {{ old('type') === 'feature_request' ? 'selected' : '' }}>
                            {{ app()->getLocale() === 'ar' ? 'طلب ميزة' : 'Feature Request' }}
                        </option>
                        <option value="complaint" {{ old('type') === 'complaint' ? 'selected' : '' }}>
                            {{ app()->getLocale() === 'ar' ? 'شكوى' : 'Complaint' }}
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
                    <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ app()->getLocale() === 'ar' ? 'الأولوية' : 'Priority' }}
                        <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="priority" 
                        name="priority" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition duration-200 outline-none"
                    >
                        <option value="">{{ app()->getLocale() === 'ar' ? 'اختر الأولوية' : 'Select Priority' }}</option>
                        <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>
                            {{ app()->getLocale() === 'ar' ? 'منخفضة' : 'Low' }}
                        </option>
                        <option value="medium" {{ old('priority') === 'medium' ? 'selected' : '' }}>
                            {{ app()->getLocale() === 'ar' ? 'متوسطة' : 'Medium' }}
                        </option>
                        <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>
                            {{ app()->getLocale() === 'ar' ? 'عالية' : 'High' }}
                        </option>
                        <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>
                            {{ app()->getLocale() === 'ar' ? 'عاجلة' : 'Urgent' }}
                        </option>
                    </select>
                    @error('priority')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
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
                    placeholder="{{ app()->getLocale() === 'ar' ? 'أدخل موضوع التذكرة (اختياري)' : 'Enter ticket subject (optional)' }}"
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
                    placeholder="{{ app()->getLocale() === 'ar' ? 'أدخل تفاصيل مشكلتك أو طلبك هنا... (حد أدنى 10 أحرف)' : 'Enter details of your issue or request here... (minimum 10 characters)' }}"
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
                <a href="{{ route('laboratories.dashboard') }}" class="px-6 py-3 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200">
                    {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                </a>
                <button 
                    type="submit" 
                    class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-teal-700 transition duration-200 font-medium"
                >
                    <i class="bi bi-send me-2"></i>
                    {{ app()->getLocale() === 'ar' ? 'إرسال التذكرة' : 'Submit Ticket' }}
                </button>
            </div>
        </form>
    </div>

    <!-- View Previous Tickets -->
    <div class="mt-6 text-center">
        <a href="{{ route('laboratories.support-tickets.index') }}" class="text-primary hover:underline text-sm">
            {{ app()->getLocale() === 'ar' ? 'عرض التذاكر السابقة' : 'View Previous Tickets' }}
        </a>
    </div>
</div>
@endsection
