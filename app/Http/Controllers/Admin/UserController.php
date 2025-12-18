<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.users.index');
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'pharmacy_id' => 'nullable|exists:pharmacies,id',
            'laboratory_id' => 'nullable|exists:laboratories,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء المستخدم بنجاح' : 'User created successfully');
    }

    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'pharmacy_id' => 'nullable|exists:pharmacies,id',
            'laboratory_id' => 'nullable|exists:laboratories,id',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم تحديث المستخدم بنجاح' : 'User updated successfully');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم حذف المستخدم بنجاح' : 'User deleted successfully');
    }

    public function data()
    {
        $users = User::with(['pharmacy', 'laboratory'])->select('users.*');

        return DataTables::of($users)
            ->addColumn('pharmacy_name', function ($user) {
                return $user->pharmacy->name ?? '-';
            })
            ->addColumn('laboratory_name', function ($user) {
                return $user->laboratory->name ?? '-';
            })
            ->addColumn('actions', function ($user) {
                return view('admin.users.actions', compact('user'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }
}

