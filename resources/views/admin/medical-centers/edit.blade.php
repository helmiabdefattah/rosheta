@extends('admin.layouts.admin')

@php $l = app()->getLocale() === 'ar'; @endphp

@section('title', 'Edit Medical Center')
@section('page-title', $l ? 'تعديل مركز طبي' : 'Edit Medical Center')
@section('page-description', $center->name)

@section('content')
    <form method="POST" action="{{ route('admin.medical-centers.update', $center) }}">
        @csrf
        @method('PUT')
        @include('admin.medical-centers._form')
    </form>
@endsection
