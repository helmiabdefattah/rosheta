@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'العيادات' : 'Clinics')
@section('page-title', app()->getLocale() === 'ar' ? 'العيادات' : 'Clinics')
@section('page-description', app()->getLocale() === 'ar' ? 'إدارة العيادات' : 'Manage clinics')

@section('header-actions')
    <x-admin.ui.button href="{{ route('admin.clinics.create') }}" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>'>
        {{ app()->getLocale() === 'ar' ? 'إضافة عيادة' : 'Add Clinic' }}
    </x-admin.ui.button>
@endsection

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="p-6">
        <table id="clinics-table" class="display nowrap w-full" style="width:100%">
            <thead>
                <tr>
                    <th class="text-start">ID</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'الطبيب' : 'Doctor' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'التخصص' : 'Specialization' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'الهاتف' : 'Phone' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'الموقع' : 'Location' }}</th>
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
        $('#clinics-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.clinics.data') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'doctor_name', name: 'doctor_name', orderable: false, searchable: false },
                { data: 'specialization', name: 'specialization', orderable: false, searchable: false },
                { data: 'phone_number', name: 'phone_number', orderable: false, searchable: true },
                { data: 'location', name: 'location', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            order: [[0, 'desc']],
            language: { @if(app()->getLocale() === 'ar') url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json' @endif }
        });
    });
</script>
@endpush
