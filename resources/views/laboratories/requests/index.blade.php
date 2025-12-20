@extends('laboratories.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'الطلبات الحالية' : 'Current Requests')

@section('page-description', app()->getLocale() === 'ar' ? 'عرض جميع طلبات الفحوصات الطبية' : 'View all medical test requests')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
@endpush

@section('content')
    <div class="bg-white rounded-lg shadow-sm mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <form method="GET" action="{{ route('laboratories.requests.index') }}" class="flex gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ app()->getLocale() === 'ar' ? 'البحث' : 'Search' }}</label>
                    <input type="text" name="search" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" 
                           placeholder="{{ app()->getLocale() === 'ar' ? 'ابحث برقم الطلب أو اسم العميل أو الهاتف...' : 'Search by Request ID, Client Name, or Phone...' }}"
                           value="{{ request('search') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</label>
                    <select name="status" id="statusFilter" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">{{ app()->getLocale() === 'ar' ? 'جميع الحالات' : 'All Statuses' }}</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'معلق' : 'Pending' }}</option>
                        <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'مقبول' : 'Accepted' }}</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>{{ app()->getLocale() === 'ar' ? 'مرفوض' : 'Rejected' }}</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                        {{ app()->getLocale() === 'ar' ? 'بحث' : 'Search' }}
                    </button>
                    @if(request('search') || request('status'))
                        <a href="{{ route('laboratories.requests.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors ml-2">
                            {{ app()->getLocale() === 'ar' ? 'مسح' : 'Clear' }}
                        </a>
                    @endif
                </div>
            </form>
        </div>
        <div class="overflow-x-auto">
            @if($requests->count() > 0)
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'رقم الطلب' : 'Request ID' }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'العميل' : 'Client' }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'الهاتف' : 'Phone' }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'عدد الفحوصات' : 'Tests Count' }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'تاريخ الإنشاء' : 'Created At' }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($requests as $request)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-semibold text-slate-800">#{{ $request->id }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-slate-600">{{ $request->client->name ?? 'N/A' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-slate-600">{{ $request->client->phone_number ?? 'N/A' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                        {{ $request->lines->where('item_type', 'test')->count() ?? 0 }} {{ app()->getLocale() === 'ar' ? 'فحص' : 'Test(s)' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'accepted' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                        ];
                                        $statusColor = $statusColors[$request->status] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColor }}">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-slate-600">{{ $request->created_at->format('Y-m-d H:i') }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    @if($request->status == 'pending')
                                        <a href="{{ route('offers.create', ['request' => $request->id]) }}" class="text-primary-600 hover:text-primary-900 mr-4">
                                            {{ app()->getLocale() === 'ar' ? 'إنشاء عرض' : 'Make Offer' }}
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $requests->links() }}
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <p class="text-slate-500">{{ app()->getLocale() === 'ar' ? 'لا توجد طلبات' : 'No requests found' }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Auto-submit form when status changes
            $('#statusFilter').on('change', function() {
                $(this).closest('form').submit();
            });
        });
    </script>
@endpush

