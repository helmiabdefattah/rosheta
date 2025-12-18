@extends('admin.layouts.admin')

@section('title', 'Users')
@section('page-title', app()->getLocale() === 'ar' ? 'المستخدمون' : 'Users')
@section('page-description', app()->getLocale() === 'ar' ? 'إدارة المستخدمين' : 'Manage Users')

@section('header-actions')
    <x-admin.ui.button href="{{ route('admin.users.create') }}" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>'>
        {{ app()->getLocale() === 'ar' ? 'إضافة مستخدم' : 'Add User' }}
    </x-admin.ui.button>
@endsection

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="p-6">
        <table id="users-table" class="display nowrap w-full" style="width:100%">
            <thead>
                <tr>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'ID' : 'ID' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'الصيدلية' : 'Pharmacy' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'المعمل' : 'Laboratory' }}</th>
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
        $('#users-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.users.data') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'email', name: 'email' },
                { data: 'pharmacy_name', name: 'pharmacy.name' },
                { data: 'laboratory_name', name: 'laboratory.name' },
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

