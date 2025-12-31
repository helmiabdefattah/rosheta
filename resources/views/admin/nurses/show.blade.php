@extends('admin.layouts.admin')

@section('title', app()->getLocale() === 'ar' ? 'تفاصيل الممرض/ة' : 'Nurse Details')
@section('page-title', app()->getLocale() === 'ar' ? 'تفاصيل الممرض/ة' : 'Nurse Details')
@section('page-description', app()->getLocale() === 'ar' ? 'معلومات كاملة عن الممرض/ة' : 'Full information about the nurse')

@section('content')
<div class="space-y-6">
	<div class="flex items-center justify-between">
		<a href="{{ route('admin.nurses.index') }}" class="px-4 py-2 bg-gray-200 text-slate-800 rounded-md text-sm">
			&larr; {{ app()->getLocale() === 'ar' ? 'رجوع' : 'Back' }}
		</a>
		<a href="{{ route('admin.nurses.edit', $nurse) }}" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm">
			{{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}
		</a>
	</div>

	<div class="bg-white rounded-lg shadow p-6">
		<div class="flex items-start gap-6">
			<div class="shrink-0">
				@if($nurse->client && $nurse->client->avatar)
					<img src="{{ Storage::url($nurse->client->avatar) }}" alt="Avatar" class="w-28 h-28 rounded-full object-cover border">
				@else
					<div class="w-28 h-28 rounded-full bg-gray-200 flex items-center justify-center text-3xl text-gray-500">
						<span>{{ strtoupper(mb_substr($nurse->client->name ?? 'N', 0, 1)) }}</span>
					</div>
				@endif
			</div>
			<div class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full">
				<div>
					<div class="text-xs uppercase text-gray-500">{{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}</div>
					<div class="font-semibold">{{ $nurse->client->name ?? '-' }}</div>
				</div>
				<div>
					<div class="text-xs uppercase text-gray-500">{{ app()->getLocale() === 'ar' ? 'الهاتف' : 'Phone' }}</div>
					<div class="font-semibold">{{ $nurse->client->phone_number ?? '-' }}</div>
				</div>
				<div>
					<div class="text-xs uppercase text-gray-500">{{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email' }}</div>
					<div class="font-semibold">{{ $nurse->client->email ?? '-' }}</div>
				</div>
				<div>
					<div class="text-xs uppercase text-gray-500">{{ app()->getLocale() === 'ar' ? 'النوع' : 'Gender' }}</div>
					<div class="font-semibold">
						@switch($nurse->gender)
							@case('male') {{ app()->getLocale() === 'ar' ? 'ذكر' : 'Male' }} @break
							@case('female') {{ app()->getLocale() === 'ar' ? 'أنثى' : 'Female' }} @break
							@default -
						@endswitch
					</div>
				</div>
				<div>
					<div class="text-xs uppercase text-gray-500">{{ app()->getLocale() === 'ar' ? 'تاريخ الميلاد' : 'Date of Birth' }}</div>
					<div class="font-semibold">{{ optional($nurse->date_of_birth)->format('Y-m-d') ?? '-' }}</div>
				</div>
				<div class="md:col-span-2">
					<div class="text-xs uppercase text-gray-500">{{ app()->getLocale() === 'ar' ? 'العنوان' : 'Address' }}</div>
					<div class="font-semibold">{{ $nurse->address ?? '-' }}</div>
				</div>
			</div>
		</div>
	</div>

	<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
		<div class="bg-white rounded-lg shadow p-6">
			<h3 class="text-lg font-semibold mb-4">{{ app()->getLocale() === 'ar' ? 'التعليم والخبرة' : 'Education & Experience' }}</h3>
			<div class="space-y-3">
				<div>
					<div class="text-xs uppercase text-gray-500">{{ app()->getLocale() === 'ar' ? 'المؤهل' : 'Qualification' }}</div>
					<div class="font-medium">{{ $nurse->qualification ? ucfirst(str_replace('_',' ',$nurse->qualification)) : '-' }}</div>
				</div>
				<div>
					<div class="text-xs uppercase text-gray-500">{{ app()->getLocale() === 'ar' ? 'جهة التعليم' : 'Education Place' }}</div>
					<div class="font-medium">{{ $nurse->education_place ?? '-' }}</div>
				</div>
				<div class="grid grid-cols-2 gap-4">
					<div>
						<div class="text-xs uppercase text-gray-500">{{ app()->getLocale() === 'ar' ? 'سنة التخرج' : 'Graduation Year' }}</div>
						<div class="font-medium">{{ $nurse->graduation_year ?? '-' }}</div>
					</div>
					<div>
						<div class="text-xs uppercase text-gray-500">{{ app()->getLocale() === 'ar' ? 'سنوات الخبرة' : 'Years of Experience' }}</div>
						<div class="font-medium">{{ $nurse->years_of_experience ?? '-' }}</div>
					</div>
				</div>
				<div>
					<div class="text-xs uppercase text-gray-500">{{ app()->getLocale() === 'ar' ? 'جهة العمل الحالية' : 'Current Workplace' }}</div>
					<div class="font-medium">{{ $nurse->current_workplace ?? '-' }}</div>
				</div>
			</div>
		</div>

		<div class="bg-white rounded-lg shadow p-6">
			<h3 class="text-lg font-semibold mb-4">{{ app()->getLocale() === 'ar' ? 'المهارات والشهادات' : 'Skills & Certifications' }}</h3>
			<div class="mb-4">
				<div class="text-xs uppercase text-gray-500 mb-1">{{ app()->getLocale() === 'ar' ? 'المهارات' : 'Skills' }}</div>
				@if(!empty($nurse->skills))
					<div class="flex flex-wrap gap-2">
						@foreach((array)$nurse->skills as $skill)
							<span class="inline-block px-2 py-1 bg-blue-50 text-blue-700 rounded text-xs">{{ $skill }}</span>
						@endforeach
					</div>
				@else
					<div class="text-sm text-gray-500">-</div>
				@endif
			</div>
			<div>
				<div class="text-xs uppercase text-gray-500 mb-1">{{ app()->getLocale() === 'ar' ? 'الشهادات' : 'Certifications' }}</div>
				@if(!empty($nurse->certifications))
					<div class="flex flex-wrap gap-2">
						@foreach((array)$nurse->certifications as $cert)
							<span class="inline-block px-2 py-1 bg-green-50 text-green-700 rounded text-xs">{{ $cert }}</span>
						@endforeach
					</div>
				@else
					<div class="text-sm text-gray-500">-</div>
				@endif
			</div>
		</div>
	</div>
</div>
@endsection


