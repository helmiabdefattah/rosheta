<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DoctorController extends Controller
{
    public function index(Request $request)
    {
        $query = Doctor::with(['specialization', 'user', 'media'])->orderByDesc('id');

        if ($request->filled('search')) {
            $term = '%' . $request->string('search') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('id', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('slug', 'like', $term)
                    ->orWhereHas('specialization', function ($q) use ($term) {
                        $q->where('name', 'like', $term);
                    })
                    ->orWhereHas('user', function ($q) use ($term) {
                        $q->where('email', 'like', $term)->orWhere('name', 'like', $term);
                    });
            });
        }

        $doctors = $query->paginate(15)->withQueryString();

        return view('admin.doctors.index', compact('doctors'));
    }

    public function create()
    {
        $specializations = Specialization::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        return view('admin.doctors.create', compact('specializations', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'specialization_id' => 'required|exists:specializations,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:doctors,slug',
            'brief' => 'nullable|string|max:5000',
            'profile_image' => 'nullable|image|mimes:jpeg,png,gif,webp|max:2048',
            'account_email' => 'nullable|email|unique:users,email',
            'account_phone' => 'nullable|string|max:50|unique:users,phone_number',
            'password' => 'nullable|string|min:8|confirmed',
            'assistant_limit' => 'nullable|integer|min:0|max:50',
        ]);
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }
        $isActive = $request->boolean('is_active', true);
        $validated['assistant_limit'] = (int) ($validated['assistant_limit'] ?? Doctor::DEFAULT_ASSISTANT_LIMIT);
        if (empty($validated['user_id'])) {
            $request->validate([
                'account_email' => 'required|email|unique:users,email',
                'account_phone' => 'required|string|max:50|unique:users,phone_number',
                'password' => 'required|string|min:8|confirmed',
            ]);
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['account_email'],
                'phone_number' => $validated['account_phone'],
                'password' => $validated['password'],
                'is_active' => $isActive,
            ]);
            $validated['user_id'] = $user->id;
        } else {
            optional(User::find($validated['user_id']))->update(['is_active' => $isActive]);
        }
        unset($validated['account_email'], $validated['account_phone'], $validated['password']);
        $doctor = Doctor::create($validated);
        if ($request->hasFile('profile_image')) {
            $doctor->addMediaFromRequest('profile_image')->toMediaCollection('profile_image');
        }
        return redirect()->route('admin.doctors.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء الطبيب بنجاح' : 'Doctor created successfully');
    }

    public function show(Doctor $doctor)
    {
        $doctor->load(['specialization', 'user', 'clinics', 'assistants.assistantClinic']);
        return view('admin.doctors.show', compact('doctor'));
    }

    public function edit(Doctor $doctor)
    {
        $specializations = Specialization::orderBy('name')->get();
        $users = User::orderBy('name')->get();
        return view('admin.doctors.edit', compact('doctor', 'specializations', 'users'));
    }

    public function update(Request $request, Doctor $doctor)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'specialization_id' => 'required|exists:specializations,id',
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:doctors,slug,' . $doctor->id,
            'brief' => 'nullable|string|max:5000',
            'profile_image' => 'nullable|image|mimes:jpeg,png,gif,webp|max:2048',
            'password' => 'nullable|string|min:8|confirmed',
            'assistant_limit' => 'nullable|integer|min:0|max:50',
        ]);
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }
        $password = $validated['password'] ?? null;
        unset($validated['password']);
        $validated['assistant_limit'] = (int) ($validated['assistant_limit'] ?? $doctor->assistantLimit());
        $doctor->update($validated);
        if ($doctor->user_id) {
            $account = User::find($doctor->user_id);
            $account?->update(array_filter([
                'is_active' => $request->boolean('is_active'),
                'password' => $request->filled('password') ? $password : null,
            ], fn ($value) => $value !== null));
        }
        if ($request->hasFile('profile_image')) {
            $doctor->clearMediaCollection('profile_image');
            $doctor->addMediaFromRequest('profile_image')->toMediaCollection('profile_image');
        }
        return redirect()->route('admin.doctors.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم تحديث الطبيب بنجاح' : 'Doctor updated successfully');
    }

    /**
     * Activate / deactivate the doctor's login. A deactivated doctor is turned
     * away at login with an "account not active" message, and so are the
     * assistants working under them.
     */
    public function toggleActive(Request $request, Doctor $doctor)
    {
        $ar = app()->getLocale() === 'ar';
        $account = $doctor->user;

        if (! $account) {
            $message = $ar
                ? 'لا يوجد حساب دخول مرتبط بهذا الطبيب.'
                : 'This doctor has no linked login account.';

            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $message], 422)
                : back()->with('error', $message);
        }

        $account->update(['is_active' => ! $account->is_active]);
        $account->refresh();

        $message = $account->is_active
            ? ($ar ? 'تم تفعيل حساب الطبيب' : 'Doctor account activated')
            : ($ar ? 'تم إيقاف حساب الطبيب' : 'Doctor account deactivated');

        return $request->expectsJson()
            ? response()->json([
                'success' => true,
                'is_active' => $account->is_active,
                'message' => $message,
            ])
            : back()->with('success', $message);
    }

    public function destroy(Doctor $doctor)
    {
        $doctor->delete();
        return redirect()->route('admin.doctors.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم حذف الطبيب' : 'Doctor deleted');
    }
}
