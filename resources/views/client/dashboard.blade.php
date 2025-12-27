@extends('client.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'لوحة التحكم' : 'Dashboard')

@section('page-title', app()->getLocale() === 'ar' ? 'لوحة التحكم' : 'Dashboard')
@section('page-description', app()->getLocale() === 'ar' ? 'نظرة عامة على طلباتك وطلباتك' : 'Overview of your requests and orders')

@section('content')
<div class="max-w-7xl mx-auto">

        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">{{ app()->getLocale() === 'ar' ? 'إجمالي الطلبات' : 'Total Requests' }}</p>
                        <p class="text-2xl font-bold text-slate-900 mt-2">{{ $stats['total_requests'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="bi bi-file-text text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">{{ app()->getLocale() === 'ar' ? 'طلبات قيد الانتظار' : 'Pending Requests' }}</p>
                        <p class="text-2xl font-bold text-slate-900 mt-2">{{ $stats['pending_requests'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="bi bi-clock text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">{{ app()->getLocale() === 'ar' ? 'إجمالي الطلبات' : 'Total Orders' }}</p>
                        <p class="text-2xl font-bold text-slate-900 mt-2">{{ $stats['total_orders'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="bi bi-cart-check text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">{{ app()->getLocale() === 'ar' ? 'طلبات نشطة' : 'Active Orders' }}</p>
                        <p class="text-2xl font-bold text-slate-900 mt-2">{{ $stats['active_orders'] }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="bi bi-activity text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Requests -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-slate-900">
                        {{ app()->getLocale() === 'ar' ? 'الطلبات الأخيرة' : 'Recent Requests' }}
                    </h3>
                </div>
                <div class="p-6">
                    @if($recentRequests->count() > 0)
                        <div class="space-y-4">
                            @foreach($recentRequests as $request)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-slate-900">#{{ $request->id }}</p>
                                        <p class="text-sm text-gray-600">{{ $request->created_at->format('M d, Y') }}</p>
                                    </div>
                                    <span class="px-3 py-1 text-xs font-medium rounded-full 
                                        {{ $request->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $request->status === 'accepted' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $request->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">
                            {{ app()->getLocale() === 'ar' ? 'لا توجد طلبات' : 'No requests yet' }}
                        </p>
                    @endif
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-slate-900">
                        {{ app()->getLocale() === 'ar' ? 'الطلبات الأخيرة' : 'Recent Orders' }}
                    </h3>
                </div>
                <div class="p-6">
                    @if($recentOrders->count() > 0)
                        <div class="space-y-4">
                            @foreach($recentOrders as $order)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                    <div>
                                        <p class="font-medium text-slate-900">Order #{{ $order->id }}</p>
                                        <p class="text-sm text-gray-600">
                                            {{ $order->pharmacy->name ?? 'N/A' }}
                                        </p>
                                    </div>
                                    <span class="px-3 py-1 text-xs font-medium rounded-full 
                                        {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $order->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">
                            {{ app()->getLocale() === 'ar' ? 'لا توجد طلبات' : 'No orders yet' }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
</div>
@endsection

