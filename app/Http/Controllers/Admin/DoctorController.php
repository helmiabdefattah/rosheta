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
        ]);
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }
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
            ]);
            $validated['user_id'] = $user->id;
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
        $doctor->load(['specialization', 'user', 'clinics']);
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
        ]);
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }
        $password = $validated['password'] ?? null;
        unset($validated['password']);
        $doctor->update($validated);
        if ($request->filled('password') && $doctor->user_id) {
            optional(User::find($doctor->user_id))->update([
                'password' => $password,
            ]);
        }
        if ($request->hasFile('profile_image')) {
            $doctor->clearMediaCollection('profile_image');
            $doctor->addMediaFromRequest('profile_image')->toMediaCollection('profile_image');
        }
        return redirect()->route('admin.doctors.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم تحديث الطبيب بنجاح' : 'Doctor updated successfully');
    }

    public function destroy(Doctor $doctor)
    {
        $doctor->delete();
        return redirect()->route('admin.doctors.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم حذف الطبيب' : 'Doctor deleted');
    }
}
