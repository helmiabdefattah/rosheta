<div class="flex items-center gap-2">
    @php
        $req = $request ?? ($record ?? null);
    @endphp
    <a href="{{ route('admin.client-requests.show', $req) }}" class="px-3 py-1 text-sm bg-green-100 text-green-700 rounded hover:bg-green-200 transition-colors">
        {{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}
    </a>
    <a href="{{ route('offers.create', ['request' => $req->id ?? null]) }}" class="px-3 py-1 text-sm bg-emerald-100 text-emerald-700 rounded hover:bg-emerald-200 transition-colors">
        {{ app()->getLocale() === 'ar' ? 'إنشاء عرض' : 'Make Offer' }}
    </a>
    <form action="{{ route('admin.client-requests.destroy', $req) }}" method="POST" class="inline" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من الحذف؟' : 'Are you sure?' }}');">
        @csrf
        @method('DELETE')
        <button type="submit" class="px-3 py-1 text-sm bg-red-100 text-red-700 rounded hover:bg-red-200 transition-colors">
            {{ app()->getLocale() === 'ar' ? 'حذف' : 'Delete' }}
        </button>
    </form>
</div>

