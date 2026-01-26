<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class DoctorController extends Controller
{
    public function index()
    {
        return view('admin.doctors.index');
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
        ]);
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }
        if (empty($validated['user_id'])) {
            $baseEmail = strtolower(Str::slug($validated['name'], ''));
            $email = $baseEmail . '-' . Str::random(6) . '@doctors.local';
            while (User::where('email', $email)->exists()) {
                $email = $baseEmail . '-' . Str::random(6) . '@doctors.local';
            }
            $user = User::create([
                'name' => $validated['name'],
                'email' => $email,
                'password' => Hash::make('password'),
            ]);
            $validated['user_id'] = $user->id;
        }
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
        ]);
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }
        $doctor->update($validated);
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

    public function data()
    {
        $doctors = Doctor::with(['specialization', 'user'])->select('doctors.*');
        return DataTables::of($doctors)
            ->addColumn('slug', fn ($d) => $d->slug ?? '-')
            ->addColumn('specialization_name', fn ($d) => $d->specialization?->name ?? '-')
            ->addColumn('user_email', fn ($d) => $d->user?->email ?? '-')
            ->addColumn('image', fn ($d) => $d->getFirstMediaUrl('profile_image') ? '<img src="' . $d->getFirstMediaUrl('profile_image') . '" class="w-10 h-10 rounded-full object-cover" alt="">' : '-')
            ->addColumn('actions', fn ($d) => view('admin.doctors.actions', ['doctor' => $d])->render())
            ->rawColumns(['actions', 'image'])
            ->make(true);
    }
}
