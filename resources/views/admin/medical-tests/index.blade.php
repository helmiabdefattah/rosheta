@extends('admin.layouts.admin')

@section('title', 'Tests & Radiology')
@section('page-title', app()->getLocale() === 'ar' ? 'الفحوصات الطبية والأشعة' : 'Medical & Radiology Tests')
@section('page-description', app()->getLocale() === 'ar' ? 'إدارة الفحوصات الطبية والأشعة' : 'Manage Medical & Radiology Tests')

@section('header-actions')
    <x-admin.ui.button href="{{ route('admin.medical-tests.create') }}" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>'>
        {{ app()->getLocale() === 'ar' ? 'إضافة فحص أو أشعة' : 'Add Test or Radiology' }}
    </x-admin.ui.button>
@endsection

@section('content')
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition-shadow duration-300 p-6">

        <!-- Toggle Buttons -->
        <div class="mb-4 flex gap-2">
            <button class="filter-btn px-4 py-2 rounded-lg bg-primary text-white" data-type="test">
                {{ app()->getLocale() === 'ar' ? 'الفحوصات الطبية' : 'Medical Tests' }}
            </button>
            <button class="filter-btn px-4 py-2 rounded-lg bg-gray-200 text-gray-700" data-type="radiology">
                {{ app()->getLocale() === 'ar' ? 'الأشعة' : 'Radiology' }}
            </button>
        </div>

        <table id="medical-tests-table" class="display nowrap w-full" style="width:100%">
            <thead>
            <tr>
                <th>{{ app()->getLocale() === 'ar' ? 'ID' : 'ID' }}</th>
                <th>{{ app()->getLocale() === 'ar' ? 'الاسم (إنجليزي)' : 'Name (English)' }}</th>
                <th>{{ app()->getLocale() === 'ar' ? 'الاسم (عربي)' : 'Name (Arabic)' }}</th>
                <th>{{ app()->getLocale() === 'ar' ? 'الوصف' : 'Description' }}</th>
                <th>{{ app()->getLocale() === 'ar' ? 'الإجراءات' : 'Actions' }}</th>
            </tr>
            </thead>
        </table>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            let currentType = 'test'; // default

            const table = $('#medical-tests-table').DataTable({
                processing: true,
                serverSide: true,
                    ajax: {
                        url: "{{ route('admin.medical-tests.data') }}",
                        data: function(d) {
                            d.type = currentType; // send current filter type
                        }

                },
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

            // Toggle buttons
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
