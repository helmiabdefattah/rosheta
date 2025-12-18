@extends('admin.layouts.admin')

@section('title', 'View Client')
@section('page-title', app()->getLocale() === 'ar' ? 'عرض العميل' : 'View Client')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">{{ app()->getLocale() === 'ar' ? 'معلومات العميل' : 'Client Information' }}</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-slate-600">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</p>
                <p class="text-lg font-medium text-slate-900">{{ $client->name }}</p>
            </div>
            <div>
                <p class="text-sm text-slate-600">{{ app()->getLocale() === 'ar' ? 'رقم الهاتف' : 'Phone' }}</p>
                <p class="text-lg font-medium text-slate-900">{{ $client->phone_number }}</p>
            </div>
            <div>
                <p class="text-sm text-slate-600">{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email' }}</p>
                <p class="text-lg font-medium text-slate-900">{{ $client->email ?? '-' }}</p>
            </div>
        </div>
    </div>

    @if($client->addresses->count() > 0)
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">{{ app()->getLocale() === 'ar' ? 'العناوين' : 'Addresses' }}</h3>
        <div class="space-y-4">
            @foreach($client->addresses as $address)
                <div class="p-4 bg-slate-50 rounded-lg">
                    <p class="font-medium text-slate-900">{{ $address->address }}</p>
                    <p class="text-sm text-slate-600">{{ $address->area->name ?? '' }}, {{ $address->city->name ?? '' }}</p>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($client->requests->count() > 0)
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">{{ app()->getLocale() === 'ar' ? 'الطلبات' : 'Requests' }}</h3>
        <div class="space-y-2">
            @foreach($client->requests as $request)
                <div class="p-3 bg-slate-50 rounded-lg">
                    <p class="font-medium text-slate-900">{{ app()->getLocale() === 'ar' ? 'طلب' : 'Request' }} #{{ $request->id }}</p>
                    <p class="text-sm text-slate-600">{{ $request->type }} - {{ $request->status }}</p>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="flex gap-4">
        <a href="{{ route('admin.clients.edit', $client) }}" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-600 transition-colors">
            {{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}
        </a>
        <a href="{{ route('admin.clients.index') }}" class="px-4 py-2 text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200 transition-colors">
            {{ app()->getLocale() === 'ar' ? 'رجوع' : 'Back' }}
        </a>
    </div>
</div>
@endsection

