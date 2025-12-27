@extends('client.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'عناويني' : 'My Addresses')

@section('page-title', app()->getLocale() === 'ar' ? 'عناويني' : 'My Addresses')
@section('page-description', app()->getLocale() === 'ar' ? 'إدارة عناوين التوصيل' : 'Manage your delivery addresses')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-900">
            {{ app()->getLocale() === 'ar' ? 'عناويني' : 'My Addresses' }}
        </h2>
        <a href="{{ route('client.addresses.create') }}" class="px-6 py-3 bg-primary text-white rounded-lg hover:bg-teal-700 transition duration-200 font-medium">
            <i class="bi bi-plus-circle me-2"></i>
            {{ app()->getLocale() === 'ar' ? 'إضافة عنوان جديد' : 'Add New Address' }}
        </a>
    </div>

    @if($addresses->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($addresses as $address)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-2">
                                <i class="bi bi-geo-alt-fill text-primary text-xl"></i>
                                <h3 class="text-lg font-semibold text-gray-800">
                                    {{ app()->getLocale() === 'ar' ? 'العنوان' : 'Address' }} #{{ $address->id }}
                                </h3>
                            </div>
                            <p class="text-gray-700 mb-2">{{ $address->address }}</p>
                            <div class="text-sm text-gray-600">
                                <p>
                                    <span class="font-medium">{{ app()->getLocale() === 'ar' ? 'المدينة:' : 'City:' }}</span>
                                    {{ $address->city->name ?? 'N/A' }}
                                </p>
                                <p>
                                    <span class="font-medium">{{ app()->getLocale() === 'ar' ? 'المنطقة:' : 'Area:' }}</span>
                                    {{ $address->area->name ?? 'N/A' }}
                                </p>
                                @if($address->location && isset($address->location['lat']) && isset($address->location['lng']))
                                    <p class="text-xs text-gray-500 mt-2">
                                        <i class="bi bi-geo-alt"></i>
                                        {{ number_format($address->location['lat'], 6) }}, {{ number_format($address->location['lng'], 6) }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 pt-4 border-t border-gray-200">
                        <a href="{{ route('client.addresses.edit', $address) }}" class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition duration-200 text-center text-sm font-medium">
                            <i class="bi bi-pencil me-1"></i>
                            {{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}
                        </a>
                        <form action="{{ route('client.addresses.destroy', $address) }}" method="POST" class="flex-1" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من حذف هذا العنوان؟' : 'Are you sure you want to delete this address?' }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-full px-4 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition duration-200 text-sm font-medium">
                                <i class="bi bi-trash me-1"></i>
                                {{ app()->getLocale() === 'ar' ? 'حذف' : 'Delete' }}
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
            <i class="bi bi-geo-alt text-gray-400 text-6xl mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">
                {{ app()->getLocale() === 'ar' ? 'لا توجد عناوين' : 'No Addresses' }}
            </h3>
            <p class="text-gray-500 mb-6">
                {{ app()->getLocale() === 'ar' 
                    ? 'لم تقم بإضافة أي عناوين بعد. أضف عنوانًا جديدًا للبدء.' 
                    : 'You haven\'t added any addresses yet. Add a new address to get started.' }}
            </p>
            <a href="{{ route('client.addresses.create') }}" class="inline-flex items-center px-6 py-3 bg-primary text-white rounded-lg hover:bg-teal-700 transition duration-200 font-medium">
                <i class="bi bi-plus-circle me-2"></i>
                {{ app()->getLocale() === 'ar' ? 'إضافة عنوان جديد' : 'Add New Address' }}
            </a>
        </div>
    @endif
</div>
@endsection

