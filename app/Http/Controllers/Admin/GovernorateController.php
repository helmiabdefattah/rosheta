<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Governorate;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class GovernorateController extends Controller
{
    public function index()
    {
        return view('admin.governorates.index');
    }

    public function create()
    {
        return view('admin.governorates.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        Governorate::create($validated);

        return redirect()->route('admin.governorates.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء المحافظة بنجاح' : 'Governorate created successfully');
    }

    public function show(Governorate $governorate)
    {
        return view('admin.governorates.show', compact('governorate'));
    }

    public function edit(Governorate $governorate)
    {
        return view('admin.governorates.edit', compact('governorate'));
    }

    public function update(Request $request, Governorate $governorate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $governorate->update($validated);

        return redirect()->route('admin.governorates.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم تحديث المحافظة بنجاح' : 'Governorate updated successfully');
    }

    public function destroy(Governorate $governorate)
    {
        $governorate->delete();

        return redirect()->route('admin.governorates.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم حذف المحافظة بنجاح' : 'Governorate deleted successfully');
    }

    public function data()
    {
        $governorates = Governorate::select('governorates.*');

        return DataTables::of($governorates)
            ->addColumn('cities_count', function ($governorate) {
                return $governorate->cities()->count();
            })
            ->addColumn('is_active', function ($governorate) {
                return $governorate->is_active 
                    ? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">' . (app()->getLocale() === 'ar' ? 'نشط' : 'Active') . '</span>'
                    : '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">' . (app()->getLocale() === 'ar' ? 'غير نشط' : 'Inactive') . '</span>';
            })
            ->addColumn('actions', function ($governorate) {
                return view('admin.governorates.actions', compact('governorate'))->render();
            })
            ->rawColumns(['is_active', 'actions'])
            ->make(true);
    }
}

