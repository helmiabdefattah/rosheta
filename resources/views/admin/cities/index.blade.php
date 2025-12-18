@extends('admin.layouts.admin')

@section('title', 'Cities')
@section('page-title', app()->getLocale() === 'ar' ? 'المدن' : 'Cities')
@section('page-description', app()->getLocale() === 'ar' ? 'إدارة المدن' : 'Manage Cities')

@section('header-actions')
    <a href="{{ route('admin.cities.create') }}" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-blue-600 transition-all">
        <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        {{ app()->getLocale() === 'ar' ? 'إضافة مدينة' : 'Add City' }}
    </a>
@endsection

@section('content')
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-6">
        <table id="cities-table" class="display nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>{{ app()->getLocale() === 'ar' ? 'ID' : 'ID' }}</th>
                    <th>{{ app()->getLocale() === 'ar' ? 'الاسم (إنجليزي)' : 'Name (English)' }}</th>
                    <th>{{ app()->getLocale() === 'ar' ? 'الاسم (عربي)' : 'Name (Arabic)' }}</th>
                    <th>{{ app()->getLocale() === 'ar' ? 'المحافظة' : 'Governorate' }}</th>
                    <th>{{ app()->getLocale() === 'ar' ? 'عدد المناطق' : 'Areas' }}</th>
                    <th>{{ app()->getLocale() === 'ar' ? 'ترتيب' : 'Sort Order' }}</th>
                    <th>{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                    <th>{{ app()->getLocale() === 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#cities-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.cities.data') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'name_ar', name: 'name_ar' },
                { data: 'governorate_name', name: 'governorate.name' },
                { data: 'areas_count', name: 'areas_count', orderable: false, searchable: false },
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

