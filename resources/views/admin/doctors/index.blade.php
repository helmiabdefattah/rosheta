@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'الأطباء' : 'Doctors')
@section('page-title', app()->getLocale() === 'ar' ? 'الأطباء' : 'Doctors')
@section('page-description', app()->getLocale() === 'ar' ? 'إدارة الأطباء' : 'Manage doctors')

@section('header-actions')
    <x-admin.ui.button href="{{ route('admin.doctors.create') }}" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>'>
        {{ app()->getLocale() === 'ar' ? 'إضافة طبيب' : 'Add Doctor' }}
    </x-admin.ui.button>
@endsection

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="p-6">
        <table id="doctors-table" class="display nowrap w-full" style="width:100%">
            <thead>
                <tr>
                    <th class="text-start">ID</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'الصورة' : 'Image' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</th>
                    <th class="text-start">Slug</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'التخصص' : 'Specialization' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'الحساب' : 'User' }}</th>
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
        $('#doctors-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.doctors.data') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'image', name: 'image', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'slug', name: 'slug' },
                { data: 'specialization_name', name: 'specialization_name', orderable: false, searchable: false },
                { data: 'user_email', name: 'user_email', orderable: false, searchable: false },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            order: [[0, 'desc']],
            language: { @if(app()->getLocale() === 'ar') url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json' @endif }
        });
    });
</script>
@endpush
