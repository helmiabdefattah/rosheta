@extends('laboratories.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'العروض المقبولة' : 'Accepted Offers')

@section('page-description', app()->getLocale() === 'ar' ? 'عرض جميع العروض المقبولة من العملاء' : 'View all offers accepted by clients')

@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
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
                                    <span class="text-sm text-slate-600">{{ $offer->updated_at->format('Y-m-d H:i') }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ app()->getLocale() === 'ar' ? 'مقبول' : 'Accepted' }}
                                    </span>
                                </td>
                            </tr>
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
@endsection

