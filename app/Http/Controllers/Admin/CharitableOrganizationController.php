<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\CharitableOrganization;
use App\Models\City;
use App\Models\Governorate;
use Illuminate\Http\Request;

class CharitableOrganizationController extends Controller
{
    public function index(Request $request)
    {
        $query = CharitableOrganization::with(['governorate', 'city', 'area'])->orderByDesc('id');

        if ($request->filled('search')) {
            $term = '%' . $request->string('search') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('id', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('address', 'like', $term)
                    ->orWhereHas('governorate', function ($q) use ($term) {
                        $q->where('name', 'like', $term)->orWhere('name_ar', 'like', $term);
                    })
                    ->orWhereHas('city', function ($q) use ($term) {
                        $q->where('name', 'like', $term)->orWhere('name_ar', 'like', $term);
                    })
                    ->orWhereHas('area', function ($q) use ($term) {
                        $q->where('name', 'like', $term)->orWhere('name_ar', 'like', $term);
                    });
            });
        }

        $charitableOrganizations = $query->paginate(15)->withQueryString();

        return view('admin.charitable-organizations.index', compact('charitableOrganizations'));
    }

    public function create()
    {
        $governorates = Governorate::where('is_active', true)->orderBy('name')->get();
        return view('admin.charitable-organizations.create', compact('governorates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'governorate_id' => 'required|exists:governorates,id',
            'city_id' => 'required|exists:cities,id',
            'area_id' => 'required|exists:areas,id',
            'address' => 'required|string',
            'phone_numbers' => 'nullable|array',
            'phone_numbers.*' => 'nullable|string|max:20',
            'services' => 'nullable|array',
            'services.*' => 'nullable|string|max:255',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        if (isset($validated['phone_numbers'])) {
            $validated['phone_numbers'] = array_values(array_filter($validated['phone_numbers'], function ($phone) {
                return !empty(trim((string) $phone));
            }));
        }

        if (isset($validated['services'])) {
            $validated['services'] = array_values(array_filter($validated['services'], function ($service) {
                return !empty(trim((string) $service));
            }));
        }

        CharitableOrganization::create($validated);

        return redirect()->route('admin.charitable-organizations.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء المنظمة الخيرية بنجاح' : 'Charitable organization created successfully');
    }

    public function show(CharitableOrganization $charitableOrganization)
    {
        $charitableOrganization->load(['governorate', 'city', 'area']);
        return view('admin.charitable-organizations.show', compact('charitableOrganization'));
    }

    public function edit(CharitableOrganization $charitableOrganization)
    {
        $governorates = Governorate::where('is_active', true)->orderBy('name')->get();
        $cities = City::where('is_active', true)->orderBy('name')->get();
        $areas = $charitableOrganization->city_id
            ? Area::where('city_id', $charitableOrganization->city_id)->where('is_active', true)->orderBy('name')->get()
            : collect();
        return view('admin.charitable-organizations.edit', compact('charitableOrganization', 'governorates', 'cities', 'areas'));
    }

    public function update(Request $request, CharitableOrganization $charitableOrganization)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'governorate_id' => 'required|exists:governorates,id',
            'city_id' => 'required|exists:cities,id',
            'area_id' => 'required|exists:areas,id',
            'address' => 'required|string',
            'phone_numbers' => 'nullable|array',
            'phone_numbers.*' => 'nullable|string|max:20',
            'services' => 'nullable|array',
            'services.*' => 'nullable|string|max:255',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        if (isset($validated['phone_numbers'])) {
            $validated['phone_numbers'] = array_values(array_filter($validated['phone_numbers'], function ($phone) {
                return !empty(trim((string) $phone));
            }));
        }

        if (isset($validated['services'])) {
            $validated['services'] = array_values(array_filter($validated['services'], function ($service) {
                return !empty(trim((string) $service));
            }));
        }

        $charitableOrganization->update($validated);

        return redirect()->route('admin.charitable-organizations.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم تحديث المنظمة الخيرية بنجاح' : 'Charitable organization updated successfully');
    }

    public function destroy(CharitableOrganization $charitableOrganization)
    {
        $charitableOrganization->delete();

        return redirect()->route('admin.charitable-organizations.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم حذف المنظمة الخيرية بنجاح' : 'Charitable organization deleted successfully');
    }
}
