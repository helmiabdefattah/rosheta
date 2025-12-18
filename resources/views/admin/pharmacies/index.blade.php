@extends('admin.layouts.admin')

@section('title', 'Pharmacies')
@section('page-title', app()->getLocale() === 'ar' ? 'الصيدليات' : 'Pharmacies')

@section('header-actions')
    <x-admin.ui.button href="{{ route('admin.pharmacies.create') }}" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>'>
        {{ app()->getLocale() === 'ar' ? 'إضافة صيدلية' : 'Add Pharmacy' }}
    </x-admin.ui.button>
@endsection

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition-shadow duration-300">
    <div class="p-6">
        <table id="pharmacies-table" class="display nowrap w-full" style="width:100%">
            <thead>
                <tr>
                   <th class="text-start">ID</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'الهاتف' : 'Phone' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'المنطقة' : 'Area' }}</th>
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
        $('#pharmacies-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.pharmacies.data') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'phone', name: 'phone' },
                { data: 'area_name', name: 'area.name' },
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

