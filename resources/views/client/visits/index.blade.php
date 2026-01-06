@extends('client.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'لوحة التحكم' : 'Dashboard')
@section('page-title', app()->getLocale() === 'ar' ? 'لوحة التحكم' : 'Dashboard')
@section('page-description', app()->getLocale() === 'ar' ? 'نظرة عامة على طلباتك وطلباتك' : 'Overview of your requests and orders')

@section('content')
    <div class="max-w-7xl mx-auto space-y-8">

        <!-- Statistics -->

        <!-- Visits Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
            <div class="p-6 border-b">
                <h3 class="text-lg font-semibold text-slate-900">
                    {{ app()->getLocale() === 'ar' ? 'زياراتك' : 'Your Visits' }}
                </h3>
            </div>

            @if($visits->count())
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3  text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                        <th class="px-4 py-3  text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'طلب التمريض المنزلي' : 'Request' }}</th>
                        <th class="px-4 py-3  text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'تاريخ الزيارة' : 'Visit Date' }}</th>
                        <th class="px-4 py-3  text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'ملاحظات' : 'Notes' }}</th>
                        <th class="px-4 py-3  text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                        <th class="px-4 py-3  text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'تقييم' : 'Rating' }}</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($visits as $visit)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $visit->id }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">#{{ $visit->home_nurse_request_id }} - {{ $visit->nurse?->user?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                @if($visit->visit_datetime)
                                    <div class="font-medium">{{ $visit->visit_datetime->format('Y-m-d') }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ $visit->visit_start_time ?? ($visit->offer?->visit_start_time ? \Illuminate\Support\Str::limit($visit->offer->visit_start_time, 5, '') : $visit->visit_datetime->format('H:i')) }}
                                        @if(!empty($visit->offer?->visit_duration))
                                            ({{ $visit->offer->visit_duration }}h)
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-400">
                                        {{ $visit->visit_start_time ?? $visit->offer?->visit_start_time ?? $visit->request?->visit_time ?? '-' }}
                                        @if(!empty($visit->offer?->visit_duration))
                                            ({{ $visit->offer->visit_duration }}h)
                                        @endif
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $visit->notes ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                @if($visit->status === 'completed')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">{{ app()->getLocale() === 'ar' ? 'مكتمل' : 'Completed' }}</span>
                                @elseif($visit->status === 'scheduled')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">{{ app()->getLocale() === 'ar' ? 'مجدول' : 'Scheduled' }}</span>
                                @elseif($visit->status === 'cancelled')
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">{{ app()->getLocale() === 'ar' ? 'ملغى' : 'Cancelled' }}</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ ucfirst($visit->status) }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                @php
                                    $existingReview = $visit->review ?? null;
                                @endphp

                                @if($existingReview)
                                    <div class="text-xs text-gray-600">
                                        <span class="inline-flex items-center">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-4 h-4 {{ $i <= (int)$existingReview->rating ? 'text-yellow-400' : 'text-gray-300' }}" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.802 2.036a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.802-2.036a1 1 0 00-1.175 0l-2.802 2.036c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @endfor
                                        </span>
                                        @if(!empty($existingReview->comment))
                                            <br>
                                            "{{ $existingReview->comment }}"
                                        @endif
                                    </div>
                                @else
                                    <form method="POST" action="{{ route('client.visits.rate', $visit) }}" class="space-y-2">
                                        @csrf
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">{{ __('Rating') }}</label>
                                            <div class="flex items-center gap-1">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <label class="cursor-pointer">
                                                        <input type="radio" name="rating" value="{{ $i }}" class="sr-only star-input" required>
                                                        <svg class="w-5 h-5 star-icon text-gray-300" viewBox="0 0 20 20" fill="currentColor">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.802 2.036a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.802-2.036a1 1 0 00-1.175 0l-2.802 2.036c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                        </svg>
                                                    </label>
                                                @endfor
                                            </div>
                                        </div>

                                        <!-- Hidden comment input populated via modal -->
                                        <input type="hidden" name="comment" id="comment-input-{{ $visit->id }}">
                                        <div class="flex items-center justify-between gap-2">
                                            <button type="button"
                                                    class="px-3 py-1.5 rounded border text-xs hover:bg-gray-50"
                                                    data-open-comment="comment-modal-{{ $visit->id }}">
                                                {{ app()->getLocale() === 'ar' ? 'إضافة تعليق' : 'Add comment' }}
                                            </button>
                                            <button type="submit"
                                                    class="px-3 py-1.5 rounded-md bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs shadow">
                                                {{ __('Submit') }}
                                            </button>
                                        </div>
                                    </form>

                                    <!-- Comment Modal -->
                                    <div id="comment-modal-{{ $visit->id }}" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
                                        <div class="bg-white rounded-lg shadow max-w-md w-full mx-4">
                                            <div class="flex items-center justify-between p-3 border-b">
                                                <h5 class="font-semibold text-slate-800 text-sm">{{ app()->getLocale() === 'ar' ? 'إضافة تعليق' : 'Add Comment' }}</h5>
                                                <button type="button" class="text-slate-500 hover:text-slate-700" data-close-comment="comment-modal-{{ $visit->id }}">×</button>
                                            </div>
                                            <div class="p-3">
                                                <textarea id="comment-text-{{ $visit->id }}" class="w-full border rounded-md p-2 text-sm" rows="4" placeholder="{{ __('Optional') }}"></textarea>
                                            </div>
                                            <div class="p-3 border-t flex justify-end gap-2">
                                                <button type="button" class="px-3 py-1.5 rounded border text-xs" data-close-comment="comment-modal-{{ $visit->id }}">
                                                    {{ app()->getLocale() === 'ar' ? 'إلغاء' : 'Cancel' }}
                                                </button>
                                                <button type="button"
                                                        class="px-3 py-1.5 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-xs"
                                                        data-save-comment="comment-modal-{{ $visit->id }}"
                                                        data-target-input="comment-input-{{ $visit->id }}"
                                                        data-target-textarea="comment-text-{{ $visit->id }}">
                                                    {{ app()->getLocale() === 'ar' ? 'حفظ' : 'Save' }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div class="px-4 py-3 border-t">
                    {{ $visits->links() }}
                </div>
            @else
                <div class="p-6 text-center text-gray-500">
                    {{ app()->getLocale() === 'ar' ? 'لا توجد زيارات' : 'No visits found' }}
                </div>
            @endif
        </div>

    </div>
@endsection

@push('scripts')
<script>
    // Simple star highlighting for chosen rating
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('star-input')) {
            const wrapper = e.target.closest('td');
            if (!wrapper) return;
            const stars = wrapper.querySelectorAll('.star-input + .star-icon');
            const value = parseInt(e.target.value || '0', 10);
            stars.forEach((svg, idx) => {
                svg.classList.toggle('text-yellow-400', idx < value);
                svg.classList.toggle('text-gray-300', idx >= value);
            });
        }
    });

    // Comment modal open/close/save handlers
    document.addEventListener('click', function(e) {
        const openBtn = e.target.closest('[data-open-comment]');
        if (openBtn) {
            const id = openBtn.getAttribute('data-open-comment');
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
            return;
        }
        const closeBtn = e.target.closest('[data-close-comment]');
        if (closeBtn) {
            const id = closeBtn.getAttribute('data-close-comment');
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
            return;
        }
        const saveBtn = e.target.closest('[data-save-comment]');
        if (saveBtn) {
            const modalId = saveBtn.getAttribute('data-save-comment');
            const inputId = saveBtn.getAttribute('data-target-input');
            const textareaId = saveBtn.getAttribute('data-target-textarea');
            const input = document.getElementById(inputId);
            const textarea = document.getElementById(textareaId);
            if (input && textarea) {
                input.value = textarea.value;
            }
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
            return;
        }
        // backdrop click to close
        if (e.target.classList.contains('bg-black/50')) {
            e.target.classList.add('hidden');
            e.target.classList.remove('flex');
        }
    }, false);
</script>
@endpush
