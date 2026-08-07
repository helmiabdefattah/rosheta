@php $ar = app()->getLocale() === 'ar'; @endphp
<div class="px-5 py-4">
    <div class="flex items-center justify-between gap-3 flex-wrap">
        <div class="min-w-0">
            <p class="font-medium text-slate-800 flex items-center gap-2">
                {{ $assistant->name }}
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $assistant->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' }}">
                    {{ $assistant->is_active ? ($ar ? 'مفعّل' : 'Active') : ($ar ? 'غير مفعّل' : 'Inactive') }}
                </span>
            </p>
            <p class="text-sm text-slate-500 break-all">
                {{ $assistant->phone_number }}@if($assistant->email) · {{ $assistant->email }}@endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button"
                    class="px-3 py-1.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50"
                    onclick="document.getElementById('edit-assistant-{{ $assistant->id }}').classList.toggle('hidden')">
                {{ $ar ? 'تعديل' : 'Edit' }}
            </button>
            <form method="POST" action="{{ route('doctor.staff.toggle-active', $assistant) }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="px-3 py-1.5 text-sm font-medium rounded-lg border {{ $assistant->is_active ? 'text-amber-700 bg-amber-50 border-amber-200 hover:bg-amber-100' : 'text-emerald-700 bg-emerald-50 border-emerald-200 hover:bg-emerald-100' }}">
                    {{ $assistant->is_active ? ($ar ? 'إيقاف' : 'Deactivate') : ($ar ? 'تفعيل' : 'Activate') }}
                </button>
            </form>
            <form method="POST" action="{{ route('doctor.staff.destroy', $assistant) }}"
                  onsubmit="return confirm('{{ $ar ? 'سيتم حذف حساب المساعد نهائياً. هل أنت متأكد؟' : 'This permanently deletes the assistant account. Are you sure?' }}');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-3 py-1.5 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100">
                    {{ $ar ? 'حذف' : 'Delete' }}
                </button>
            </form>
        </div>
    </div>

    <form id="edit-assistant-{{ $assistant->id }}" method="POST" action="{{ route('doctor.staff.update', $assistant) }}"
          class="hidden mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 border-t border-gray-100 pt-4">
        @csrf
        @method('PUT')
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ $ar ? 'الاسم' : 'Name' }}</label>
            <input type="text" name="name" value="{{ $assistant->name }}" required
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ $ar ? 'العيادة' : 'Clinic' }}</label>
            <select name="clinic_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500">
                @foreach($clinics as $c)
                    <option value="{{ $c->id }}" {{ (int) $assistant->clinic_id === $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">
                {{ $ar ? 'رقم الهاتف' : 'Phone' }} <span class="text-red-500">*</span>
            </label>
            <input type="text" name="phone_number" value="{{ $assistant->phone_number }}" required
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ $ar ? 'البريد الإلكتروني (اختياري)' : 'Email (optional)' }}</label>
            <input type="email" name="email" value="{{ $assistant->email }}"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ $ar ? 'كلمة مرور جديدة (اختياري)' : 'New password (optional)' }}</label>
            <input type="password" name="password" autocomplete="new-password"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">{{ $ar ? 'تأكيد كلمة المرور' : 'Confirm password' }}</label>
            <input type="password" name="password_confirmation" autocomplete="new-password"
                   class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-teal-500/30 focus:border-teal-500">
        </div>
        <div class="sm:col-span-2 flex justify-end gap-3">
            <button type="button" class="px-4 py-2 text-sm text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50"
                    onclick="document.getElementById('edit-assistant-{{ $assistant->id }}').classList.add('hidden')">
                {{ $ar ? 'إلغاء' : 'Cancel' }}
            </button>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-teal-600 rounded-lg hover:bg-teal-700">
                {{ $ar ? 'حفظ التعديلات' : 'Save changes' }}
            </button>
        </div>
    </form>
</div>
