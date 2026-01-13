@extends('laboratories.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'الاستفسارات' : 'Quotes')

@section('page-description', app()->getLocale() === 'ar' ? 'عرض جميع الاستفسارات المرسلة من العملاء' : 'View all quotes sent by clients')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="bg-white rounded-lg shadow-sm p-6">
            <h2 class="text-2xl font-bold text-slate-800 mb-2">
                {{ app()->getLocale() === 'ar' ? 'الاستفسارات' : 'Quotes' }}
            </h2>
            <p class="text-slate-600">
                {{ app()->getLocale() === 'ar' 
                    ? 'عرض وإدارة جميع الاستفسارات المرسلة من العملاء' 
                    : 'View and manage all quotes sent by clients' }}
            </p>
        </div>

        {{-- Quotes List --}}
        @if($quotes->count() > 0)
            <div class="space-y-4">
                @foreach($quotes as $quote)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden" id="quote-{{ $quote->id }}">
                        <div class="p-6">
                            {{-- Quote Header --}}
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                                            <svg class="w-5 h-5 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-semibold text-slate-800">
                                                {{ $quote->client->name ?? 'N/A' }}
                                            </h3>
                                            <p class="text-sm text-slate-500">
                                                {{ $quote->created_at->format('Y-m-d H:i') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                @if($quote->reply)
                                    <span class="px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                        {{ app()->getLocale() === 'ar' ? 'تم الرد' : 'Replied' }}
                                    </span>
                                @else
                                    <span class="px-3 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">
                                        {{ app()->getLocale() === 'ar' ? 'في انتظار الرد' : 'Pending Reply' }}
                                    </span>
                                @endif
                            </div>

                            {{-- Quote Content --}}
                            <div class="mb-4">
                                <p class="text-sm font-medium text-slate-600 mb-2">
                                    {{ app()->getLocale() === 'ar' ? 'الاستفسار:' : 'Quote:' }}
                                </p>
                                <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                                    <p class="text-slate-800 whitespace-pre-wrap">{{ $quote->quote }}</p>
                                </div>
                            </div>

                            {{-- Reply Section --}}
                            @if($quote->reply)
                                <div class="mb-4">
                                    <p class="text-sm font-medium text-slate-600 mb-2">
                                        {{ app()->getLocale() === 'ar' ? 'الرد:' : 'Reply:' }}
                                    </p>
                                    <div class="bg-primary-50 rounded-lg p-4 border border-primary-200">
                                        <p class="text-slate-800 whitespace-pre-wrap">{{ $quote->reply }}</p>
                                        <p class="text-xs text-slate-500 mt-2">
                                            {{ app()->getLocale() === 'ar' ? 'تم الرد في:' : 'Replied at:' }} 
                                            {{ $quote->updated_at->format('Y-m-d H:i') }}
                                        </p>
                                    </div>
                                </div>
                                <button 
                                    type="button"
                                    class="text-primary-600 hover:text-primary-700 text-sm font-medium edit-reply-btn"
                                    data-quote-id="{{ $quote->id }}"
                                    data-reply="{{ $quote->reply }}">
                                    {{ app()->getLocale() === 'ar' ? 'تعديل الرد' : 'Edit Reply' }}
                                </button>
                            @else
                                <button 
                                    type="button"
                                    class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-teal-700 transition-colors reply-btn"
                                    data-quote-id="{{ $quote->id }}">
                                    {{ app()->getLocale() === 'ar' ? 'إضافة رد' : 'Add Reply' }}
                                </button>
                            @endif

                            {{-- Reply Form (Hidden by default) --}}
                            <div class="mt-4 hidden reply-form" id="reply-form-{{ $quote->id }}">
                                <form class="quote-reply-form" data-quote-id="{{ $quote->id }}">
                                    <div class="mb-3">
                                        <label for="reply-{{ $quote->id }}" class="block text-sm font-medium text-slate-700 mb-2">
                                            {{ app()->getLocale() === 'ar' ? 'الرد' : 'Reply' }}
                                            <span class="text-red-500">*</span>
                                        </label>
                                        <textarea 
                                            id="reply-{{ $quote->id }}"
                                            name="reply" 
                                            rows="4" 
                                            class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-primary focus:border-primary"
                                            placeholder="{{ app()->getLocale() === 'ar' ? 'اكتب ردك هنا...' : 'Write your reply here...' }}"
                                            required>{{ $quote->reply ?? '' }}</textarea>
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ app()->getLocale() === 'ar' ? 'الحد الأقصى 5000 حرف' : 'Maximum 5000 characters' }}
                                        </p>
                                    </div>
                                    <div id="error-{{ $quote->id }}" class="mb-3 hidden">
                                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                                            <p class="error-message"></p>
                                        </div>
                                    </div>
                                    <div id="success-{{ $quote->id }}" class="mb-3 hidden">
                                        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
                                            <p class="success-message"></p>
                                        </div>
                                    </div>
                                    <div class="flex gap-3">
                                        <button 
                                            type="submit" 
                                            class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-teal-700 transition-colors">
                                            {{ app()->getLocale() === 'ar' ? 'إرسال الرد' : 'Send Reply' }}
                                        </button>
                                        <button 
                                            type="button" 
                                            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors cancel-reply-btn"
                                            data-quote-id="{{ $quote->id }}">
                                            {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $quotes->links() }}
            </div>
        @else
            <div class="bg-white rounded-lg shadow-sm p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
                <p class="text-gray-500 text-lg">
                    {{ app()->getLocale() === 'ar' ? 'لا توجد استفسارات حالياً' : 'No quotes available at the moment' }}
                </p>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const isArabic = {{ app()->getLocale() === 'ar' ? 'true' : 'false' }};

            // Show reply form when "Add Reply" or "Edit Reply" is clicked
            $(document).on('click', '.reply-btn, .edit-reply-btn', function() {
                const quoteId = $(this).data('quote-id');
                const replyForm = $('#reply-form-' + quoteId);
                const replyTextarea = $('#reply-' + quoteId);
                
                // If editing, set the reply text
                if ($(this).hasClass('edit-reply-btn')) {
                    const reply = $(this).data('reply');
                    replyTextarea.val(reply);
                }
                
                replyForm.removeClass('hidden');
                replyTextarea.focus();
            });

            // Hide reply form when "Cancel" is clicked
            $(document).on('click', '.cancel-reply-btn', function() {
                const quoteId = $(this).data('quote-id');
                const replyForm = $('#reply-form-' + quoteId);
                const replyTextarea = $('#reply-' + quoteId);
                
                replyForm.addClass('hidden');
                replyTextarea.val('');
                
                // Hide error/success messages
                $('#error-' + quoteId).addClass('hidden');
                $('#success-' + quoteId).addClass('hidden');
            });

            // Handle form submission
            $(document).on('submit', '.quote-reply-form', function(e) {
                e.preventDefault();
                
                const form = $(this);
                const quoteId = form.data('quote-id');
                const formData = new FormData(this);
                const submitBtn = form.find('button[type="submit"]');
                const originalText = submitBtn.text();
                
                // Hide previous messages
                $('#error-' + quoteId).addClass('hidden');
                $('#success-' + quoteId).addClass('hidden');
                
                // Disable submit button
                submitBtn.prop('disabled', true);
                submitBtn.text(isArabic ? 'جاري الإرسال...' : 'Sending...');

                fetch(`/laboratory/quotes/${quoteId}`, {
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        reply: formData.get('reply')
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        $('#success-' + quoteId + ' .success-message').text(data.message);
                        $('#success-' + quoteId).removeClass('hidden');
                        
                        // Reload page after 1.5 seconds to show updated reply
                        setTimeout(() => {
                            location.reload();
                        }, 1500);
                    } else {
                        // Show error message
                        $('#error-' + quoteId + ' .error-message').text(data.message || (isArabic ? 'حدث خطأ أثناء الإرسال' : 'An error occurred while sending'));
                        $('#error-' + quoteId).removeClass('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    $('#error-' + quoteId + ' .error-message').text(isArabic ? 'حدث خطأ أثناء الإرسال' : 'An error occurred while sending');
                    $('#error-' + quoteId).removeClass('hidden');
                })
                .finally(() => {
                    // Re-enable submit button
                    submitBtn.prop('disabled', false);
                    submitBtn.text(originalText);
                });
            });
        });
    </script>
    @endpush
@endsection
