<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\City;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function index(Request $request)
    {
        $query = Area::with(['city.governorate'])->withCount('pharmacies')->orderByDesc('id');

        if ($request->filled('search')) {
            $term = '%' . $request->string('search') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('id', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('name_ar', 'like', $term)
                    ->orWhere('sort_order', 'like', $term)
                    ->orWhereHas('city', function ($q) use ($term) {
                        $q->where('name', 'like', $term)
                            ->orWhere('name_ar', 'like', $term)
                            ->orWhereHas('governorate', function ($q) use ($term) {
                                $q->where('name', 'like', $term)->orWhere('name_ar', 'like', $term);
                            });
                    });
            });
        }

        $areas = $query->paginate(15)->withQueryString();

        return view('admin.areas.index', compact('areas'));
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
}
