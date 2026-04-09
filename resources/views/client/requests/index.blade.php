@extends('client.layouts.dashboard')

@section('title', app()->getLocale() === 'ar' ? 'جميع الطلبات' : 'All Requests')
@section('page-title', app()->getLocale() === 'ar' ? 'جميع الطلبات' : 'All Requests')
@section('page-description', app()->getLocale() === 'ar' ? 'عرض وإدارة طلباتك (أدوية، تحاليل، أشعة، تمريض منزلي، حجوزات العيادات)' : 'View and manage your requests (medicines, tests, radiology, home nursing, clinic visits)')

@section('content')
@php
	$isAr = app()->getLocale() === 'ar';
	$filters = [
		'all' => $isAr ? 'الكل' : 'All',
		'medicine' => $isAr ? 'أدوية' : 'Medicines',
		'test' => $isAr ? 'تحاليل' : 'Tests',
		'radiology' => $isAr ? 'أشعة' : 'Radiology',
		'nurse' => $isAr ? 'تمريض منزلي' : 'Home nursing',
		'clinic' => $isAr ? 'حجوزات العيادات' : 'Clinic visits',
	];
@endphp
<div class="max-w-6xl mx-auto space-y-6">
	@if(session('success'))
		<div class="p-4 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm">{{ session('success') }}</div>
	@endif
	@if($errors->has('delete'))
		<div class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">{{ $errors->first('delete') }}</div>
	@endif

	<div class="flex flex-wrap gap-2">
		@foreach($filters as $key => $label)
			<a href="{{ route('client.requests.index', ['type' => $key]) }}"
			   class="px-4 py-2 rounded-lg text-sm font-medium transition
				{{ $filterType === $key ? 'bg-primary text-white' : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50' }}">
				{{ $label }}
			</a>
		@endforeach
	</div>

	<p class="text-sm text-gray-600">
		{{ $isAr
			? 'يمكنك تعديل أو حذف طلبات الأدوية والتحاليل والتمريض أثناء «قيد الانتظار» فقط. مواعيد العيادة: يمكن تعديل الملاحظات في حالة الانتظار، وإلغاء الموعد المستقبلي إن كان قيد الانتظار أو مؤكدًا.'
			: 'Pharmacy, lab, and nursing requests can be edited or deleted only while pending. Clinic visits: you can edit notes while pending, and cancel a future appointment if it is still pending or confirmed.' }}
	</p>

	<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
	@forelse($rows as $row)
		@php
			$m = $row['model'];
			$kind = $row['kind'];
			$visitTypeLabel = '';
			$canCancelClinic = false;
			if ($kind === 'pharmacy_lab') {
				$typeLabel = match ($m->type) {
					'medicine' => $isAr ? 'أدوية' : 'Medicine',
					'test' => $isAr ? 'تحاليل' : 'Tests',
					'radiology' => $isAr ? 'أشعة' : 'Radiology',
					default => $m->type,
				};
				$statusClass = match ($m->status) {
					'pending' => 'bg-amber-100 text-amber-800',
					'approved' => 'bg-green-100 text-green-800',
					'rejected' => 'bg-red-100 text-red-800',
					default => 'bg-gray-100 text-gray-800',
				};
				$canMutate = $m->status === 'pending';
				$cardAccent = match ($m->type) {
					'medicine' => 'from-teal-500 to-emerald-600',
					'test' => 'from-sky-500 to-blue-600',
					'radiology' => 'from-violet-500 to-purple-600',
					default => 'from-slate-400 to-slate-600',
				};
			} elseif ($kind === 'clinic') {
				$typeLabel = $isAr ? 'حجز عيادة' : 'Clinic visit';
				$visitTypeLabel = match ($m->type) {
					'medical_examination' => $isAr ? 'كشف' : 'Examination',
					'follow_up' => $isAr ? 'متابعة' : 'Follow-up',
					default => $m->type,
				};
				$statusClass = match ($m->status) {
					'pending' => 'bg-amber-100 text-amber-800',
					'confirmed' => 'bg-green-100 text-green-800',
					'completed' => 'bg-emerald-100 text-emerald-800',
					'missed' => 'bg-orange-100 text-orange-800',
					'cancelled' => 'bg-gray-100 text-gray-800',
					default => 'bg-gray-100 text-gray-800',
				};
				$canMutate = $m->status === 'pending';
				$t = $m->appointment_time;
				$timeStr = $t instanceof \DateTimeInterface ? $t->format('H:i:s') : (string) $t;
				$startsAt = \Carbon\Carbon::parse($m->appointment_date->format('Y-m-d').' '.$timeStr);
				$canCancelClinic = in_array($m->status, ['pending', 'confirmed'], true) && $startsAt->isFuture();
				$cardAccent = 'from-cyan-500 to-teal-600';
			} else {
				$typeLabel = $isAr ? 'تمريض منزلي' : 'Home nursing';
				$statusClass = match ($m->status) {
					'pending' => 'bg-amber-100 text-amber-800',
					'scheduled' => 'bg-blue-100 text-blue-800',
					'in_progress' => 'bg-indigo-100 text-indigo-800',
					'completed' => 'bg-green-100 text-green-800',
					'cancelled' => 'bg-gray-100 text-gray-800',
					default => 'bg-gray-100 text-gray-800',
				};
				$canMutate = $m->status === 'pending';
				$cardAccent = 'from-rose-500 to-pink-600';
			}
		@endphp
		<div class="group bg-white rounded-xl border border-gray-200/80 shadow-sm hover:shadow-md hover:border-gray-300/90 transition-shadow duration-200 overflow-hidden">
			<div class="h-1 bg-gradient-to-r {{ $cardAccent }}"></div>
			<div class="p-5 flex flex-col gap-4">
				<div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
					<div class="min-w-0 flex-1 space-y-2">
						<div class="flex flex-wrap items-center gap-2">
							<span class="text-xs font-bold text-gray-400 tabular-nums">#{{ $m->id }}</span>
							<span class="px-2.5 py-0.5 rounded-md text-xs font-semibold bg-slate-100 text-slate-800 border border-slate-200/80">{{ $typeLabel }}</span>
							@if($kind === 'clinic' && $visitTypeLabel)
								<span class="px-2.5 py-0.5 rounded-md text-xs font-semibold bg-cyan-50 text-cyan-900 border border-cyan-200/80">{{ $visitTypeLabel }}</span>
							@endif
							<span class="px-2.5 py-0.5 rounded-md text-xs font-semibold {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $m->status)) }}</span>
						</div>
						<p class="text-xs text-gray-500 flex items-center gap-1.5">
							<i class="bi bi-clock"></i>
							{{ $row['created_at']->timezone(config('app.timezone'))->format('Y-m-d H:i') }}
						</p>
						@if($kind === 'pharmacy_lab')
							<p class="text-sm text-gray-800 leading-relaxed line-clamp-3">{{ \Illuminate\Support\Str::limit($m->note ?? '—', 160) }}</p>
						@elseif($kind === 'clinic')
							@php
								$apptTime = $m->appointment_time;
								$apptTimeFmt = $apptTime instanceof \DateTimeInterface ? $apptTime->format('H:i') : (string) $apptTime;
							@endphp
							<p class="text-sm font-medium text-gray-900">
								<i class="bi bi-calendar-event text-primary"></i>
								{{ $m->appointment_date->format('Y-m-d') }}
								<span class="text-gray-500 font-normal">·</span>
								{{ $apptTimeFmt }}
							</p>
							<p class="text-sm text-gray-700 line-clamp-2 mt-1">
								{{ $m->clinic->name ?? '—' }}
								@if($m->doctor) — {{ $m->doctor->name }} @endif
							</p>
						@else
							<p class="text-sm text-gray-800 leading-relaxed line-clamp-3">{{ $m->getTranslatedServiceType() }}</p>
						@endif
					</div>
				</div>
				<div class="flex flex-wrap items-center gap-2 pt-1 border-t border-gray-100">
					@if($kind === 'pharmacy_lab')
						<a href="{{ route('client.requests.pharmacy-lab.show', $m) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium rounded-lg bg-slate-100 text-slate-800 hover:bg-slate-200 transition-colors">
							<i class="bi bi-eye text-base"></i>
							{{ $isAr ? 'عرض' : 'View' }}
						</a>
						@if($canMutate)
							<a href="{{ route('client.requests.pharmacy-lab.edit', $m) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium rounded-lg bg-primary text-white hover:opacity-90 transition-opacity">
								<i class="bi bi-pencil text-base"></i>
								{{ $isAr ? 'تعديل' : 'Edit' }}
							</a>
							<form action="{{ route('client.requests.pharmacy-lab.destroy', $m) }}" method="POST" class="inline" onsubmit="return confirm(@json($isAr ? 'حذف هذا الطلب؟' : 'Delete this request?'));">
								@csrf
								@method('DELETE')
								<button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium rounded-lg bg-red-600 text-white hover:bg-red-700 transition-colors">
									<i class="bi bi-trash text-base"></i>
									{{ $isAr ? 'حذف' : 'Delete' }}
								</button>
							</form>
						@endif
					@elseif($kind === 'clinic')
						<a href="{{ route('client.requests.clinic.show', $m) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium rounded-lg bg-slate-100 text-slate-800 hover:bg-slate-200 transition-colors">
							<i class="bi bi-eye text-base"></i>
							{{ $isAr ? 'عرض' : 'View' }}
						</a>
						@if($canMutate)
							<a href="{{ route('client.requests.clinic.edit', $m) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium rounded-lg bg-primary text-white hover:opacity-90 transition-opacity">
								<i class="bi bi-pencil text-base"></i>
								{{ $isAr ? 'تعديل' : 'Edit' }}
							</a>
						@endif
						@if($canCancelClinic)
							<form action="{{ route('client.requests.clinic.cancel', $m) }}" method="POST" class="inline" onsubmit="return confirm(@json($isAr ? 'إلغاء هذا الموعد؟' : 'Cancel this appointment?'));">
								@csrf
								@method('DELETE')
								<button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium rounded-lg bg-red-600 text-white hover:bg-red-700 transition-colors">
									<i class="bi bi-x-circle text-base"></i>
									{{ $isAr ? 'إلغاء الموعد' : 'Cancel' }}
								</button>
							</form>
						@endif
					@else
						<a href="{{ route('client.nurse-requests.show', $m) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium rounded-lg bg-slate-100 text-slate-800 hover:bg-slate-200 transition-colors">
							<i class="bi bi-eye text-base"></i>
							{{ $isAr ? 'عرض' : 'View' }}
						</a>
						@if($canMutate)
							<a href="{{ route('client.nurse-requests.edit', $m) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium rounded-lg bg-primary text-white hover:opacity-90 transition-opacity">
								<i class="bi bi-pencil text-base"></i>
								{{ $isAr ? 'تعديل' : 'Edit' }}
							</a>
							<form action="{{ route('client.requests.nurse.destroy', $m) }}" method="POST" class="inline" onsubmit="return confirm(@json($isAr ? 'حذف طلب التمريض؟' : 'Delete this nursing request?'));">
								@csrf
								@method('DELETE')
								<button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium rounded-lg bg-red-600 text-white hover:bg-red-700 transition-colors">
									<i class="bi bi-trash text-base"></i>
									{{ $isAr ? 'حذف' : 'Delete' }}
								</button>
							</form>
						@endif
					@endif
				</div>
			</div>
		</div>
	@empty
		<div class="lg:col-span-2 bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center text-gray-500 text-sm shadow-sm">
			<i class="bi bi-inbox text-3xl text-gray-300 mb-3 block"></i>
			{{ $isAr ? 'لا توجد طلبات لهذا التصفية.' : 'No requests for this filter.' }}
		</div>
	@endforelse
	</div>

	@if($rows->hasPages())
		<div class="mt-4">{{ $rows->links() }}</div>
	@endif
</div>
@endsection
