@extends('laboratories.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'إدارة المستخدمين' : 'Manage Users')

@section('page-description', app()->getLocale() === 'ar' ? 'إدارة مستخدمي المعمل' : 'Manage laboratory users')

@push('styles')
    <link href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css" rel="stylesheet" />
@endpush

@section('content')
    <div class="bg-white rounded-lg shadow-sm mb-6">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-slate-800">{{ app()->getLocale() === 'ar' ? 'مستخدمي المعمل' : 'Laboratory Users' }}</h2>
            <a href="{{ route('laboratories.users.create') }}" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                {{ app()->getLocale() === 'ar' ? 'إضافة مستخدم' : 'Add User' }}
            </a>
        </div>
        <div class="p-6">
            <table id="users-table" class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email' }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'تاريخ الإنشاء' : 'Created At' }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'الإجراءات' : 'Actions' }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('laboratories.users.data') }}',
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[2, 'desc']],
                language: {
                    @if(app()->getLocale() === 'ar')
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json'
                    @endif
                }
            });
        });
    </script>
@endpush

