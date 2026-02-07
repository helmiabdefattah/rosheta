<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\CharitableOrganization;
use App\Models\City;
use App\Models\Governorate;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CharitableOrganizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('admin.charitable-organizations.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $governorates = Governorate::where('is_active', true)->orderBy('name')->get();
        return view('admin.charitable-organizations.create', compact('governorates'));
    }

    /**
     * Store a newly created resource in storage.
     */
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

        // Filter out empty phone numbers and services
        if (isset($validated['phone_numbers'])) {
            $validated['phone_numbers'] = array_filter($validated['phone_numbers'], function($phone) {
                return !empty(trim($phone));
            });
            $validated['phone_numbers'] = array_values($validated['phone_numbers']); // Re-index array
        }

        if (isset($validated['services'])) {
            $validated['services'] = array_filter($validated['services'], function($service) {
                return !empty(trim($service));
            });
            $validated['services'] = array_values($validated['services']); // Re-index array
        }

        CharitableOrganization::create($validated);

        return redirect()->route('admin.charitable-organizations.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء المنظمة الخيرية بنجاح' : 'Charitable organization created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(CharitableOrganization $charitableOrganization)
    {
        $charitableOrganization->load(['governorate', 'city', 'area']);
        return view('admin.charitable-organizations.show', compact('charitableOrganization'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CharitableOrganization $charitableOrganization)
    {
        $cities = City::where('is_active', true)->orderBy('name')->get();
        $areas = $charitableOrganization->city_id 
            ? Area::where('city_id', $charitableOrganization->city_id)->where('is_active', true)->orderBy('name')->get()
            : collect();
        return view('admin.charitable-organizations.edit', compact('charitableOrganization', 'cities', 'areas'));
    }

    /**
     * Update the specified resource in storage.
     */
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

        // Filter out empty phone numbers and services
        if (isset($validated['phone_numbers'])) {
            $validated['phone_numbers'] = array_filter($validated['phone_numbers'], function($phone) {
                return !empty(trim($phone));
            });
            $validated['phone_numbers'] = array_values($validated['phone_numbers']); // Re-index array
        }

        if (isset($validated['services'])) {
            $validated['services'] = array_filter($validated['services'], function($service) {
                return !empty(trim($service));
            });
            $validated['services'] = array_values($validated['services']); // Re-index array
        }

        $charitableOrganization->update($validated);

        return redirect()->route('admin.charitable-organizations.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم تحديث المنظمة الخيرية بنجاح' : 'Charitable organization updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CharitableOrganization $charitableOrganization)
    {
        $charitableOrganization->delete();

        return redirect()->route('admin.charitable-organizations.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم حذف المنظمة الخيرية بنجاح' : 'Charitable organization deleted successfully');
    }

    /**
     * Get data for DataTables.
     */
    public function data()
    {
        $organizations = CharitableOrganization::with(['governorate', 'city', 'area'])->select('charitable_organizations.*');

        return DataTables::of($organizations)
            ->addColumn('location', function ($organization) {
                $location = [];
                if ($organization->governorate) {
                    $location[] = app()->getLocale() === 'ar' ? ($organization->governorate->name_ar ?? $organization->governorate->name) : ($organization->governorate->name ?? $organization->governorate->name_ar);
                }
                if ($organization->city) {
                    $location[] = app()->getLocale() === 'ar' ? ($organization->city->name_ar ?? $organization->city->name) : ($organization->city->name ?? $organization->city->name_ar);
                }
                if ($organization->area) {
                    $location[] = app()->getLocale() === 'ar' ? ($organization->area->name_ar ?? $organization->area->name) : ($organization->area->name ?? $organization->area->name_ar);
                }
                return !empty($location) ? implode(', ', $location) : '-';
            })
            ->addColumn('phone_numbers_display', function ($organization) {
                $phones = $organization->phone_numbers ?? [];
                if (empty($phones)) {
                    return '-';
                }
                return implode(', ', $phones);
            })
            ->addColumn('services_display', function ($organization) {
                $services = $organization->services ?? [];
                if (empty($services)) {
                    return '-';
                }
                return implode(', ', $services);
            })
            ->addColumn('actions', function ($organization) {
                return view('admin.charitable-organizations.actions', compact('organization'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }
}
