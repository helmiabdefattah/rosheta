@extends($layout)

@section('title', app()->getLocale() === 'ar' ? 'جميع الإشعارات' : 'All Notifications')
@section('page-title', app()->getLocale() === 'ar' ? 'جميع الإشعارات' : 'All Notifications')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-slate-800">
            {{ app()->getLocale() === 'ar' ? 'مركز الإشعارات' : 'Notification Center' }}
        </h1>
        
        <div class="flex gap-2">
            <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                @csrf
                <button type="submit" class="text-sm font-medium text-primary hover:text-primary/80 px-4 py-2 bg-primary/10 rounded-lg transition-colors">
                    {{ app()->getLocale() === 'ar' ? 'تحديد الكل كمقروء' : 'Mark all as read' }}
                </button>
            </form>
            
            <form action="{{ route('notifications.destroy-all') }}" method="POST" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من حذف جميع الإشعارات؟' : 'Are you sure you want to delete all notifications?' }}')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm font-medium text-red-600 hover:text-red-700 px-4 py-2 bg-red-50 rounded-lg transition-colors">
                    {{ app()->getLocale() === 'ar' ? 'حذف الكل' : 'Delete all' }}
                </button>
            </form>
        </div>
    </div>

    @if($notifications->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            <p class="text-slate-500 font-medium">
                {{ app()->getLocale() === 'ar' ? 'لا توجد إشعارات حالياً' : 'No notifications yet' }}
            </p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($notifications as $notification)
                @php $url = $notification->data['url'] ?? $notification->data['data']['url'] ?? null; @endphp
                <div class="bg-white rounded-xl shadow-sm border {{ $notification->unread() ? 'border-primary/30 bg-primary/5' : 'border-slate-200 hover:border-slate-300' }} transition-all overflow-hidden group 
                     {{ $url ? 'cursor-pointer' : '' }}"
                     @if($url) onclick="markAsReadAndRedirect('{{ $notification->id }}', '{{ url($url) }}')" @endif>
                    <div class="flex items-start p-4 gap-4">
                        <!-- Icon/Indicator -->
                        <div class="flex-shrink-0 mt-1">
                            @if($notification->unread())
                                <div class="w-3 h-3 rounded-full bg-primary animate-pulse"></div>
                            @else
                                <div class="w-3 h-3 rounded-full bg-slate-300"></div>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-4 mb-1">
                                <h3 class="text-sm font-bold text-slate-900 truncate">
                                    {{ $notification->data['title_' . app()->getLocale()] ?? $notification->data['title'] ?? 'Notification' }}
                                </h3>
                                <span class="text-xs text-slate-400 whitespace-nowrap">
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>
                            </div>
                            <p class="text-sm text-slate-600 mb-3">
                                {{ $notification->data['message_' . app()->getLocale()] ?? $notification->data['message'] ?? $notification->data['body'] ?? '' }}
                            </p>
                            
                            <div class="flex items-center gap-3">
                                @if($url)
                                    <span class="text-xs font-semibold text-primary group-hover:underline flex items-center gap-1">
                                        {{ app()->getLocale() === 'ar' ? 'عرض التفاصيل' : 'View Details' }}
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </span>
                                @endif

                                @if($notification->unread())
                                    <button onclick="event.stopPropagation(); markAsRead('{{ $notification->id }}', this)" 
                                            class="text-xs font-medium text-slate-500 hover:text-primary transition-colors relative z-10">
                                        {{ app()->getLocale() === 'ar' ? 'تحديد كمقروء' : 'Mark as read' }}
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity relative z-10">
                            <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'حذف هذا الإشعار؟' : 'Delete this notification?' }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="event.stopPropagation();" class="p-2 text-slate-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition-all">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $notifications->links() }}
        </div>
    @endif
</div>

<script>
    async function markAsRead(id, button) {
        try {
            const response = await fetch(`/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            });
            
            if (response.ok) {
                // Find parent container and update styles
                const container = button.closest('.bg-white');
                container.classList.remove('border-primary/30', 'bg-primary/5');
                container.classList.add('border-slate-200');
                
                // Update indicator
                const indicator = container.querySelector('.bg-primary');
                if (indicator) {
                    indicator.classList.remove('bg-primary', 'animate-pulse');
                    indicator.classList.add('bg-slate-300');
                }
                
                button.remove();
            }
        } catch (error) {
            console.error('Error marking as read:', error);
        }
    }

    async function markAsReadAndRedirect(id, url) {
        try {
            await fetch(`/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            });
        } catch (error) {
            console.error('Error marking as read before redirect:', error);
        } finally {
            window.location.href = url;
        }
    }
</script>
@endsection
