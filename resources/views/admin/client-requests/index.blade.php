@extends('admin.layouts.admin')

@section('title', 'Client Requests')
@section('page-title', app()->getLocale() === 'ar' ? 'طلبات العملاء' : 'Client Requests')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition-shadow duration-300">
    <div class="p-6">
        <table id="client-requests-table" class="display nowrap w-full" style="width:100%">
            <thead>
                <tr>
                    <th class="text-start">ID</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'العميل' : 'Client' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'العنوان' : 'Address' }}</th>
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
        $('#client-requests-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.client-requests.data') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'client_name', name: 'client.name' },
                { data: 'address_text', name: 'address_text' },
                { data: 'status_badge', name: 'status' },
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

