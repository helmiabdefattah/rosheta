@extends('client.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'العروض' : 'My Offers')

@section('page-title', app()->getLocale() === 'ar' ? 'عروض على طلباتي' : 'Offers for My Requests')
@section('page-description', app()->getLocale() === 'ar' ? 'عروض من الصيدليات والمعامل على طلباتك' : 'Offers from pharmacies and laboratories for your requests')

@push('styles')
<style>
    .offer-card {
        transition: all 0.3s ease;
    }
    .offer-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    .status-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }
    .status-pending {
        background-color: #fef3c7;
        color: #92400e;
    }
    .status-accepted {
        background-color: #d1fae5;
        color: #065f46;
    }
    .status-rejected {
        background-color: #fee2e2;
        color: #991b1b;
    }
    .refresh-indicator {
        display: inline-block;
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">
                {{ app()->getLocale() === 'ar' ? 'عروض على طلباتي' : 'Offers for My Requests' }}
            </h2>
            <p class="text-sm text-gray-600 mt-1">
                {{ app()->getLocale() === 'ar'
                    ? 'سيتم تحديث القائمة تلقائياً كل 5 ثوانٍ'
                    : 'List will auto-refresh every 5 seconds' }}
            </p>
        </div>
        <div class="flex items-center gap-3">
            <span id="lastRefresh" class="text-xs text-gray-500"></span>
            <span id="refreshIndicator" class="refresh-indicator hidden">
                <i class="bi bi-arrow-clockwise text-primary"></i>
            </span>
        </div>
    </div>
    <div class="flex gap-2 mb-4">
        <button class="filter-btn px-4 py-2 rounded {{ $defaultType === 'all' ? 'bg-primary text-white' : 'bg-gray-100' }}" data-type="all">
            {{ __('All') }}
        </button>
        <button class="filter-btn px-4 py-2 rounded {{ $defaultType === 'test' ? 'bg-primary text-white' : 'bg-gray-100' }}" data-type="test">
            {{ __('Tests') }}
        </button>
        <button class="filter-btn px-4 py-2 rounded {{ $defaultType === 'radiology' ? 'bg-primary text-white' : 'bg-gray-100' }}" data-type="radiology">
            {{ __('Radiology') }}
        </button>
        <button class="filter-btn px-4 py-2 rounded {{ $defaultType === 'medicine' ? 'bg-primary text-white' : 'bg-gray-100' }}" data-type="medicine">
            {{ __('Medicine') }}
        </button>
    </div>
    <div id="offersContainer">
        @include('client.offers.partials.offers-list', ['offersByRequest' => $offersByRequest])
    </div>
</div>
@endsection

@push('scripts')
    <script>
        // Wait for jQuery to be available
        if (typeof jQuery === 'undefined') {
            console.error('jQuery is not loaded');
        } else {
            (function($) {
                'use strict';

                let refreshInterval;
                const refreshIntervalMs = 5000; // 5 seconds
                let currentFilterType = '{{ $defaultType }}';

                function updateLastRefreshTime() {
                    const now = new Date();
                    const timeStr = now.toLocaleTimeString();
                    $('#lastRefresh').text('{{ app()->getLocale() === "ar" ? "آخر تحديث:" : "Last refresh:" }} ' + timeStr);
                }

                function refreshOffers() {
                    $('#refreshIndicator').removeClass('hidden');

                    // Pass current filter type in AJAX request
                    $.ajax({
                        url: '{{ route("client.offers.index") }}',
                        method: 'GET',
                        data: {
                            filter_type: currentFilterType
                        },
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        },
                        success: function(html) {
                            // If it's an AJAX request from filter, we'll get the partial
                            // If it's a full page, we need to extract the container
                            if (html.includes('offersContainer')) {
                                // Full page HTML response
                                const $temp = $('<div>').html(html);
                                const $newContent = $temp.find('#offersContainer').html();
                                if ($newContent) {
                                    $('#offersContainer').html($newContent);
                                }
                            } else {
                                // Partial HTML response
                                $('#offersContainer').html(html);
                            }
                            updateLastRefreshTime();

                            // Reinitialize filters after content is loaded
                            initializeFilters();
                        },
                        error: function(xhr, status, error) {
                            console.error('Failed to refresh offers:', error);
                        },
                        complete: function() {
                            $('#refreshIndicator').addClass('hidden');
                        }
                    });
                }

                function initializeFilters() {
                    // Remove existing event listeners to prevent duplicates
                    $('.filter-btn').off('click');

                    // Add new event listeners
                    $('.filter-btn').on('click', function() {
                        const type = $(this).data('type');
                        filterOffers(type);
                    });
                }

                function filterOffers(type) {
                    currentFilterType = type; // Store current filter type

                    // Update active filter button
                    $('.filter-btn').removeClass('bg-primary text-white').addClass('bg-gray-100');
                    $('.filter-btn[data-type="'+type+'"]').removeClass('bg-gray-100').addClass('bg-primary text-white');

                    // For immediate UI feedback, filter client-side
                    $('.offer-card').each(function() {
                        const offerType = $(this).data('type');
                        if (type === 'all' || offerType === type) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });

                    // Then refresh from server with the new filter
                    refreshOffers();
                }

                $(document).ready(function() {
                    // Initial setup
                    updateLastRefreshTime();
                    initializeFilters();

                    // Apply initial filter (client-side only on first load)
                    const defaultType = '{{ $defaultType }}';
                    filterOffers(defaultType);

                    // Start auto-refresh
                    refreshInterval = setInterval(refreshOffers, refreshIntervalMs);

                    // Refresh on page visibility change (when user comes back to tab)
                    document.addEventListener('visibilitychange', function() {
                        if (!document.hidden) {
                            refreshOffers();
                        }
                    });
                });

                // Cleanup on page unload
                $(window).on('beforeunload', function() {
                    if (refreshInterval) {
                        clearInterval(refreshInterval);
                    }
                });
            })(jQuery);
        }
    </script>
@endpush

