<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Governorate;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CityController extends Controller
{
    public function index()
    {
        return view('admin.cities.index');
    }

    public function create()
    {
        $governorates = Governorate::where('is_active', true)->orderBy('name')->get();
        return view('admin.cities.create', compact('governorates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'governorate_id' => 'required|exists:governorates,id',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        City::create($validated);

        return redirect()->route('admin.cities.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء المدينة بنجاح' : 'City created successfully');
    }

    public function show(City $city)
    {
        return view('admin.cities.show', compact('city'));
    }

    public function edit(City $city)
    {
        $governorates = Governorate::where('is_active', true)->orderBy('name')->get();
        return view('admin.cities.edit', compact('city', 'governorates'));
    }

    public function update(Request $request, City $city)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
            'governorate_id' => 'required|exists:governorates,id',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $city->update($validated);

        return redirect()->route('admin.cities.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم تحديث المدينة بنجاح' : 'City updated successfully');
    }

    public function destroy(City $city)
    {
        $city->delete();

        return redirect()->route('admin.cities.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم حذف المدينة بنجاح' : 'City deleted successfully');
    }

    public function data()
    {
        $cities = City::with('governorate')->select('cities.*');

        return DataTables::of($cities)
            ->addColumn('governorate_name', function ($city) {
                return $city->governorate->name ?? 'N/A';
            })
            ->addColumn('areas_count', function ($city) {
                return $city->areas()->count();
            })
            ->addColumn('is_active', function ($city) {
                return $city->is_active 
                    ? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">' . (app()->getLocale() === 'ar' ? 'نشط' : 'Active') . '</span>'
                    : '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">' . (app()->getLocale() === 'ar' ? 'غير نشط' : 'Inactive') . '</span>';
            })
            ->addColumn('actions', function ($city) {
                return view('admin.cities.actions', compact('city'))->render();
            })
            ->rawColumns(['is_active', 'actions'])
            ->make(true);
    }
}

