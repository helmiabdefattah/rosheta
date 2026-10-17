@extends('clinic.layouts.app')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-slate-900">✏️ {{ $template->title }}</h1>
    <a href="{{ route('practice.doctor.recurring.index') }}" class="btn btn-secondary btn-sm">← {{ __('app.recurring.title') }}</a>
</div>

<form method="POST" action="{{ route('practice.doctor.recurring.update', $template) }}" class="card p-5 space-y-3 max-w-xl">
    @csrf @method('PUT')
    @include('clinic.recurring.partials.fields', ['template' => $template])
    <button class="btn btn-primary">{{ __('app.expenses.f.save') }}</button>
</form>

<form method="POST" action="{{ route('practice.doctor.recurring.destroy', $template) }}" class="mt-4 max-w-xl"
      onsubmit="return confirm('{{ __('app.dental.actions.confirm_delete') }}')">
    @csrf @method('DELETE')
    <button class="btn btn-danger btn-sm">🗑 {{ __('app.expenses.f.delete') }}</button>
</form>
@endsection
