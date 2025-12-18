@extends('admin.layouts.admin')

@section('title', 'Clients')
@section('page-title', app()->getLocale() === 'ar' ? 'العملاء' : 'Clients')
@section('page-description', app()->getLocale() === 'ar' ? 'إدارة العملاء' : 'Manage Clients')

@section('header-actions')
    <x-admin.ui.button href="{{ route('admin.clients.create') }}" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>'>
        {{ app()->getLocale() === 'ar' ? 'إضافة عميل' : 'Add Client' }}
    </x-admin.ui.button>
@endsection

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition-shadow duration-300">
    <div class="p-6">
        <table id="clients-table" class="display nowrap w-full" style="width:100%">
            <thead>
                <tr>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'ID' : 'ID' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'رقم الهاتف' : 'Phone' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'العناوين' : 'Addresses' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'المدن' : 'Cities' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'المناطق' : 'Areas' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#clients-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.clients.data') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'phone_number', name: 'phone_number' },
                { data: 'email', name: 'email' },
                { data: 'addresses_list', name: 'addresses_list', orderable: false, searchable: false },
                { data: 'cities_list', name: 'cities_list', orderable: false, searchable: false },
                { data: 'areas_list', name: 'areas_list', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            order: [[0, 'desc']],
            language: {
                @if(app()->getLocale() === 'ar')
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json'
                @endif
            }
        });
    });
</script>
@endpush

