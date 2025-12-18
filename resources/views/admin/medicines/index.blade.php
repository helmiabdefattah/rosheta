@extends('admin.layouts.admin')

@section('title', 'Medicines')
@section('page-title', app()->getLocale() === 'ar' ? 'الأدوية' : 'Medicines')
@section('page-description', app()->getLocale() === 'ar' ? 'إدارة الأدوية' : 'Manage Medicines')

@section('header-actions')
    <x-admin.ui.button href="{{ route('admin.medicines.create') }}" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>'>
        {{ app()->getLocale() === 'ar' ? 'إضافة دواء' : 'Add Medicine' }}
    </x-admin.ui.button>
@endsection

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="p-6">
        <table id="medicines-table" class="display nowrap w-full" style="width:100%">
            <thead>
                <tr>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'ID' : 'ID' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'الاسم العربي' : 'Arabic Name' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'السعر' : 'Price' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'الشركة' : 'Company' }}</th>
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
        $('#medicines-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.medicines.data') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'arabic', name: 'arabic' },
                { data: 'price_formatted', name: 'price' },
                { data: 'company', name: 'company' },
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

