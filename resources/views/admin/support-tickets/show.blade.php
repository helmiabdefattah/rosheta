@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'تفاصيل التذكرة' : 'Ticket Details')
@section('page-title', app()->getLocale() === 'ar' ? 'تفاصيل التذكرة' : 'Ticket Details')

@section('content')
<div class="space-y-6">
    <!-- Back Button -->
    <div>
        <a href="{{ route('admin.support-tickets.index') }}" class="inline-flex items-center text-primary hover:underline">
            <i class="bi bi-arrow-left me-2"></i>
            {{ app()->getLocale() === 'ar' ? 'العودة إلى القائمة' : 'Back to List' }}
        </a>
    </div>

    <!-- Ticket Details -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
        <div class="flex items-start justify-between mb-6">
            <div>
                <div class="flex items-center gap-3 mb-3 flex-wrap">
                    <span class="px-3 py-1 text-sm font-medium rounded-full 
                        @if($supportTicket->type === 'technical') bg-blue-100 text-blue-800
                        @elseif($supportTicket->type === 'billing') bg-yellow-100 text-yellow-800
                        @elseif($supportTicket->type === 'feature_request') bg-green-100 text-green-800
                        @elseif($supportTicket->type === 'complaint') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        @if($supportTicket->type === 'technical')
                            {{ app()->getLocale() === 'ar' ? 'مشكلة تقنية' : 'Technical Issue' }}
                        @elseif($supportTicket->type === 'billing')
                            {{ app()->getLocale() === 'ar' ? 'الفوترة' : 'Billing' }}
                        @elseif($supportTicket->type === 'feature_request')
                            {{ app()->getLocale() === 'ar' ? 'طلب ميزة' : 'Feature Request' }}
                        @elseif($supportTicket->type === 'complaint')
                            {{ app()->getLocale() === 'ar' ? 'شكوى' : 'Complaint' }}
                        @else
                            {{ app()->getLocale() === 'ar' ? 'أخرى' : 'Other' }}
                        @endif
                    </span>
                    <span class="px-3 py-1 text-sm font-medium rounded-full 
                        @if($supportTicket->priority === 'urgent') bg-red-100 text-red-800
                        @elseif($supportTicket->priority === 'high') bg-orange-100 text-orange-800
                        @elseif($supportTicket->priority === 'medium') bg-yellow-100 text-yellow-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        @if($supportTicket->priority === 'urgent')
                            {{ app()->getLocale() === 'ar' ? 'عاجلة' : 'Urgent' }}
                        @elseif($supportTicket->priority === 'high')
                            {{ app()->getLocale() === 'ar' ? 'عالية' : 'High' }}
                        @elseif($supportTicket->priority === 'medium')
                            {{ app()->getLocale() === 'ar' ? 'متوسطة' : 'Medium' }}
                        @else
                            {{ app()->getLocale() === 'ar' ? 'منخفضة' : 'Low' }}
                        @endif
                    </span>
                    <span class="px-3 py-1 text-sm font-medium rounded-full 
                        @if($supportTicket->status === 'open') bg-blue-100 text-blue-800
                        @elseif($supportTicket->status === 'in_progress') bg-yellow-100 text-yellow-800
                        @elseif($supportTicket->status === 'resolved') bg-green-100 text-green-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        @if($supportTicket->status === 'open')
                            {{ app()->getLocale() === 'ar' ? 'مفتوحة' : 'Open' }}
                        @elseif($supportTicket->status === 'in_progress')
                            {{ app()->getLocale() === 'ar' ? 'قيد المعالجة' : 'In Progress' }}
                        @elseif($supportTicket->status === 'resolved')
                            {{ app()->getLocale() === 'ar' ? 'تم الحل' : 'Resolved' }}
                        @else
                            {{ app()->getLocale() === 'ar' ? 'مغلقة' : 'Closed' }}
                        @endif
                    </span>
                </div>
                @if($supportTicket->subject)
                    <h2 class="text-2xl font-bold text-slate-900 mb-2">
                        {{ $supportTicket->subject }}
                    </h2>
                @endif
            </div>
        </div>

        <div class="mb-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">
                {{ app()->getLocale() === 'ar' ? 'الرسالة' : 'Message' }}
            </h3>
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <p class="text-gray-700 whitespace-pre-wrap">{{ $supportTicket->message }}</p>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4 mb-6">
            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'المرسل' : 'From' }}
                </h3>
                <p class="text-gray-600">
                    @if($supportTicket->ticketable_type === 'App\Models\Laboratory')
                        <strong>{{ app()->getLocale() === 'ar' ? 'المعمل:' : 'Laboratory:' }}</strong> {{ $supportTicket->ticketable->name ?? 'N/A' }}<br>
                        <span class="text-sm text-gray-500">{{ $supportTicket->ticketable->email ?? $supportTicket->ticketable->phone ?? '' }}</span>
                    @else
                        <strong>{{ app()->getLocale() === 'ar' ? 'الممرضة:' : 'Nurse:' }}</strong> {{ $supportTicket->ticketable->user->name ?? 'N/A' }}<br>
                        <span class="text-sm text-gray-500">{{ $supportTicket->ticketable->user->email ?? '' }}</span>
                    @endif
                </p>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-gray-700 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'التاريخ' : 'Date' }}
                </h3>
                <p class="text-gray-600">
                    {{ $supportTicket->created_at->format('Y-m-d H:i') }}
                </p>
            </div>
        </div>

        @if($supportTicket->admin_response)
            <div class="mb-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <h3 class="text-sm font-semibold text-blue-900 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'رد الإدارة' : 'Admin Response' }}
                </h3>
                <p class="text-blue-800 whitespace-pre-wrap">{{ $supportTicket->admin_response }}</p>
                @if($supportTicket->resolved_at)
                    <p class="text-xs text-blue-600 mt-2">
                        {{ app()->getLocale() === 'ar' ? 'تم الحل في' : 'Resolved on' }}: 
                        {{ $supportTicket->resolved_at->format('Y-m-d H:i') }}
                    </p>
                @endif
            </div>
        @endif

        <!-- Update Form -->
        <form method="POST" action="{{ route('admin.support-tickets.update', $supportTicket) }}" class="border-t border-gray-200 pt-6">
            @csrf
            @method('PUT')
            
            <div class="space-y-4">
                <div class="grid md:grid-cols-2 gap-4">
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
                            <option value="open" {{ $supportTicket->status === 'open' ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? 'مفتوحة' : 'Open' }}
                            </option>
                            <option value="in_progress" {{ $supportTicket->status === 'in_progress' ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? 'قيد المعالجة' : 'In Progress' }}
                            </option>
                            <option value="resolved" {{ $supportTicket->status === 'resolved' ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? 'تم الحل' : 'Resolved' }}
                            </option>
                            <option value="closed" {{ $supportTicket->status === 'closed' ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? 'مغلقة' : 'Closed' }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ app()->getLocale() === 'ar' ? 'الأولوية' : 'Priority' }}
                        </label>
                        <select 
                            id="priority" 
                            name="priority" 
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                        >
                            <option value="low" {{ $supportTicket->priority === 'low' ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? 'منخفضة' : 'Low' }}
                            </option>
                            <option value="medium" {{ $supportTicket->priority === 'medium' ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? 'متوسطة' : 'Medium' }}
                            </option>
                            <option value="high" {{ $supportTicket->priority === 'high' ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? 'عالية' : 'High' }}
                            </option>
                            <option value="urgent" {{ $supportTicket->priority === 'urgent' ? 'selected' : '' }}>
                                {{ app()->getLocale() === 'ar' ? 'عاجلة' : 'Urgent' }}
                            </option>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-2">
                        {{ app()->getLocale() === 'ar' ? 'تعيين إلى' : 'Assign To' }}
                        <span class="text-gray-500 text-xs">({{ app()->getLocale() === 'ar' ? 'اختياري' : 'Optional' }})</span>
                    </label>
                    <select 
                        id="assigned_to" 
                        name="assigned_to" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary"
                    >
                        <option value="">{{ app()->getLocale() === 'ar' ? 'غير مخصص' : 'Unassigned' }}</option>
                        @foreach($admins as $admin)
                            <option value="{{ $admin->id }}" {{ $supportTicket->assigned_to == $admin->id ? 'selected' : '' }}>
                                {{ $admin->name }}
                            </option>
                        @endforeach
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
                    >{{ old('admin_response', $supportTicket->admin_response) }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">
                        {{ app()->getLocale() === 'ar' 
                            ? 'الحد الأقصى: 5000 حرف' 
                            : 'Maximum: 5000 characters' }}
                    </p>
                </div>

                <div class="flex items-center justify-end gap-4">
                    <a href="{{ route('admin.support-tickets.index') }}" class="px-6 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition duration-200">
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
