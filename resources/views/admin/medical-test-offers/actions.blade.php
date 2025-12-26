<div class="flex items-center gap-2">
    <a href="{{ route('admin.medical-test-offers.show', $offer) }}" class="px-3 py-1 text-sm bg-green-100 text-green-700 rounded hover:bg-green-200 transition-colors">
        {{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}
    </a>
    <a href="{{ route('admin.medical-test-offers.edit', $offer) }}" class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition-colors">
        {{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}
    </a>
    <form action="{{ route('admin.medical-test-offers.destroy', $offer) }}" method="POST" class="inline" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من الحذف؟' : 'Are you sure?' }}');">
        @csrf
        @method('DELETE')
        <button type="submit" class="px-3 py-1 text-sm bg-red-100 text-red-700 rounded hover:bg-red-200 transition-colors">
            {{ app()->getLocale() === 'ar' ? 'حذف' : 'Delete' }}
        </button>
    </form>
</div>

