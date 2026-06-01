@extends('admin.layouts.admin')

@php
    $l = app()->getLocale() === 'ar';
    $type = $type ?? 'all';
@endphp

@section('title', $l ? 'المعامل' : 'Laboratories')
@section('page-title', $l ? 'المعامل' : 'Laboratories')
@section('page-description', $l ? 'إدارة المعامل' : 'Manage laboratories')

@section('header-actions')
    <x-admin.ui.button href="{{ route('admin.laboratories.create') }}" icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>'>
        {{ $l ? 'إضافة معمل' : 'Add Laboratory' }}
    </x-admin.ui.button>
@endsection

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 max-lg:overflow-visible lg:overflow-hidden hover:shadow-md transition-shadow duration-300">
    <div class="p-4 sm:p-6 border-b border-slate-100 space-y-4">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.laboratories.index', array_filter(['search' => request('search')])) }}"
               class="px-4 py-2 rounded-lg text-sm font-medium {{ $type === 'all' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">{{ $l ? 'الكل' : 'All' }}</a>
            <a href="{{ route('admin.laboratories.index', array_filter(['type' => 'test', 'search' => request('search')])) }}"
               class="px-4 py-2 rounded-lg text-sm font-medium {{ $type === 'test' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">{{ $l ? 'معامل التحاليل' : 'Tests' }}</a>
            <a href="{{ route('admin.laboratories.index', array_filter(['type' => 'radiology', 'search' => request('search')])) }}"
               class="px-4 py-2 rounded-lg text-sm font-medium {{ $type === 'radiology' ? 'bg-primary text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">{{ $l ? 'معامل الأشعة' : 'Radiology' }}</a>
        </div>
        <form method="GET" action="{{ route('admin.laboratories.index') }}" class="flex flex-col sm:flex-row gap-3 sm:items-end sm:justify-between">
            @if($type !== 'all')
                <input type="hidden" name="type" value="{{ $type }}">
            @endif
            <div class="flex-1 w-full min-w-0">
                <label for="labs-search" class="block text-xs font-medium text-slate-500 mb-1">{{ $l ? 'بحث' : 'Search' }}</label>
                <input type="search" name="search" id="labs-search" value="{{ request('search') }}"
                       placeholder="{{ $l ? 'المعرف، الاسم، المستخدم، المنطقة…' : 'ID, name, user, area…' }}"
                       class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-primary/30 focus:border-primary">
            </div>
            <div class="flex gap-2 w-full sm:w-auto">
                <button type="submit" class="flex-1 sm:flex-none px-4 py-2 bg-primary text-white rounded-lg text-sm font-medium hover:opacity-90">{{ $l ? 'بحث' : 'Search' }}</button>
                @if(request()->filled('search'))
                    <a href="{{ route('admin.laboratories.index', $type === 'all' ? [] : ['type' => $type]) }}" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-lg text-sm text-center">{{ $l ? 'مسح' : 'Clear' }}</a>
                @endif
            </div>
        </form>
    </div>

    @if($laboratories->count() === 0)
        <div class="p-10 text-center text-slate-500">{{ $l ? 'لا توجد معامل.' : 'No laboratories found.' }}</div>
    @else
        <div class="lg:hidden space-y-3 p-4">
            @foreach($laboratories as $laboratory)
                @php
                    $areaLabel = '—';
                    if ($laboratory->area) {
                        $areaLabel = $l
                            ? ($laboratory->area->name_ar ?? $laboratory->area->name ?? '—')
                            : ($laboratory->area->name ?? $laboratory->area->name_ar ?? '—');
                    }
                    $cityLabel = $laboratory->area?->city
                        ? ($l ? ($laboratory->area->city->name_ar ?? $laboratory->area->city->name) : ($laboratory->area->city->name ?? $laboratory->area->city->name_ar))
                        : '—';
                    $govLabel = $laboratory->area?->city?->governorate
                        ? ($l ? ($laboratory->area->city->governorate->name_ar ?? $laboratory->area->city->governorate->name) : ($laboratory->area->city->governorate->name ?? $laboratory->area->city->governorate->name_ar))
                        : '—';
                    $typeLabel = $laboratory->type === 'radiology' ? ($l ? 'أشعة' : 'Radiology') : ($l ? 'تحاليل' : 'Tests');
                @endphp
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="mb-3 flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">#{{ $laboratory->id }}</p>
                            <p class="text-lg font-bold text-slate-900">{{ $laboratory->name }}</p>
                        </div>
                        <span class="shrink-0 px-2 py-0.5 text-xs font-semibold rounded-full bg-slate-100 text-slate-700">{{ $typeLabel }}</span>
                    </div>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500 shrink-0">{{ $l ? 'المستخدم' : 'User' }}</dt>
                            <dd class="text-slate-800 text-end text-xs break-all">{{ $laboratory->user->name ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500 shrink-0">{{ $l ? 'المنطقة' : 'Area' }}</dt>
                            <dd class="text-slate-800 text-end">{{ $areaLabel }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500 shrink-0">{{ $l ? 'المدينة' : 'City' }}</dt>
                            <dd class="text-slate-800 text-end">{{ $cityLabel }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500 shrink-0">{{ $l ? 'المحافظة' : 'Governorate' }}</dt>
                            <dd class="text-slate-800 text-end">{{ $govLabel }}</dd>
                        </div>
                        <div class="flex justify-between items-center gap-3 pt-1">
                            <dt class="text-slate-500">{{ $l ? 'الحالة' : 'Status' }}</dt>
                            <dd>@include('admin.laboratories.partials.status-toggle', ['laboratory' => $laboratory])</dd>
                        </div>
                    </dl>
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        @include('admin.laboratories.actions', ['laboratory' => $laboratory])
                    </div>
                </div>
            @endforeach
        </div>

        <div class="hidden lg:block overflow-x-auto p-4 sm:p-6 pt-0">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الاسم' : 'Name' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'النوع' : 'Type' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'المستخدم' : 'User' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'المنطقة' : 'Area' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'المدينة' : 'City' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'المحافظة' : 'Governorate' }}</th>
                        <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الحالة' : 'Status' }}</th>
                        <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'إجراءات' : 'Actions' }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($laboratories as $laboratory)
                        @php
                            $areaLabel = '—';
                            if ($laboratory->area) {
                                $areaLabel = $l
                                    ? ($laboratory->area->name_ar ?? $laboratory->area->name ?? '—')
                                    : ($laboratory->area->name ?? $laboratory->area->name_ar ?? '—');
                            }
                            $cityLabel = $laboratory->area?->city
                                ? ($l ? ($laboratory->area->city->name_ar ?? $laboratory->area->city->name) : ($laboratory->area->city->name ?? $laboratory->area->city->name_ar))
                                : '—';
                            $govLabel = $laboratory->area?->city?->governorate
                                ? ($l ? ($laboratory->area->city->governorate->name_ar ?? $laboratory->area->city->governorate->name) : ($laboratory->area->city->governorate->name ?? $laboratory->area->city->governorate->name_ar))
                                : '—';
                            $typeLabel = $laboratory->type === 'radiology' ? ($l ? 'أشعة' : 'Radiology') : ($l ? 'تحاليل' : 'Tests');
                        @endphp
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-sm font-medium text-slate-800">#{{ $laboratory->id }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $laboratory->name }}</td>
                            <td class="px-4 py-3 text-sm text-slate-600">{{ $typeLabel }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $laboratory->user->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $areaLabel }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $cityLabel }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $govLabel }}</td>
                            <td class="px-4 py-3 text-sm">
                                @include('admin.laboratories.partials.status-toggle', ['laboratory' => $laboratory])
                            </td>
                            <td class="px-4 py-3 text-sm text-end">@include('admin.laboratories.actions', ['laboratory' => $laboratory])</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-4 sm:px-6 pb-6 border-t border-slate-100 pt-4">{{ $laboratories->links() }}</div>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function () {
    const isAr = @json($l);
    const activeLabel = isAr ? 'نشط' : 'Active';
    const inactiveLabel = isAr ? 'غير نشط' : 'Inactive';
    const errorMsg = isAr ? 'تعذر تحديث الحالة. حاول مرة أخرى.' : 'Could not update status. Please try again.';

    $(document).on('change', '.lab-active-toggle-input', function () {
        const $input = $(this);
        const $wrap = $input.closest('.lab-active-toggle');
        const $label = $wrap.find('.lab-active-toggle-label');
        const url = $wrap.data('toggle-url');
        const previousChecked = !$input.is(':checked');

        $input.prop('disabled', true);

        $.ajax({
            url: url,
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                Accept: 'application/json',
            },
        })
            .done(function (res) {
                if (!res.success) {
                    $input.prop('checked', previousChecked);
                    toastr.error(res.message || errorMsg);
                    return;
                }

                const on = !!res.is_active;
                $input.prop('checked', on);
                $label
                    .text(on ? activeLabel : inactiveLabel)
                    .toggleClass('text-emerald-700', on)
                    .toggleClass('text-red-600', !on);
                toastr.success(res.message);
            })
            .fail(function (xhr) {
                $input.prop('checked', previousChecked);
                const msg = xhr.responseJSON?.message || errorMsg;
                toastr.error(msg);
            })
            .always(function () {
                $input.prop('disabled', false);
            });
    });
})();
</script>
@endpush
