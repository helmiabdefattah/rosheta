@if($offersByRequest->count() > 0)
    <div class="space-y-6">
        @foreach($offersByRequest as $item)
            @php
                $request = $item['request'];
                $offers = $item['offers'];
            @endphp

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <!-- Request Header -->
                <div class="mb-4 pb-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">
                                {{ app()->getLocale() === 'ar' ? 'طلب' : 'Request' }} #{{ $request->id }}
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ app()->getLocale() === 'ar' ? 'نوع الطلب:' : 'Request Type:' }}
                              @if($request->type != "radiology")
                                <span class="font-medium">
                                    {{ $request->type === 'test'
                                        ? (app()->getLocale() === 'ar' ? 'تحاليل طبية' : 'Medical Tests')
                                        : (app()->getLocale() === 'ar' ? 'أدوية' : 'Medicines') }}
                                </span>
                                @else
                                <span class="font-medium">
                                    {{ $request->type === 'radiology'
                                        ? (app()->getLocale() === 'ar' ? 'أشعة' : 'radiology')
                                        : (app()->getLocale() === 'ar' ? 'أدوية' : 'Medicines') }}
                                </span>
                                @endif
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $request->created_at->format('Y-m-d H:i') }}
                            </p>
                        </div>
                        <span class="status-badge status-{{ $request->status }}">
                            {{ ucfirst($request->status) }}
                        </span>
                    </div>
                </div>

                <!-- Offers List -->
                <div class="space-y-4">
                    @foreach($offers as $offer)
                        <div class="offer-card border border-gray-200 rounded-lg p-4 hover:border-primary transition-all">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <h4 class="font-semibold text-slate-900">
                                            {{ app()->getLocale() === 'ar' ? 'عرض' : 'Offer' }} #{{ $offer->id }}
                                        </h4>
                                        <span class="status-badge status-{{ $offer->status }}">
                                            {{ ucfirst($offer->status) }}
                                        </span>
                                        @if($offer->vendor_status)
                                            <span class="text-xs text-gray-500">
                                                ({{ ucfirst(str_replace('_', ' ', $offer->vendor_status)) }})
                                            </span>
                                        @endif
                                    </div>

                                    <div class="text-sm text-gray-700 mb-2">
                                        <p>
                                            <span class="font-medium">
                                                {{ app()->getLocale() === 'ar' ? 'المزود:' : 'Provider:' }}
                                            </span>
                                            {{ $offer->request_type === 'test'
                                                ? ($offer->laboratory->name ?? 'N/A')
                                                : ($offer->pharmacy->name ?? 'N/A') }}
                                        </p>
                                        @if($offer->request_type === 'test' && $offer->laboratory && $offer->laboratory->phone)
                                            <p class="text-xs text-gray-500">
                                                <i class="bi bi-telephone me-1"></i>
                                                {{ $offer->laboratory->phone }}
                                            </p>
                                        @elseif($offer->pharmacy && $offer->pharmacy->phone)
                                            <p class="text-xs text-gray-500">
                                                <i class="bi bi-telephone me-1"></i>
                                                {{ $offer->pharmacy->phone }}
                                            </p>
                                        @endif
                                        @if($request->client_address_id)
                                            <div class="mt-2">
                                                @if(is_null($offer->visit_price))
                                                    <span class="status-badge status-rejected">
                {{ app()->getLocale() === 'ar'
                    ? 'المعمل لا يوفّر زيارة منزلية'
                    : 'Home visit not available' }}
            </span>
                                                @elseif($offer->visit_price == 0)
                                                    <span class="status-badge status-accepted">
                {{ app()->getLocale() === 'ar'
                    ? 'زيارة منزلية مجانية'
                    : 'Free home visit' }}
            </span>
                                                @else
                                                    <span class="status-badge status-pending">
                {{ app()->getLocale() === 'ar'
                    ? 'زيارة منزلية بسعر ' . $offer->visit_price . ' ج.م'
                    : 'Home visit: ' . $offer->visit_price . ' EGP' }}
            </span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Offer Lines -->
                                    @if($offer->lines && $offer->lines->count() > 0)
                                        <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                                            <p class="text-xs font-medium text-gray-700 mb-2">
                                                {{ app()->getLocale() === 'ar' ? 'العناصر:' : 'Items:' }}
                                            </p>
                                            <ul class="space-y-1">
                                                @foreach($offer->lines as $line)
                                                    <li class="text-sm text-gray-600">
                                                        @if($line->item_type === 'test' ||$line->item_type === 'radiology')
                                                            • {{ $line->medicalTest->test_name_en ?? 'N/A' }}
                                                            @if($line->medicalTest && $line->medicalTest->test_name_ar)
                                                                ({{ $line->medicalTest->test_name_ar }})
                                                            @endif
                                                            -
                                                            <span class="font-medium">{{ number_format($line->price ?? 0, 2) }} {{ app()->getLocale() === 'ar' ? 'ج.م' : 'EGP' }}</span>
                                                        @else
                                                            • {{ $line->medicine->name ?? 'N/A' }}
                                                            ({{ $line->quantity }} {{ $line->unit ?? '' }})
                                                            -
                                                            <span class="font-medium">{{ number_format($line->price ?? 0, 2) }} {{ app()->getLocale() === 'ar' ? 'ج.م' : 'EGP' }}</span>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </div>

                                <div class="text-right ml-4">
                                    <div class="text-2xl font-bold text-primary mb-1">
                                        {{ number_format($offer->total_price ?? 0, 2) }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ app()->getLocale() === 'ar' ? 'ج.م' : 'EGP' }}
                                    </div>
                                </div>
                            </div>

                            <!-- Offer Actions -->
                            @if($offer->status === 'pending')
                                <div class="flex items-center gap-3 pt-3 border-t border-gray-200 mt-3">
                                    <form action="{{ route('client.offers.accept', $offer) }}" method="POST" class="flex-1" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من قبول هذا العرض؟' : 'Are you sure you want to accept this offer?' }}');">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200 text-sm font-medium">
                                            <i class="bi bi-check-circle me-1"></i>
                                            {{ app()->getLocale() === 'ar' ? 'قبول العرض' : 'Accept Offer' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('client.offers.reject', $offer) }}" method="POST" class="flex-1" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من رفض هذا العرض؟' : 'Are you sure you want to reject this offer?' }}');">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200 text-sm font-medium">
                                            <i class="bi bi-x-circle me-1"></i>
                                            {{ app()->getLocale() === 'ar' ? 'رفض العرض' : 'Reject Offer' }}
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
@else
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
        <i class="bi bi-inbox text-gray-400 text-6xl mb-4"></i>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">
            {{ app()->getLocale() === 'ar' ? 'لا توجد عروض' : 'No Offers' }}
        </h3>
        <p class="text-gray-500">
            {{ app()->getLocale() === 'ar'
                ? 'لم تتلق أي عروض على طلباتك بعد.'
                : 'You haven\'t received any offers on your requests yet.' }}
        </p>
    </div>
@endif

