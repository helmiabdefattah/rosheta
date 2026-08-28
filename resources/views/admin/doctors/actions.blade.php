<div class="flex items-center gap-2">
    <a href="{{ route('admin.doctors.show', $doctor) }}" class="px-3 py-1 text-sm bg-slate-100 text-slate-700 rounded hover:bg-slate-200 transition-colors">{{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}</a>
    <a href="{{ route('admin.doctors.edit', $doctor) }}" class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors">{{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}</a>
    @if(! $doctor->user_id)
        <a href="{{ route('admin.doctors.account.create', $doctor) }}" class="px-3 py-1 text-sm bg-emerald-100 text-emerald-700 rounded hover:bg-emerald-200 transition-colors">{{ app()->getLocale() === 'ar' ? 'إضافة حساب' : 'Add account' }}</a>
    @endif
    <form action="{{ route('admin.doctors.destroy', $doctor) }}" method="POST" class="inline" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من الحذف؟' : 'Are you sure?' }}');">
        @csrf
        @method('DELETE')
        <button type="submit" class="px-3 py-1 text-sm bg-red-100 text-red-700 rounded hover:bg-red-200 transition-colors">{{ app()->getLocale() === 'ar' ? 'حذف' : 'Delete' }}</button>
    </form>
</div>
