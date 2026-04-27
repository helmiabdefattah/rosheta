<div class="flex flex-col sm:flex-row sm:items-center gap-2 min-w-0 lg:justify-end">
    @php
        $req = $request ?? ($record ?? null);
    @endphp
    <a href="{{ route('admin.client-requests.show', $req) }}" class="inline-flex justify-center px-3 py-2 text-sm bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition-colors text-center">
        {{ app()->getLocale() === 'ar' ? 'عرض' : 'View' }}
    </a>
    <a href="{{ route('offers.create', ['request' => $req->id ?? null]) }}" class="inline-flex justify-center px-3 py-2 text-sm bg-emerald-100 text-emerald-700 rounded-lg hover:bg-emerald-200 transition-colors text-center">
        {{ app()->getLocale() === 'ar' ? 'إنشاء عرض' : 'Make Offer' }}
    </a>
    <form action="{{ route('admin.client-requests.destroy', $req) }}" method="POST" class="inline sm:inline" onsubmit="return confirm('{{ app()->getLocale() === 'ar' ? 'هل أنت متأكد من الحذف؟' : 'Are you sure?' }}');">
        @csrf
        @method('DELETE')
        <button type="submit" class="w-full sm:w-auto px-3 py-2 text-sm bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors">
            {{ app()->getLocale() === 'ar' ? 'حذف' : 'Delete' }}
        </button>
    </form>
</div>

