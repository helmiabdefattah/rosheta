@extends('nurse.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'تذاكر الدعم' : 'Support Tickets')

@section('page-title', app()->getLocale() === 'ar' ? 'تذاكر الدعم' : 'Support Tickets')
@section('page-description', app()->getLocale() === 'ar' ? 'عرض جميع تذاكر الدعم التي أرسلتها' : 'View all support tickets you have submitted')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">
                {{ app()->getLocale() === 'ar' ? 'تذاكر الدعم' : 'Support Tickets' }}
            </h2>
            <p class="text-sm text-gray-600 mt-1">
                {{ app()->getLocale() === 'ar' 
                    ? 'جميع تذاكر الدعم التي أرسلتها' 
                    : 'All support tickets you have submitted' }}
            </p>
        </div>
        <a href="{{ route('nurse.support-tickets.create') }}" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-teal-700 transition duration-200 text-sm font-medium">
            <i class="bi bi-plus-circle me-1"></i>
            {{ app()->getLocale() === 'ar' ? 'تذكرة جديدة' : 'New Ticket' }}
        </a>
    </div>

    @if($tickets->count() > 0)
        <div class="space-y-4">
            @foreach($tickets as $ticket)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
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
                            </div>
                            @if($ticket->subject)
                                <h3 class="text-lg font-semibold text-slate-900 mb-2">
                                    {{ $ticket->subject }}
                                </h3>
                            @endif
                            <p class="text-sm text-gray-700 mb-3">
                                {{ \Illuminate\Support\Str::limit($ticket->message, 150) }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ app()->getLocale() === 'ar' ? 'تم الإرسال في' : 'Submitted on' }}: 
                                {{ $ticket->created_at->format('Y-m-d H:i') }}
                            </p>
                        </div>
                    </div>

                    @if($ticket->admin_response)
                        <div class="mt-4 pt-4 border-t border-gray-200 bg-blue-50 rounded-lg p-4">
                            <h4 class="text-sm font-semibold text-blue-900 mb-2">
                                {{ app()->getLocale() === 'ar' ? 'رد الإدارة' : 'Admin Response' }}
                            </h4>
                            <p class="text-sm text-blue-800">
                                {{ $ticket->admin_response }}
                            </p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $tickets->links() }}
        </div>
    @else
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
            <i class="bi bi-ticket-perforated text-gray-400 text-6xl mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">
                {{ app()->getLocale() === 'ar' ? 'لا توجد تذاكر' : 'No Tickets' }}
            </h3>
            <p class="text-gray-500 mb-6">
                {{ app()->getLocale() === 'ar' 
                    ? 'لم تقم بإنشاء أي تذاكر دعم بعد.' 
                    : 'You haven\'t created any support tickets yet.' }}
            </p>
            <a href="{{ route('nurse.support-tickets.create') }}" class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-lg hover:bg-teal-700 transition duration-200 font-medium">
                <i class="bi bi-plus-circle me-2"></i>
                {{ app()->getLocale() === 'ar' ? 'إنشاء تذكرة جديدة' : 'Create New Ticket' }}
            </a>
        </div>
    @endif
</div>
@endsection
