<div class="flex items-center gap-2">
    <a href="{{ route('admin.offers.show', $offer) }}" class="px-3 py-1 text-sm bg-green-100 text-green-700 rounded hover:bg-green-200 transition-colors">
        {{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}
    </a>
    <form action="{{ route('admin.offers.destroy', $offer) }}" method="POST" class="inline" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من الحذف؟' : 'Are you sure?' }}');">
        @csrf
        @method('DELETE')
        <button type="submit" class="px-3 py-1 text-sm bg-red-100 text-red-700 rounded hover:bg-red-200 transition-colors">
            {{ app()->getLocale() === 'ar' ? 'حذف' : 'Delete' }}
        </button>
    </form>
</div>

