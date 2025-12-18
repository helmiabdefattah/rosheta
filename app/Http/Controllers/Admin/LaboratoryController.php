<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laboratory;
use App\Models\User;
use App\Models\Area;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class LaboratoryController extends Controller
{
    public function index()
    {
        return view('admin.laboratories.index');
    }

    public function create()
    {
        $users = User::all();
        $areas = Area::with('city.governorate')->where('is_active', true)->get();
        return view('admin.laboratories.create', compact('users', 'areas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'area_id' => 'nullable|exists:areas,id',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'is_active' => 'boolean',
        ]);

        Laboratory::create($validated);

        return redirect()->route('admin.laboratories.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء المعمل بنجاح' : 'Laboratory created successfully');
    }

    public function edit(Laboratory $laboratory)
    {
        $users = User::all();
        $areas = Area::with('city.governorate')->where('is_active', true)->get();
        return view('admin.laboratories.edit', compact('laboratory', 'users', 'areas'));
    }

    public function update(Request $request, Laboratory $laboratory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'area_id' => 'nullable|exists:areas,id',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'is_active' => 'boolean',
        ]);

        $laboratory->update($validated);

        return redirect()->route('admin.laboratories.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم تحديث المعمل بنجاح' : 'Laboratory updated successfully');
    }

    public function destroy(Laboratory $laboratory)
    {
        $laboratory->delete();

        return redirect()->route('admin.laboratories.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم حذف المعمل بنجاح' : 'Laboratory deleted successfully');
    }

    public function data()
    {
        $laboratories = Laboratory::with(['user', 'area.city.governorate'])->select('laboratories.*');

        return DataTables::of($laboratories)
            ->addColumn('user_name', function ($laboratory) {
                return $laboratory->user->name ?? '-';
            })
            ->addColumn('area_name', function ($laboratory) {
                return $laboratory->area->name ?? '-';
            })
            ->addColumn('city_name', function ($laboratory) {
                return $laboratory->area->city->name ?? '-';
            })
            ->addColumn('governorate_name', function ($laboratory) {
                return $laboratory->area->city->governorate->name ?? '-';
            })
            ->addColumn('is_active', function ($laboratory) {
                return $laboratory->is_active 
                    ? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">' . (app()->getLocale() === 'ar' ? 'نشط' : 'Active') . '</span>'
                    : '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">' . (app()->getLocale() === 'ar' ? 'غير نشط' : 'Inactive') . '</span>';
            })
            ->addColumn('actions', function ($laboratory) {
                return view('admin.laboratories.actions', compact('laboratory'))->render();
            })
            ->rawColumns(['is_active', 'actions'])
            ->make(true);
    }
}
