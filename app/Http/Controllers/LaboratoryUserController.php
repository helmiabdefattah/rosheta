<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Laboratory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;

class LaboratoryUserController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $laboratory = Laboratory::find($user->laboratory_id);

        if (!$laboratory) {
            return redirect()->route('admin.dashboard')
                ->with('error', app()->getLocale() === 'ar' ? 'أنت غير مرتبط بأي معمل.' : 'You are not associated with any laboratory.');
        }

        return view('laboratories.users.index', compact('laboratory'));
    }

    public function data()
    {
        $user = Auth::user();
        $laboratory = Laboratory::find($user->laboratory_id);

        if (!$laboratory) {
            return response()->json([], 403);
        }

        $users = User::where('laboratory_id', $laboratory->id)->select('users.*');

        return DataTables::of($users)
            ->addColumn('actions', function ($user) {
                return view('laboratories.users.actions', compact('user'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function create()
    {
        $user = Auth::user();
        $laboratory = Laboratory::find($user->laboratory_id);

        if (!$laboratory) {
            return redirect()->route('admin.dashboard')
                ->with('error', app()->getLocale() === 'ar' ? 'أنت غير مرتبط بأي معمل.' : 'You are not associated with any laboratory.');
        }

        return view('laboratories.users.create', compact('laboratory'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $laboratory = Laboratory::find($user->laboratory_id);

        if (!$laboratory) {
            return redirect()->route('admin.dashboard')
                ->with('error', app()->getLocale() === 'ar' ? 'أنت غير مرتبط بأي معمل.' : 'You are not associated with any laboratory.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'laboratory_id' => $laboratory->id,
        ]);

        return redirect()->route('laboratories.users.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء المستخدم بنجاح' : 'User created successfully');
    }

    public function edit(User $user)
    {
        $authUser = Auth::user();
        $laboratory = Laboratory::find($authUser->laboratory_id);

        if (!$laboratory || $user->laboratory_id != $laboratory->id) {
            return redirect()->route('laboratories.users.index')
                ->with('error', app()->getLocale() === 'ar' ? 'غير مصرح لك بتعديل هذا المستخدم.' : 'You are not authorized to edit this user.');
        }

        return view('laboratories.users.edit', compact('user', 'laboratory'));
    }

    public function update(Request $request, User $user)
    {
        $authUser = Auth::user();
        $laboratory = Laboratory::find($authUser->laboratory_id);

        if (!$laboratory || $user->laboratory_id != $laboratory->id) {
            return redirect()->route('laboratories.users.index')
                ->with('error', app()->getLocale() === 'ar' ? 'غير مصرح لك بتعديل هذا المستخدم.' : 'You are not authorized to edit this user.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('laboratories.users.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم تحديث المستخدم بنجاح' : 'User updated successfully');
    }

    public function destroy(User $user)
    {
        $authUser = Auth::user();
        $laboratory = Laboratory::find($authUser->laboratory_id);

        if (!$laboratory || $user->laboratory_id != $laboratory->id) {
            return redirect()->route('laboratories.users.index')
                ->with('error', app()->getLocale() === 'ar' ? 'غير مصرح لك بحذف هذا المستخدم.' : 'You are not authorized to delete this user.');
        }

        $user->delete();

        return redirect()->route('laboratories.users.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم حذف المستخدم بنجاح' : 'User deleted successfully');
    }
}

