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
        return view('laboratories.create', compact('users', 'areas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:radiology,test',
            'user_id' => 'nullable|exists:users,id',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'area_id' => 'nullable|exists:areas,id',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'license_number' => 'nullable|string|max:255',
            'manager_name' => 'nullable|string|max:255',
            'manager_license' => 'nullable|string|max:255',
            'opening_time' => 'nullable|date_format:H:i',
            'closing_time' => 'nullable|date_format:H:i',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        // Handle location array if provided
        if ($request->has('location') && is_array($request->location)) {
            $validated['location'] = $request->location;
        }

        Laboratory::create($validated);

        return redirect()->route('admin.laboratories.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء المعمل بنجاح' : 'Laboratory created successfully');
    }

    public function edit(Laboratory $laboratory)
    {
        $users = User::all();
        $areas = Area::with('city.governorate')->where('is_active', true)->get();
        return view('laboratories.edit', compact('laboratory', 'users', 'areas'));
    }

    public function update(Request $request, Laboratory $laboratory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:radiology,test',
            'user_id' => 'nullable|exists:users,id',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'area_id' => 'nullable|exists:areas,id',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'license_number' => 'nullable|string|max:255',
            'manager_name' => 'nullable|string|max:255',
            'manager_license' => 'nullable|string|max:255',
            'opening_time' => 'nullable|date_format:H:i',
            'closing_time' => 'nullable|date_format:H:i',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        // Handle location array if provided
        if ($request->has('location') && is_array($request->location)) {
            $validated['location'] = $request->location;
        }

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


    public function data(Request $request)
    {
        $query = Laboratory::query();

        if ($request->type === 'test') {
            $query->where('type', 'test');
        } elseif ($request->type === 'radiology') {
            $query->where('type', 'radiology');
        }


        return DataTables::of($query)
            ->addColumn('user_name', fn($lab) => $lab->user->name ?? '-')
            ->addColumn('area_name', fn($lab) => $lab->area->name ?? '-')
            ->addColumn('city_name', fn($lab) => $lab->area->city->name ?? '-')
            ->addColumn('governorate_name', fn($lab) => $lab->area->city->governorate->name ?? '-')
            ->addColumn('actions', fn($lab) => view('admin.laboratories.actions', ['laboratory' => $lab])->render())
            ->rawColumns(['actions', 'is_active'])
            ->make(true);
    }

}
