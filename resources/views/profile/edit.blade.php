@extends($layout)

@section('title', app()->getLocale() === 'ar' ? 'الملف الشخصي' : 'My Profile')
@section('page-title', app()->getLocale() === 'ar' ? 'الملف الشخصي' : 'My Profile')

@section('content')
<div class="max-w-4xl mx-auto py-8 px-4">
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-xl font-bold text-slate-800">
                {{ app()->getLocale() === 'ar' ? 'إعدادات الحساب' : 'Account Settings' }}
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                {{ app()->getLocale() === 'ar' ? 'قم بتحديث معلوماتك الشخصية وإعدادات الإشعارات.' : 'Update your personal information and notification settings.' }}
            </p>
        </div>

        <form action="{{ route('user.profile.update') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">
                        {{ app()->getLocale() === 'ar' ? 'الاسم' : 'Name' }}
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                        class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none">
                    @error('name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">
                        {{ app()->getLocale() === 'ar' ? 'البريد الإلكتروني' : 'Email Address' }}
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                        class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none">
                    @error('email')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">
                        {{ app()->getLocale() === 'ar' ? 'كلمة مرور جديدة' : 'New Password' }}
                        <span class="text-xs font-normal text-slate-400">({{ app()->getLocale() === 'ar' ? 'اختياري' : 'Optional' }})</span>
                    </label>
                    <input type="password" name="password" id="password"
                        class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none"
                        placeholder="••••••••">
                    @error('password')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-2">
                        {{ app()->getLocale() === 'ar' ? 'تأكيد كلمة المرور' : 'Confirm Password' }}
                    </label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all outline-none"
                        placeholder="••••••••">
                </div>
            </div>

            <!-- Profile Image -->
            <div class="pt-4 border-t border-slate-100">
                <label class="block text-sm font-semibold text-slate-700 mb-4">
                    {{ app()->getLocale() === 'ar' ? 'الصورة الشخصية' : 'Profile Image' }}
                </label>
                <div class="flex items-center gap-6">
                    <img src="{{ $user->getFirstMediaUrl('profile_image') ?: 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=0d9488&color=fff' }}" 
                         class="w-20 h-20 rounded-2xl object-cover border-4 border-slate-50 shadow-sm"
                         id="preview-image">
                    
                    <div class="flex-1">
                        <input type="file" name="profile_image" id="profile_image" accept="image/*" class="hidden" onchange="previewImage(this)">
                        <label for="profile_image" class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 cursor-pointer transition-colors shadow-sm">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            {{ app()->getLocale() === 'ar' ? 'تغيير الصورة' : 'Change Photo' }}
                        </label>
                        <p class="text-xs text-slate-400 mt-2">JPG, PNG or WEBP. Max 2MB.</p>
                    </div>
                </div>
            </div>

            <!-- Notification Sound -->
            <div class="pt-6 border-t border-slate-100">
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                    <label class="flex items-center justify-between cursor-pointer group">
                        <div class="flex-1">
                            <h3 class="text-sm font-bold text-slate-800">
                                {{ app()->getLocale() === 'ar' ? 'صوت الإشعارات' : 'Notification Sound' }}
                            </h3>
                            <p class="text-xs text-slate-500 mt-1">
                                {{ app()->getLocale() === 'ar' ? 'تشغيل صوت مميز عند استلام إشعارات جديدة في النظام.' : 'Play a distinct sound when new system notifications arrive.' }}
                            </p>
                        </div>
                        <div class="relative inline-flex items-center ml-4">
                            <input type="checkbox" name="notification_sound" value="1" {{ old('notification_sound', $user->notification_sound) ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                        </div>
                    </label>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 flex justify-end gap-3">
                <button type="submit" class="px-8 py-2.5 bg-primary text-white font-bold rounded-lg hover:bg-primary/90 transition-all shadow-md shadow-primary/20">
                    {{ app()->getLocale() === 'ar' ? 'حفط التغييرات' : 'Save Changes' }}
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('preview-image').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
