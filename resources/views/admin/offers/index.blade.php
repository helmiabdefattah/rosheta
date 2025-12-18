@extends('admin.layouts.admin')

@section('title', 'Offers')
@section('page-title', app()->getLocale() === 'ar' ? 'العروض' : 'Offers')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition-shadow duration-300">
    <div class="p-6">
        <table id="offers-table" class="display nowrap w-full" style="width:100%">
            <thead>
                <tr>
                    <th class="text-start">ID</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'الطلب' : 'Request' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'العميل' : 'Client' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'المزود' : 'Provider' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'المبلغ' : 'Total' }}</th>
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
        $('#offers-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.offers.data') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'request_id', name: 'request.id' },
                { data: 'client_name', name: 'client_name' },
                { data: 'provider_name', name: 'provider_name' },
                { data: 'status_badge', name: 'status' },
                { data: 'total_price_formatted', name: 'total_price' },
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

