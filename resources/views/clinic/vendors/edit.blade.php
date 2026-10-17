@extends('clinic.layouts.app')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-slate-900">✏️ {{ $vendor->name }}</h1>
    <a href="{{ route('practice.doctor.vendors.index') }}" class="btn btn-secondary btn-sm">← {{ __('app.vendors.title') }}</a>
</div>

<form method="POST" action="{{ route('practice.doctor.vendors.update', $vendor) }}" class="card p-5 space-y-3 max-w-xl">
    @csrf @method('PUT')
    @include('clinic.vendors.partials.fields', ['vendor' => $vendor, 'types' => $types])
    <div class="flex items-center gap-2 pt-2">
        <button class="btn btn-primary">{{ __('app.expenses.f.save') }}</button>
        <a href="{{ route('practice.doctor.vendors.statement', $vendor) }}" class="btn btn-secondary">{{ __('app.vendors.statement') }}</a>
    </div>
</form>

<form method="POST" action="{{ route('practice.doctor.vendors.destroy', $vendor) }}" class="mt-4 max-w-xl"
      onsubmit="return confirm('{{ __('app.dental.actions.confirm_delete') }}')">
    @csrf @method('DELETE')
    <button class="btn btn-danger btn-sm">🗑 {{ __('app.vendors.delete') }}</button>
</form>
@endsection
