<div class="flex items-center gap-2">
    <a href="{{ route('laboratories.users.edit', $user) }}" class="text-primary-600 hover:text-primary-900 text-sm font-medium">
        {{ app()->getLocale() === 'ar' ? 'تعديل' : 'Edit' }}
    </a>
    <form method="POST" action="{{ route('laboratories.users.destroy', $user) }}" class="inline" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من حذف هذا المستخدم؟' : 'Are you sure you want to delete this user?' }}');">
        @csrf
        @method('DELETE')
        <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">
            {{ app()->getLocale() === 'ar' ? 'حذف' : 'Delete' }}
        </button>
    </form>
</div>

