@extends('admin.layouts.admin')

@section('title', 'Medical Tests')
@section('page-title', app()->getLocale() === 'ar' ? 'الفحوصات الطبية' : 'Medical Tests')
@section('page-description', app()->getLocale() === 'ar' ? 'إدارة الفحوصات الطبية' : 'Manage Medical Tests')

@section('header-actions')
    <x-admin.ui.button href="{{ route('admin.medical-tests.create') }}" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>'>
        {{ app()->getLocale() === 'ar' ? 'إضافة فحص طبي' : 'Add Medical Test' }}
    </x-admin.ui.button>
@endsection

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition-shadow duration-300">
    <div class="p-6">
        <table id="medical-tests-table" class="display nowrap w-full" style="width:100%">
            <thead>
                <tr>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'ID' : 'ID' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'الاسم (إنجليزي)' : 'Name (English)' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'الاسم (عربي)' : 'Name (Arabic)' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'الوصف' : 'Description' }}</th>
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
        $('#medical-tests-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.medical-tests.data') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'test_name_en', name: 'test_name_en' },
                { data: 'test_name_ar', name: 'test_name_ar' },
                { data: 'test_description', name: 'test_description' },
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

