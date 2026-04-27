<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Governorate;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index(Request $request)
    {
        $query = City::with('governorate')->withCount('areas')->orderByDesc('id');

        if ($request->filled('search')) {
            $term = '%' . $request->string('search') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('id', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('name_ar', 'like', $term)
                    ->orWhere('sort_order', 'like', $term)
                    ->orWhereHas('governorate', function ($q) use ($term) {
                        $q->where('name', 'like', $term)->orWhere('name_ar', 'like', $term);
                    });
            });
        }

        $cities = $query->paginate(15)->withQueryString();

        return view('admin.cities.index', compact('cities'));
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
}
