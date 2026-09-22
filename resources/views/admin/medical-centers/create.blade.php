@extends('admin.layouts.admin')

@php $l = app()->getLocale() === 'ar'; @endphp

@section('title', 'Add Medical Center')
@section('page-title', $l ? 'إضافة مركز طبي' : 'Add Medical Center')
@section('page-description', $l ? 'أنشئ مركزًا وأضف عياداته' : 'Create a center and add its clinics')

@section('content')
    <form method="POST" action="{{ route('admin.medical-centers.store') }}">
        @csrf
        @include('admin.medical-centers._form')
    </form>
@endsection
