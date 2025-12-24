@extends('admin.layouts.admin')

@section('title', 'Medical Test Offers')
@section('page-title', app()->getLocale() === 'ar' ? 'عروض الفحوصات' : 'Medical Test Offers')

@section('content')
<div class=" rounded-lg shadow overflow-hidden">
    <div class="p-6">
        <div class="mb-4 flex items-center justify-end" >
            <x-admin.ui.button color="green" href="{{ route('admin.medical-test-offers.create') }}"   icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>'>
                {{ app()->getLocale() === 'ar' ? 'إضافة عرض' : 'Add Offer' }}
            </x-admin.ui.button>
        </div>
        <table id="medical-test-offers-table" class="display nowrap" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>{{ app()->getLocale() === 'ar' ? 'المعمل' : 'Laboratory' }}</th>
                    <th>{{ app()->getLocale() === 'ar' ? 'الفحص' : 'Test' }}</th>
                    <th>{{ app()->getLocale() === 'ar' ? 'السعر' : 'Price' }}</th>
                    <th>{{ app()->getLocale() === 'ar' ? 'سعر العرض' : 'Offer Price' }}</th>
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
        $('#medical-test-offers-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.medical-test-offers.data') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'laboratory_name', name: 'laboratory.name' },
                { data: 'test_name', name: 'medicalTest.test_name_en' },
                { data: 'price_formatted', name: 'price' },
                { data: 'offer_price_formatted', name: 'offer_price' },
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

