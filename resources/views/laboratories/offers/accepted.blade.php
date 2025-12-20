@extends('laboratories.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'العروض المقبولة' : 'Accepted Offers')

@section('page-description', app()->getLocale() === 'ar' ? 'عرض جميع العروض المقبولة من العملاء' : 'View all offers accepted by clients')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet" />
    <style>
        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .status-empty { background: #e5e7eb; color: #6b7280; }
        .status-sample-collected { background: #fef3c7; color: #92400e; }
        .status-test-completed { background: #d1fae5; color: #065f46; }
    </style>
@endpush

@section('content')
    <div class="bg-white rounded-lg shadow-sm mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <form method="GET" action="{{ route('laboratories.offers.accepted') }}" class="flex gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ app()->getLocale() === 'ar' ? 'البحث' : 'Search' }}</label>
                    <input type="text" name="search" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" 
                           placeholder="{{ app()->getLocale() === 'ar' ? 'ابحث برقم العرض أو اسم العميل أو الهاتف...' : 'Search by Offer ID, Client Name, or Phone...' }}"
                           value="{{ request('search') }}">
                </div>
                <div>
                    <button type="submit" class="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                        {{ app()->getLocale() === 'ar' ? 'بحث' : 'Search' }}
                    </button>
                    @if(request('search'))
                        <a href="{{ route('laboratories.offers.accepted') }}" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors ml-2">
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
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'حالة المزود' : 'Vendor Status' }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'تاريخ القبول' : 'Accepted At' }}</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($offers as $offer)
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
                                    <span class="text-sm font-semibold text-green-600">{{ number_format($offer->total_price, 2) }} {{ app()->getLocale() === 'ar' ? 'جنيه' : 'EGP' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusClass = 'status-empty';
                                        $statusText = app()->getLocale() === 'ar' ? 'غير محدد' : 'Not Set';
                                        if ($offer->vendor_status == 'sample_collected') {
                                            $statusClass = 'status-sample-collected';
                                            $statusText = app()->getLocale() === 'ar' ? 'تم جمع العينة' : 'Sample Collected';
                                        } elseif ($offer->vendor_status == 'test_completed') {
                                            $statusClass = 'status-test-completed';
                                            $statusText = app()->getLocale() === 'ar' ? 'تم إكمال الفحص' : 'Test Completed';
                                        }
                                    @endphp
                                    <span class="status-badge {{ $statusClass }}" id="status-badge-{{ $offer->id }}">
                                        {{ $statusText }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-slate-600">{{ $offer->updated_at->format('Y-m-d H:i') }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button type="button" class="text-primary-600 hover:text-primary-900" data-bs-toggle="modal" data-bs-target="#offerModal{{ $offer->id }}">
                                        {{ app()->getLocale() === 'ar' ? 'إدارة' : 'Manage' }}
                                    </button>
                                </td>
                            </tr>

                            <!-- Offer Management Modal -->
                            <div class="modal fade" id="offerModal{{ $offer->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">{{ app()->getLocale() === 'ar' ? 'إدارة العرض' : 'Manage Offer' }} #{{ $offer->id }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <!-- Vendor Status Section -->
                                            <div class="mb-4">
                                                <label class="form-label fw-bold">{{ app()->getLocale() === 'ar' ? 'حالة المزود' : 'Vendor Status' }}</label>
                                                <div class="d-flex gap-2 mb-3">
                                                    <button type="button" class="btn btn-warning btn-sm update-status-btn" 
                                                            data-offer-id="{{ $offer->id }}" 
                                                            data-status="sample_collected"
                                                            {{ $offer->vendor_status == 'sample_collected' || $offer->vendor_status == 'test_completed' ? 'disabled' : '' }}>
                                                        {{ app()->getLocale() === 'ar' ? 'تم جمع العينة' : 'Sample Collected' }}
                                                    </button>
                                                    <button type="button" class="btn btn-success btn-sm update-status-btn" 
                                                            data-offer-id="{{ $offer->id }}" 
                                                            data-status="test_completed"
                                                            {{ $offer->vendor_status != 'sample_collected' ? 'disabled' : '' }}>
                                                        {{ app()->getLocale() === 'ar' ? 'تم إكمال الفحص' : 'Test Completed' }}
                                                    </button>
                                                </div>
                                            </div>

                                            <hr>

                                            <!-- Attachments Section -->
                                            <div class="mb-4">
                                                <label class="form-label fw-bold">{{ app()->getLocale() === 'ar' ? 'مرفقات نتائج الفحص' : 'Test Result Attachments' }}</label>
                                                
                                                <!-- Upload Form -->
                                                <form class="mb-3 upload-attachment-form" data-offer-id="{{ $offer->id }}">
                                                    <div class="input-group">
                                                        <input type="file" class="form-control" name="file" accept="image/*,application/pdf" required>
                                                        <button type="submit" class="btn btn-primary">
                                                            {{ app()->getLocale() === 'ar' ? 'رفع' : 'Upload' }}
                                                        </button>
                                                    </div>
                                                </form>

                                                <!-- Attachments List -->
                                                <div id="attachments-list-{{ $offer->id }}">
                                                    @if($offer->attachments->count() > 0)
                                                        <div class="list-group">
                                                            @foreach($offer->attachments as $attachment)
                                                                <div class="list-group-item d-flex justify-content-between align-items-center" data-attachment-id="{{ $attachment->id }}">
                                                                    <div class="d-flex align-items-center gap-2">
                                                                        @if($attachment->isImage())
                                                                            <i class="bi bi-image text-primary"></i>
                                                                        @elseif($attachment->isPdf())
                                                                            <i class="bi bi-file-pdf text-danger"></i>
                                                                        @else
                                                                            <i class="bi bi-file text-secondary"></i>
                                                                        @endif
                                                                        <span>{{ $attachment->file_name }}</span>
                                                                        <button type="button" class="btn btn-sm btn-link text-primary preview-attachment" 
                                                                                data-url="{{ $attachment->url }}" 
                                                                                data-mime="{{ $attachment->mime_type }}">
                                                                            {{ app()->getLocale() === 'ar' ? 'معاينة' : 'Preview' }}
                                                                        </button>
                                                                    </div>
                                                                    <button type="button" class="btn btn-sm btn-danger delete-attachment" 
                                                                            data-offer-id="{{ $offer->id }}" 
                                                                            data-attachment-id="{{ $attachment->id }}">
                                                                        {{ app()->getLocale() === 'ar' ? 'حذف' : 'Delete' }}
                                                                    </button>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <p class="text-muted">{{ app()->getLocale() === 'ar' ? 'لا توجد مرفقات' : 'No attachments' }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ app()->getLocale() === 'ar' ? 'إغلاق' : 'Close' }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $offers->links() }}
                </div>
            @else
                <div class="px-6 py-12 text-center">
                    <p class="text-slate-500">{{ app()->getLocale() === 'ar' ? 'لا توجد عروض مقبولة' : 'No accepted offers found' }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ app()->getLocale() === 'ar' ? 'معاينة الملف' : 'File Preview' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div id="preview-content"></div>
                </div>
                <div class="modal-footer">
                    <a id="preview-download" href="#" target="_blank" class="btn btn-primary">{{ app()->getLocale() === 'ar' ? 'تحميل' : 'Download' }}</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ app()->getLocale() === 'ar' ? 'إغلاق' : 'Close' }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Setup CSRF token
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Update vendor status
            $('.update-status-btn').on('click', function() {
                const $btn = $(this);
                const offerId = $btn.data('offer-id');
                const status = $btn.data('status');
                const $badge = $('#status-badge-' + offerId);

                $btn.prop('disabled', true);

                $.ajax({
                    url: `/laboratory/offers/${offerId}/vendor-status`,
                    method: 'PUT',
                    data: { vendor_status: status },
                    success: function(response) {
                        if (response.success) {
                            // Update badge
                            if (status === 'sample_collected') {
                                $badge.removeClass('status-empty status-test-completed').addClass('status-sample-collected')
                                    .text('{{ app()->getLocale() === "ar" ? "تم جمع العينة" : "Sample Collected" }}');
                                // Enable test completed button
                                $btn.siblings('[data-status="test_completed"]').prop('disabled', false);
                            } else if (status === 'test_completed') {
                                $badge.removeClass('status-empty status-sample-collected').addClass('status-test-completed')
                                    .text('{{ app()->getLocale() === "ar" ? "تم إكمال الفحص" : "Test Completed" }}');
                            }
                            
                            // Disable sample collected button
                            $btn.siblings('[data-status="sample_collected"]').prop('disabled', true);
                        }
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || '{{ app()->getLocale() === "ar" ? "حدث خطأ" : "An error occurred" }}');
                        $btn.prop('disabled', false);
                    }
                });
            });

            // Upload attachment
            $('.upload-attachment-form').on('submit', function(e) {
                e.preventDefault();
                const $form = $(this);
                const offerId = $form.data('offer-id');
                const formData = new FormData($form[0]);

                $.ajax({
                    url: `/laboratory/offers/${offerId}/attachments`,
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            // Add attachment to list
                            const attachment = response.attachment;
                            const icon = attachment.is_image ? '<i class="bi bi-image text-primary"></i>' : 
                                        attachment.is_pdf ? '<i class="bi bi-file-pdf text-danger"></i>' : 
                                        '<i class="bi bi-file text-secondary"></i>';
                            
                            const attachmentHtml = `
                                <div class="list-group-item d-flex justify-content-between align-items-center" data-attachment-id="${attachment.id}">
                                    <div class="d-flex align-items-center gap-2">
                                        ${icon}
                                        <span>${attachment.file_name}</span>
                                        <button type="button" class="btn btn-sm btn-link text-primary preview-attachment" 
                                                data-url="${attachment.url}" 
                                                data-mime="${attachment.mime_type}">
                                            {{ app()->getLocale() === 'ar' ? 'معاينة' : 'Preview' }}
                                        </button>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger delete-attachment" 
                                            data-offer-id="${offerId}" 
                                            data-attachment-id="${attachment.id}">
                                        {{ app()->getLocale() === 'ar' ? 'حذف' : 'Delete' }}
                                    </button>
                                </div>
                            `;
                            
                            const $list = $('#attachments-list-' + offerId);
                            if ($list.find('.list-group').length === 0) {
                                $list.html('<div class="list-group"></div>');
                            }
                            $list.find('.list-group').append(attachmentHtml);
                            $form[0].reset();
                        }
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || '{{ app()->getLocale() === "ar" ? "حدث خطأ أثناء الرفع" : "Upload error occurred" }}');
                    }
                });
            });

            // Delete attachment
            $(document).on('click', '.delete-attachment', function() {
                if (!confirm('{{ app()->getLocale() === "ar" ? "هل أنت متأكد من حذف هذا الملف؟" : "Are you sure you want to delete this file?" }}')) {
                    return;
                }

                const $btn = $(this);
                const offerId = $btn.data('offer-id');
                const attachmentId = $btn.data('attachment-id');

                $.ajax({
                    url: `/laboratory/offers/${offerId}/attachments/${attachmentId}`,
                    method: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            $(`.list-group-item[data-attachment-id="${attachmentId}"]`).remove();
                            const $list = $('#attachments-list-' + offerId);
                            if ($list.find('.list-group-item').length === 0) {
                                $list.html('<p class="text-muted">{{ app()->getLocale() === "ar" ? "لا توجد مرفقات" : "No attachments" }}</p>');
                            }
                        }
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || '{{ app()->getLocale() === "ar" ? "حدث خطأ أثناء الحذف" : "Delete error occurred" }}');
                    }
                });
            });

            // Preview attachment
            $(document).on('click', '.preview-attachment', function() {
                const url = $(this).data('url');
                const mime = $(this).data('mime');
                const $previewContent = $('#preview-content');
                const $downloadLink = $('#preview-download');

                $downloadLink.attr('href', url);

                if (mime && mime.startsWith('image/')) {
                    $previewContent.html(`<img src="${url}" class="img-fluid" alt="Preview">`);
                } else if (mime === 'application/pdf') {
                    $previewContent.html(`<iframe src="${url}" width="100%" height="600px"></iframe>`);
                } else {
                    $previewContent.html(`<p class="text-muted">{{ app()->getLocale() === "ar" ? "معاينة غير متاحة. يرجى تحميل الملف." : "Preview not available. Please download the file." }}</p>`);
                }

                const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
                previewModal.show();
            });
        });
    </script>
@endpush
