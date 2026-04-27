@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'الممرضون' : 'Nurses')
@section('page-title', app()->getLocale() === 'ar' ? 'قائمة الممرضين/ات' : 'Nurses List')

@section('content')
    @php $l = app()->getLocale() === 'ar'; @endphp
    <div class="flex items-center justify-between mb-4">
        <a href="{{ route('admin.nurses.create') }}" class="px-4 py-2 bg-primary text-white rounded-lg text-sm">
            {{ $l ? 'إضافة تمريض' : 'Add Nurse' }}
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-visible lg:overflow-x-auto lg:table-scroll-container p-2 sm:p-0 lg:p-0">
        <table class="min-w-full divide-y divide-gray-200 stack-table-mobile">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الصورة' : 'Image' }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الاسم' : 'Name' }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الهاتف' : 'Phone' }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-48">{{ $l ? 'المناطق' : 'Areas' }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'الحالة' : 'Status' }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'المؤهل' : 'Qualification' }}</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $l ? 'إجراءات' : 'Actions' }}</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            @foreach($nurses as $nurse)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 text-sm text-gray-700" data-label="#">{{ $nurse->id }}</td>
                    <td class="px-4 py-2 text-sm text-gray-700" data-label="{{ $l ? 'الصورة' : 'Image' }}">
                        @if($nurse->user && $nurse->user->hasMedia('avatar'))
                            <img src="{{ $nurse->user->getFirstMediaUrl('avatar') }}" alt="avatar" class="h-10 w-10 rounded-full object-cover border">
                        @else
                            <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-xs text-gray-500">
                                {{ strtoupper(mb_substr($nurse->user->name ?? 'N', 0, 1)) }}
                            </div>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-sm text-gray-700" data-label="{{ $l ? 'الاسم' : 'Name' }}">{{ $nurse->user->name ?? '-' }}</td>
                    <td class="px-4 py-2 text-sm text-gray-700" data-label="{{ $l ? 'الهاتف' : 'Phone' }}">{{ $nurse->user->phone_number ?? '-' }}</td>
                    <td class="px-4 py-2 text-sm text-gray-700" data-label="{{ $l ? 'المناطق' : 'Areas' }}">
                        @php
                            $ids = is_array($nurse->area_ids) ? $nurse->area_ids : [];
                            $labels = collect($ids)->map(function($id) use ($areaMap) {
                                $area = $areaMap[$id] ?? null;
                                if (!$area) return null;
                                $city = $area->city->name ?? '';
                                $gov = $area->city->governorate->name ?? '';
                                return trim($area->name . ($city ? ' - '.$city : '') . ($gov ? ' ('.$gov.')' : ''));
                            })->filter()->values();
                        @endphp
                        @if($labels->isEmpty())
                            <span class="text-gray-400">-</span>
                        @else
                            @php
                                $display = $labels->take(2);
                                $remaining = $labels->count() - $display->count();
                            @endphp
                            <div class="flex flex-wrap gap-1 lg:max-w-[200px]">
                                @foreach($display as $label)
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 text-xs">{{ $label }}</span>
                                @endforeach
                                @if($remaining > 0)
                                    <span class="px-2 py-0.5 rounded bg-slate-200 text-slate-700 text-xs">+{{ $remaining }}</span>
                                @endif
                            </div>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-sm text-gray-700" data-label="{{ $l ? 'الحالة' : 'Status' }}">
                        @if($nurse->status === 'active')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">{{ $l ? 'نشط' : 'Active' }}</span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">{{ $l ? 'غير نشط' : 'Inactive' }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-sm text-gray-700" data-label="{{ $l ? 'المؤهل' : 'Qualification' }}">{{ ucfirst(str_replace('_',' ',$nurse->qualification ?? '-')) }}</td>
                    <td class="px-4 py-2 text-sm text-right stack-td-actions" data-label="{{ $l ? 'إجراءات' : 'Actions' }}">
                        <div class="flex flex-wrap items-center justify-end gap-2">
                            <a href="{{ route('admin.nurses.show', $nurse) }}" class="inline-block px-3 py-2 bg-gray-200 text-slate-800 rounded-md text-xs text-center">{{ $l ? 'عرض' : 'View' }}</a>
                            <a href="{{ route('admin.nurses.edit', $nurse) }}" class="inline-block px-3 py-2 bg-blue-600 text-white rounded-md text-xs text-center">{{ $l ? 'تعديل' : 'Edit' }}</a>
                            <form action="{{ route('admin.nurses.destroy', $nurse) }}" method="POST" class="inline-block w-full sm:w-auto" onsubmit="return confirm('{{ $l ? 'تأكيد الحذف؟' : 'Delete this nurse?' }}')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full sm:w-auto px-3 py-2 bg-red-600 text-white rounded-md text-xs">{{ $l ? 'حذف' : 'Delete' }}</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $nurses->links() }}
    </div>
@endsection
