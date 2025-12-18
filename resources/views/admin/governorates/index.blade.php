@extends('admin.layouts.admin')

@section('title', 'Governorates')
@section('page-title', app()->getLocale() === 'ar' ? 'المحافظات' : 'Governorates')
@section('page-description', app()->getLocale() === 'ar' ? 'إدارة المحافظات' : 'Manage Governorates')

@section('header-actions')
    <x-admin.ui.button href="{{ route('admin.governorates.create') }}" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>'>
        {{ app()->getLocale() === 'ar' ? 'إضافة محافظة' : 'Add Governorate' }}
    </x-admin.ui.button>
@endsection

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition-shadow duration-300">
    <div class="p-6">
        <table id="governorates-table" class="display nowrap w-full" style="width:100%">
            <thead>
                <tr>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'ID' : 'ID' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'الاسم (إنجليزي)' : 'Name (English)' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'الاسم (عربي)' : 'Name (Arabic)' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'عدد المدن' : 'Cities' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'ترتيب' : 'Sort Order' }}</th>
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
        $('#governorates-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.governorates.data') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'name_ar', name: 'name_ar' },
                { data: 'cities_count', name: 'cities_count', orderable: false, searchable: false },
                { data: 'sort_order', name: 'sort_order' },
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

