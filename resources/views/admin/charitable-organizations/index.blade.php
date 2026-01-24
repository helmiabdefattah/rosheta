@extends('admin.layouts.admin')

@section('title', 'Charitable Organizations')
@section('page-title', app()->getLocale() === 'ar' ? 'المنظمات الخيرية' : 'Charitable Organizations')
@section('page-description', app()->getLocale() === 'ar' ? 'إدارة المنظمات الخيرية' : 'Manage Charitable Organizations')

@section('header-actions')
    <x-admin.ui.button href="{{ route('admin.charitable-organizations.create') }}" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>'>
        {{ app()->getLocale() === 'ar' ? 'إضافة منظمة خيرية' : 'Add Charitable Organization' }}
    </x-admin.ui.button>
@endsection

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition-shadow duration-300">
    <div class="p-6">
        <table id="charitable-organizations-table" class="display nowrap w-full" style="width:100%">
            <thead>
                <tr>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'ID' : 'ID' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'الموقع' : 'Location' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'العنوان' : 'Address' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'أرقام الهاتف' : 'Phone Numbers' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'الخدمات' : 'Services' }}</th>
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
        $('#charitable-organizations-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.charitable-organizations.data') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'location', name: 'location', orderable: false, searchable: false },
                { data: 'address', name: 'address' },
                { data: 'phone_numbers_display', name: 'phone_numbers_display', orderable: false, searchable: false },
                { data: 'services_display', name: 'services_display', orderable: false, searchable: false },
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
