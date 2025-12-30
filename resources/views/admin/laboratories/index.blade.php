@extends('admin.layouts.admin')

@section('title', 'Laboratories')
@section('page-title', app()->getLocale() === 'ar' ? 'المعامل' : 'Laboratories')
@section('page-description', app()->getLocale() === 'ar' ? 'إدارة المعامل' : 'Manage Laboratories')

@section('header-actions')
    <x-admin.ui.button href="{{ route('admin.laboratories.create') }}" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>'>
        {{ app()->getLocale() === 'ar' ? 'إضافة معمل' : 'Add Laboratory' }}
    </x-admin.ui.button>
@endsection

@section('content')
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition-shadow duration-300 p-6">

        <!-- Toggle Buttons -->
        <div class="mb-4 flex gap-2">
            <button class="filter-btn px-4 py-2 rounded-lg bg-primary text-white" data-type="all">
                {{ app()->getLocale() === 'ar' ? 'الكل' : 'All' }}
            </button>
            <button class="filter-btn px-4 py-2 rounded-lg bg-gray-200 text-gray-700" data-type="test">
                {{ app()->getLocale() === 'ar' ? 'معامل التحاليل' : 'medical test' }}
            </button>
            <button class="filter-btn px-4 py-2 rounded-lg bg-gray-200 text-gray-700" data-type="radiology">
                {{ app()->getLocale() === 'ar' ? 'معامل الاشعة' : 'radiology' }}
            </button>
        </div>

        <table id="laboratories-table" class="display nowrap w-full" style="width:100%">
            <thead>
            <tr>
                <th>{{ app()->getLocale() === 'ar' ? 'ID' : 'ID' }}</th>
                <th>{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</th>
                <th>{{ app()->getLocale() === 'ar' ? 'المستخدم' : 'User' }}</th>
                <th>{{ app()->getLocale() === 'ar' ? 'المنطقة' : 'Area' }}</th>
                <th>{{ app()->getLocale() === 'ar' ? 'المدينة' : 'City' }}</th>
                <th>{{ app()->getLocale() === 'ar' ? 'المحافظة' : 'Governorate' }}</th>
                <th>{{ app()->getLocale() === 'ar' ? 'الحالة' : 'Status' }}</th>
                <th>{{ app()->getLocale() === 'ar' ? 'الإجراءات' : 'Actions' }}</th>
            </tr>
            </thead>
        </table>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let currentType = 'all'; // default

            const table = $('#laboratories-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.laboratories.data') }}",
                    data: function(d) {
                        d.type = currentType; // send current filter type to server
                    }
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'user_name', name: 'user.name' },
                    { data: 'area_name', name: 'area.name' },
                    { data: 'city_name', name: 'area.city.name' },
                    { data: 'governorate_name', name: 'area.city.governorate.name' },
                    { data: 'is_active', name: 'is_active', render: function(data) {
                            return data
                                ? '<span class="text-green-600 font-semibold">' + ( "{{ app()->getLocale() === 'ar' ? 'نشطة' : 'Active' }}" ) + '</span>'
                                : '<span class="text-red-600 font-semibold">' + ( "{{ app()->getLocale() === 'ar' ? 'غير نشطة' : 'Inactive' }}" ) + '</span>';
                        }},
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[0, 'desc']],
                language: {
                    @if(app()->getLocale() === 'ar')
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json'
                    @endif
                }
            });

            // Toggle filter buttons
            $('.filter-btn').click(function() {
                currentType = $(this).data('type');

                // Update button styles
                $('.filter-btn').removeClass('bg-primary text-white').addClass('bg-gray-200 text-gray-700');
                $(this).removeClass('bg-gray-200 text-gray-700').addClass('bg-primary text-white');

                table.ajax.reload();
            });
        });
    </script>
@endpush
