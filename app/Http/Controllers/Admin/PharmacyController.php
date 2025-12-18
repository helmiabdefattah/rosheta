<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use App\Models\Area;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PharmacyController extends Controller
{
    public function index()
    {
        return view('admin.pharmacies.index');
    }

    public function create()
    {
        $users = User::all();
        $areas = Area::with('city.governorate')->where('is_active', true)->get();
        return view('admin.pharmacies.create', compact('users', 'areas'));
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

        Pharmacy::create($validated);

        return redirect()->route('admin.pharmacies.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء الصيدلية بنجاح' : 'Pharmacy created successfully');
    }

    public function show(Pharmacy $pharmacy)
    {
        return view('admin.pharmacies.show', compact('pharmacy'));
    }

    public function edit(Pharmacy $pharmacy)
    {
        $users = User::all();
        $areas = Area::with('city.governorate')->where('is_active', true)->get();
        return view('admin.pharmacies.edit', compact('pharmacy', 'users', 'areas'));
    }

    public function update(Request $request, Pharmacy $pharmacy)
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

        $pharmacy->update($validated);

        return redirect()->route('admin.pharmacies.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم تحديث الصيدلية بنجاح' : 'Pharmacy updated successfully');
    }

    public function destroy(Pharmacy $pharmacy)
    {
        $pharmacy->delete();

        return redirect()->route('admin.pharmacies.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم حذف الصيدلية بنجاح' : 'Pharmacy deleted successfully');
    }

    public function data()
    {
        $pharmacies = Pharmacy::with(['user', 'area.city.governorate'])->select('pharmacies.*');

        return DataTables::of($pharmacies)
            ->addColumn('area_name', function ($pharmacy) {
                return $pharmacy->area->name ?? '-';
            })
            ->addColumn('city_name', function ($pharmacy) {
                return $pharmacy->area->city->name ?? '-';
            })
            ->addColumn('governorate_name', function ($pharmacy) {
                return $pharmacy->area->city->governorate->name ?? '-';
            })
            ->addColumn('is_active', function ($pharmacy) {
                return $pharmacy->is_active 
                    ? '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">' . (app()->getLocale() === 'ar' ? 'نشط' : 'Active') . '</span>'
                    : '<span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">' . (app()->getLocale() === 'ar' ? 'غير نشط' : 'Inactive') . '</span>';
            })
            ->addColumn('actions', function ($pharmacy) {
                return view('admin.pharmacies.actions', compact('pharmacy'))->render();
            })
            ->rawColumns(['is_active', 'actions'])
            ->make(true);
    }
}

