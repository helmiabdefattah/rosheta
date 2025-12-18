<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\City;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AreaController extends Controller
{
    public function index()
    {
        return view('admin.areas.index');
    }

    public function create()
    {
        $cities = City::where('is_active', true)->orderBy('name')->get();
        return view('admin.areas.create', compact('cities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        Area::create($validated);

        return redirect()->route('admin.areas.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء المنطقة بنجاح' : 'Area created successfully');
    }

    public function show(Area $area)
    {
        return view('admin.areas.show', compact('area'));
    }

    public function edit(Area $area)
    {
        $cities = City::where('is_active', true)->orderBy('name')->get();
        return view('admin.areas.edit', compact('area', 'cities'));
    }

    public function update(Request $request, Area $area)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'city_id' => 'required|exists:cities,id',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $area->update($validated);

        return redirect()->route('admin.areas.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم تحديث المنطقة بنجاح' : 'Area updated successfully');
    }

    public function destroy(Area $area)
    {
        $area->delete();

        return redirect()->route('admin.areas.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم حذف المنطقة بنجاح' : 'Area deleted successfully');
    }

    public function data()
    {
        $areas = Area::with(['city.governorate'])->select('areas.*');

        return DataTables::of($areas)
            ->addColumn('city_name', function ($area) {
                return $area->city->name ?? 'N/A';
            })
            ->addColumn('governorate_name', function ($area) {
                return $area->city->governorate->name ?? 'N/A';
            })
            ->addColumn('pharmacies_count', function ($area) {
                return $area->pharmacies()->count();
            })
            ->addColumn('is_active', function ($area) {
                return $area->is_active 
                    ? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">' . (app()->getLocale() === 'ar' ? 'نشط' : 'Active') . '</span>'
                    : '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">' . (app()->getLocale() === 'ar' ? 'غير نشط' : 'Inactive') . '</span>';
            })
            ->addColumn('actions', function ($area) {
                return view('admin.areas.actions', compact('area'))->render();
            })
            ->rawColumns(['is_active', 'actions'])
            ->make(true);
    }
}

