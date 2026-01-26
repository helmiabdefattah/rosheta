@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'حجز المواعيد' : 'Appointments')
@section('page-title', app()->getLocale() === 'ar' ? 'حجز المواعيد' : 'Appointments')
@section('page-description', app()->getLocale() === 'ar' ? 'إدارة المواعيد' : 'Manage appointments')

@section('header-actions')
    <x-admin.ui.button href="{{ route('admin.appointments.create') }}" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>'>
        {{ app()->getLocale() === 'ar' ? 'حجز موعد جديد' : 'Book Appointment' }}
    </x-admin.ui.button>
@endsection

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="p-6">
        <table id="appointments-table" class="display nowrap w-full" style="width:100%">
            <thead>
                <tr>
                    <th class="text-start">ID</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'التاريخ' : 'Date' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'الوقت' : 'Time' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'الطبيب' : 'Doctor' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'العيادة' : 'Clinic' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'المريض' : 'Patient' }}</th>
                    <th class="text-start">{{ app()->getLocale() === 'ar' ? 'النوع' : 'Type' }}</th>
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
        $('#appointments-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.appointments.data') }}",
            columns: [
                { data: 'id', name: 'id' },
                { data: 'appointment_date', name: 'appointment_date' },
                { data: 'appointment_time', name: 'appointment_time' },
                { data: 'doctor_name', name: 'doctor_name', orderable: false, searchable: false },
                { data: 'clinic_name', name: 'clinic_name', orderable: false, searchable: false },
                { data: 'patient_name', name: 'patient_name', orderable: false, searchable: false },
                { data: 'type_label', name: 'type_label', orderable: false, searchable: false },
                { data: 'status', name: 'status' },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            order: [[1, 'desc'], [2, 'asc']],
            language: { @if(app()->getLocale() === 'ar') url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/ar.json' @endif }
        });
    });
</script>
@endpush
