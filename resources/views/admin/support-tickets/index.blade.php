@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'تذاكر الدعم' : 'Support Tickets')
@section('page-title', app()->getLocale() === 'ar' ? 'تذاكر الدعم' : 'Support Tickets')
@section('page-description', app()->getLocale() === 'ar' ? 'عرض وإدارة تذاكر الدعم' : 'View and manage support tickets')

@section('content')
@if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <p class="text-sm text-green-800">{{ session('success') }}</p>
    </div>
@endif

<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <!-- Filters -->
    <div class="p-6 border-b border-slate-200">
        <form method="GET" action="{{ route('admin.support-tickets.index') }}" class="flex flex-wrap gap-4">
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
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'مفتوحة' : 'Open' }}
                    </option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'قيد المعالجة' : 'In Progress' }}
                    </option>
                    <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'تم الحل' : 'Resolved' }}
                    </option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'مغلقة' : 'Closed' }}
                    </option>
                </select>
            </div>
            <div>
                <select name="type" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    <option value="">{{ app()->getLocale() === 'ar' ? 'جميع الأنواع' : 'All Types' }}</option>
                    <option value="technical" {{ request('type') === 'technical' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'مشكلة تقنية' : 'Technical Issue' }}
                    </option>
                    <option value="billing" {{ request('type') === 'billing' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'الفوترة' : 'Billing' }}
                    </option>
                    <option value="feature_request" {{ request('type') === 'feature_request' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'طلب ميزة' : 'Feature Request' }}
                    </option>
                    <option value="complaint" {{ request('type') === 'complaint' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'شكوى' : 'Complaint' }}
                    </option>
                    <option value="other" {{ request('type') === 'other' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'أخرى' : 'Other' }}
                    </option>
                </select>
            </div>
            <div>
                <select name="priority" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    <option value="">{{ app()->getLocale() === 'ar' ? 'جميع الأولويات' : 'All Priorities' }}</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'منخفضة' : 'Low' }}
                    </option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'متوسطة' : 'Medium' }}
                    </option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'عالية' : 'High' }}
                    </option>
                    <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'عاجلة' : 'Urgent' }}
                    </option>
                </select>
            </div>
            <div>
                <select name="ticketable_type" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary">
                    <option value="">{{ app()->getLocale() === 'ar' ? 'جميع الأنواع' : 'All Sources' }}</option>
                    <option value="App\Models\Laboratory" {{ request('ticketable_type') === 'App\Models\Laboratory' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'معامل' : 'Laboratories' }}
                    </option>
                    <option value="App\Models\Nurse" {{ request('ticketable_type') === 'App\Models\Nurse' ? 'selected' : '' }}>
                        {{ app()->getLocale() === 'ar' ? 'ممرضات' : 'Nurses' }}
                    </option>
                </select>
            </div>
            <div>
                <button type="submit" class="px-6 py-2 bg-primary text-white rounded-lg hover:bg-teal-600 transition duration-200">
                    {{ app()->getLocale() === 'ar' ? 'تصفية' : 'Filter' }}
                </button>
            </div>
            @if(request()->has('search') || request()->has('status') || request()->has('type') || request()->has('priority') || request()->has('ticketable_type'))
                <div>
                    <a href="{{ route('admin.support-tickets.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition duration-200">
                        {{ app()->getLocale() === 'ar' ? 'إعادة تعيين' : 'Reset' }}
                    </a>
                </div>
            @endif
        </form>
    </div>

    <!-- Tickets List -->
    <div class="p-6">
        @if($tickets->count() > 0)
            <div class="space-y-4">
                @foreach($tickets as $ticket)
                    <div class="border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2 flex-wrap">
                                    <span class="px-3 py-1 text-xs font-medium rounded-full 
                                        @if($ticket->type === 'technical') bg-blue-100 text-blue-800
                                        @elseif($ticket->type === 'billing') bg-yellow-100 text-yellow-800
                                        @elseif($ticket->type === 'feature_request') bg-green-100 text-green-800
                                        @elseif($ticket->type === 'complaint') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        @if($ticket->type === 'technical')
                                            {{ app()->getLocale() === 'ar' ? 'مشكلة تقنية' : 'Technical Issue' }}
                                        @elseif($ticket->type === 'billing')
                                            {{ app()->getLocale() === 'ar' ? 'الفوترة' : 'Billing' }}
                                        @elseif($ticket->type === 'feature_request')
                                            {{ app()->getLocale() === 'ar' ? 'طلب ميزة' : 'Feature Request' }}
                                        @elseif($ticket->type === 'complaint')
                                            {{ app()->getLocale() === 'ar' ? 'شكوى' : 'Complaint' }}
                                        @else
                                            {{ app()->getLocale() === 'ar' ? 'أخرى' : 'Other' }}
                                        @endif
                                    </span>
                                    <span class="px-3 py-1 text-xs font-medium rounded-full 
                                        @if($ticket->priority === 'urgent') bg-red-100 text-red-800
                                        @elseif($ticket->priority === 'high') bg-orange-100 text-orange-800
                                        @elseif($ticket->priority === 'medium') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        @if($ticket->priority === 'urgent')
                                            {{ app()->getLocale() === 'ar' ? 'عاجلة' : 'Urgent' }}
                                        @elseif($ticket->priority === 'high')
                                            {{ app()->getLocale() === 'ar' ? 'عالية' : 'High' }}
                                        @elseif($ticket->priority === 'medium')
                                            {{ app()->getLocale() === 'ar' ? 'متوسطة' : 'Medium' }}
                                        @else
                                            {{ app()->getLocale() === 'ar' ? 'منخفضة' : 'Low' }}
                                        @endif
                                    </span>
                                    <span class="px-3 py-1 text-xs font-medium rounded-full 
                                        @if($ticket->status === 'open') bg-blue-100 text-blue-800
                                        @elseif($ticket->status === 'in_progress') bg-yellow-100 text-yellow-800
                                        @elseif($ticket->status === 'resolved') bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        @if($ticket->status === 'open')
                                            {{ app()->getLocale() === 'ar' ? 'مفتوحة' : 'Open' }}
                                        @elseif($ticket->status === 'in_progress')
                                            {{ app()->getLocale() === 'ar' ? 'قيد المعالجة' : 'In Progress' }}
                                        @elseif($ticket->status === 'resolved')
                                            {{ app()->getLocale() === 'ar' ? 'تم الحل' : 'Resolved' }}
                                        @else
                                            {{ app()->getLocale() === 'ar' ? 'مغلقة' : 'Closed' }}
                                        @endif
                                    </span>
                                    <span class="px-3 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">
                                        @if($ticket->ticketable_type === 'App\Models\Laboratory')
                                            {{ app()->getLocale() === 'ar' ? 'معمل' : 'Laboratory' }}
                                        @else
                                            {{ app()->getLocale() === 'ar' ? 'ممرضة' : 'Nurse' }}
                                        @endif
                                    </span>
                                </div>
                                @if($ticket->subject)
                                    <h3 class="text-lg font-semibold text-slate-900 mb-2">
                                        {{ $ticket->subject }}
                                    </h3>
                                @endif
                                <p class="text-sm text-gray-700 mb-3 line-clamp-2">
                                    {{ \Illuminate\Support\Str::limit($ticket->message, 150) }}
                                </p>
                                <div class="flex items-center gap-4 text-xs text-gray-500">
                                    <span>
                                        <strong>{{ app()->getLocale() === 'ar' ? 'المرسل:' : 'From:' }}</strong> 
                                        @if($ticket->ticketable_type === 'App\Models\Laboratory')
                                            {{ $ticket->ticketable->name ?? 'N/A' }}
                                        @else
                                            {{ $ticket->ticketable->user->name ?? 'N/A' }}
                                        @endif
                                    </span>
                                    @if($ticket->assignedAdmin)
                                        <span>
                                            <strong>{{ app()->getLocale() === 'ar' ? 'مخصص لـ:' : 'Assigned to:' }}</strong> 
                                            {{ $ticket->assignedAdmin->name }}
                                        </span>
                                    @endif
                                    <span>
                                        <strong>{{ app()->getLocale() === 'ar' ? 'التاريخ:' : 'Date:' }}</strong> 
                                        {{ $ticket->created_at->format('Y-m-d H:i') }}
                                    </span>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('admin.support-tickets.show', $ticket) }}" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-teal-600 transition duration-200 text-sm">
                                    {{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $tickets->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="bi bi-ticket-perforated text-gray-400 text-6xl mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">
                    {{ app()->getLocale() === 'ar' ? 'لا توجد تذاكر' : 'No Tickets' }}
                </h3>
                <p class="text-gray-500">
                    {{ app()->getLocale() === 'ar' 
                        ? 'لا توجد تذاكر دعم لعرضها.' 
                        : 'No support tickets to display.' }}
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
