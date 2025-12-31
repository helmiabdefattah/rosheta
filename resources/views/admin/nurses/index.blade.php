@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'الممرضون' : 'Nurses')
@section('page-title', app()->getLocale() === 'ar' ? 'قائمة الممرضين/ات' : 'Nurses List')

@section('content')
<div class="flex items-center justify-between mb-4">
	<a href="{{ route('admin.nurses.create') }}" class="px-4 py-2 bg-primary text-white rounded-lg text-sm">
		{{ app()->getLocale() === 'ar' ? 'إضافة ممرض/ة' : 'Add Nurse' }}
	</a>
</div>

<div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto">
	<table class="min-w-full divide-y divide-gray-200">
		<thead class="bg-gray-50">
			<tr>
				<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
				<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</th>
				<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'الهاتف' : 'Phone' }}</th>
				<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'البريد' : 'Email' }}</th>
				<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'المؤهل' : 'Qualification' }}</th>
				<th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ app()->getLocale() === 'ar' ? 'إجراءات' : 'Actions' }}</th>
			</tr>
		</thead>
		<tbody class="bg-white divide-y divide-gray-200">
			@foreach($nurses as $nurse)
				<tr>
					<td class="px-4 py-2 text-sm text-gray-700">{{ $nurse->id }}</td>
					<td class="px-4 py-2 text-sm text-gray-700">{{ $nurse->client->name ?? '-' }}</td>
					<td class="px-4 py-2 text-sm text-gray-700">{{ $nurse->client->phone_number ?? '-' }}</td>
					<td class="px-4 py-2 text-sm text-gray-700">{{ $nurse->client->email ?? '-' }}</td>
					<td class="px-4 py-2 text-sm text-gray-700">{{ ucfirst(str_replace('_',' ',$nurse->qualification ?? '-')) }}</td>
					<td class="px-4 py-2 text-sm text-right">
						<a href="{{ route('admin.nurses.show', $nurse) }}" class="px-3 py-1 bg-gray-200 text-slate-800 rounded-md text-xs">{{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}</a>
						<a href="{{ route('admin.nurses.edit', $nurse) }}" class="px-3 py-1 bg-blue-600 text-white rounded-md text-xs">{{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}</a>
						<form action="{{ route('admin.nurses.destroy', $nurse) }}" method="POST" class="inline-block" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'تأكيد الحذف؟' : 'Delete this nurse?' }}')">
							@csrf
							@method('DELETE')
							<button type="submit" class="px-3 py-1 bg-red-600 text-white rounded-md text-xs">{{ app()->getLocale() === 'ar' ? 'حذف' : 'Delete' }}</button>
						</form>
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


