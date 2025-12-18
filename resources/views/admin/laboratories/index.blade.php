@extends('admin.layouts.admin')

@section('title', 'Laboratories')
@section('page-title', app()->getLocale() === 'ar' ? 'المعامل' : 'Laboratories')
@section('page-description', app()->getLocale() === 'ar' ? 'إدارة المعامل' : 'Manage Laboratories')

@section('header-actions')
    <x-admin.ui.button href="{{ route('admin.laboratories.create') }}" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>'>
        {{ app()->getLocale() === 'ar' ? 'إضافة معمل' : 'Add Laboratory' }}
    </x-admin.ui.button>
@endsection

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition-shadow duration-300">
    <div class="p-6">
        <table id="laboratories-table" class="display nowrap w-full" style="width:100%">
            <thead>
                <tr>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'ID' : 'ID' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'المستخدم' : 'User' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'المنطقة' : 'Area' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'المدينة' : 'City' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'المحافظة' : 'Governorate' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
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
        $('#laboratories-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.laboratories.data') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'user_name', name: 'user.name' },
                { data: 'area_name', name: 'area.name' },
                { data: 'city_name', name: 'area.city.name' },
                { data: 'governorate_name', name: 'area.city.governorate.name' },
                { data: 'is_active', name: 'is_active' },
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

