@extends(request()->boolean('embed') ? 'clinic.layouts.embed' : 'clinic.layouts.app')

@section('content')
    @unless(request()->boolean('embed'))
        @include('clinic.dental-lab.partials._tabs', ['active' => 'orders'])
    @endunless

    <h2 class="font-bold text-slate-800 mb-4">{{ __('app.dental.order.edit') }} · {{ __('app.dental.order.number', ['id' => $order->id]) }}</h2>

    @include('clinic.dental-lab.orders._form', ['action' => route('practice.dental-lab.orders.update', $order)])
@endsection
