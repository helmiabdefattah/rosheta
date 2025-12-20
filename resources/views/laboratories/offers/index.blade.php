@extends('laboratories.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'العروض المرسلة' : 'Sent Offers')

@section('page-description', app()->getLocale() === 'ar' ? 'عرض جميع العروض المرسلة من المعمل' : 'View all offers sent by the laboratory')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
@endpush

@section('content')
    <div class="bg-white rounded-lg shadow-sm mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <form method="GET" action="{{ route('laboratories.offers.index') }}" class="flex gap-4 items-end" id="filterForm">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ app()->getLocale() === 'ar' ? 'البحث' : 'Search' }}</label>
                    <input type="text" name="search" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" 
                           placeholder="{{ app()->getLocale() === 'ar' ? 'ابحث برقم العرض أو اسم العميل أو الهاتف...' : 'Search by Offer ID, Client Name, or Phone...' }}"
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
                        <a href="{{ route('laboratories.offers.index') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors ml-2">
                            {{ app()->getLocale() === 'ar' ? 'مسح' : 'Clear' }}
                        </a>
                    @endif
                </div>
            </form>
        </div>
        <div class="overflow-x-auto">
            @if($offers->count() > 0)
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'رقم العرض' : 'Offer ID' }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'رقم الطلب' : 'Request ID' }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'العميل' : 'Client' }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'السعر الإجمالي' : 'Total Price' }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'تاريخ الإنشاء' : 'Created At' }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($offers as $offer)
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'accepted' => 'bg-green-100 text-green-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                    'draft' => 'bg-gray-100 text-gray-800',
                                ];
                                $statusColor = $statusColors[$offer->status] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-semibold text-slate-800">#{{ $offer->id }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-slate-600">#{{ $offer->client_request_id }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <span class="text-sm font-medium text-slate-800">{{ $offer->request->client->name ?? 'N/A' }}</span>
                                        @if($offer->request->client)
                                            <br><span class="text-xs text-slate-500">{{ $offer->request->client->phone_number ?? '' }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-semibold text-primary-600">{{ number_format($offer->total_price, 2) }} {{ app()->getLocale() === 'ar' ? 'جنيه' : 'EGP' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColor }}">
                                        {{ ucfirst($offer->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-slate-600">{{ $offer->created_at->format('Y-m-d H:i') }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center gap-2">
                                        <button type="button" class="text-primary-600 hover:text-primary-900" data-bs-toggle="modal" data-bs-target="#offerModal{{ $offer->id }}">
                                            {{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}
                                        </button>
                                        @if($offer->status == 'pending')
                                            <form method="POST" action="{{ route('laboratories.offers.cancel', $offer) }}" class="inline" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من إلغاء هذا العرض؟' : 'Are you sure you want to cancel this offer?' }}');">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <!-- Modals placed outside the table -->
                @foreach($offers as $offer)
                    @php
                        $statusColors = [
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'accepted' => 'bg-green-100 text-green-800',
                            'rejected' => 'bg-red-100 text-red-800',
                            'draft' => 'bg-gray-100 text-gray-800',
                        ];
                        $statusColor = $statusColors[$offer->status] ?? 'bg-gray-100 text-gray-800';
                    @endphp
                    <!-- Offer Details Modal -->
                    <div class="modal fade" id="offerModal{{ $offer->id }}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">{{ app()->getLocale() === 'ar' ? 'تفاصيل العرض' : 'Offer Details' }} #{{ $offer->id }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <strong>{{ app()->getLocale() === 'ar' ? 'رقم الطلب' : 'Request ID' }}:</strong><br>
                                            #{{ $offer->client_request_id }}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}:</strong><br>
                                            <span class="badge {{ $statusColor }}">{{ ucfirst($offer->status) }}</span>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <strong>{{ app()->getLocale() === 'ar' ? 'العميل' : 'Client' }}:</strong><br>
                                            {{ $offer->request->client->name ?? 'N/A' }}<br>
                                            <small class="text-muted">{{ $offer->request->client->phone_number ?? '' }}</small>
                                        </div>
                                        <div class="col-md-6">
                                            <strong>{{ app()->getLocale() === 'ar' ? 'السعر الإجمالي' : 'Total Price' }}:</strong><br>
                                            <span class="h5 text-primary">{{ number_format($offer->total_price, 2) }} {{ app()->getLocale() === 'ar' ? 'جنيه' : 'EGP' }}</span>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <strong>{{ app()->getLocale() === 'ar' ? 'تاريخ الإنشاء' : 'Created At' }}:</strong><br>
                                            {{ $offer->created_at->format('Y-m-d H:i') }}
                                        </div>
                                        <div class="col-md-6">
                                            <strong>{{ app()->getLocale() === 'ar' ? 'آخر تحديث' : 'Last Updated' }}:</strong><br>
                                            {{ $offer->updated_at->format('Y-m-d H:i') }}
                                        </div>
                                    </div>
                                    <hr>
                                    <h6>{{ app()->getLocale() === 'ar' ? 'تفاصيل العرض' : 'Offer Items' }}</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead>
                                                <tr>
                                                    @if($offer->request_type == 'test')
                                                        <th>{{ app()->getLocale() === 'ar' ? 'اسم الفحص (EN)' : 'Test Name (EN)' }}</th>
                                                        <th>{{ app()->getLocale() === 'ar' ? 'اسم الفحص (AR)' : 'Test Name (AR)' }}</th>
                                                    @else
                                                        <th>{{ app()->getLocale() === 'ar' ? 'الدواء' : 'Medicine' }}</th>
                                                        <th>{{ app()->getLocale() === 'ar' ? 'الكمية' : 'Quantity' }}</th>
                                                        <th>{{ app()->getLocale() === 'ar' ? 'الوحدة' : 'Unit' }}</th>
                                                    @endif
                                                    <th>{{ app()->getLocale() === 'ar' ? 'السعر' : 'Price' }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if($offer->request_type == 'test')
                                                    @foreach($offer->testLines as $line)
                                                        <tr>
                                                            <td>{{ $line->medicalTest->test_name_en ?? 'N/A' }}</td>
                                                            <td>{{ $line->medicalTest->test_name_ar ?? 'N/A' }}</td>
                                                            <td>{{ number_format($line->price, 2) }} {{ app()->getLocale() === 'ar' ? 'جنيه' : 'EGP' }}</td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    @foreach($offer->medicineLines as $line)
                                                        <tr>
                                                            <td>{{ $line->medicine->name ?? 'N/A' }}</td>
                                                            <td>{{ $line->quantity ?? 1 }}</td>
                                                            <td>{{ $line->unit ?? 'box' }}</td>
                                                            <td>{{ number_format($line->price, 2) }} {{ app()->getLocale() === 'ar' ? 'جنيه' : 'EGP' }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ app()->getLocale() === 'ar' ? 'إغلاق' : 'Close' }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
                </table>
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $offers->links() }}
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <p class="text-slate-500">{{ app()->getLocale() === 'ar' ? 'لا توجد عروض مرسلة' : 'No sent offers found' }}</p>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Auto-submit form when status changes
            $('#statusFilter').on('change', function() {
                $('#filterForm').submit();
            });
        });
    </script>
@endpush

