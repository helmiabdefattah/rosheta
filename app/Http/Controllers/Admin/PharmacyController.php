<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use App\Models\Area;
use App\Models\User;
use Illuminate\Http\Request;

class PharmacyController extends Controller
{
    public function index(Request $request)
    {
        $query = Pharmacy::with(['user', 'area.city.governorate'])->orderByDesc('id');

        if ($request->filled('search')) {
            $term = '%' . $request->string('search') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('id', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhereHas('area', function ($q) use ($term) {
                        $q->where('name', 'like', $term)
                            ->orWhere('name_ar', 'like', $term);
                    });
            });
        }

        $pharmacies = $query->paginate(15)->withQueryString();

        return view('admin.pharmacies.index', compact('pharmacies'));
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
}

