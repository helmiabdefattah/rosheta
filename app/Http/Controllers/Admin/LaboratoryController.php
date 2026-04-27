<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laboratory;
use App\Models\User;
use App\Models\Area;
use Illuminate\Http\Request;

class LaboratoryController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type', 'all');
        if (! in_array($type, ['all', 'test', 'radiology'], true)) {
            $type = 'all';
        }

        $query = Laboratory::with(['user', 'area.city.governorate'])->orderByDesc('id');

        if ($type === 'test') {
            $query->where('type', 'test');
        } elseif ($type === 'radiology') {
            $query->where('type', 'radiology');
        }

        if ($request->filled('search')) {
            $term = '%' . $request->string('search') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('id', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhereHas('user', function ($q) use ($term) {
                        $q->where('name', 'like', $term)->orWhere('email', 'like', $term);
                    })
                    ->orWhereHas('area', function ($q) use ($term) {
                        $q->where('name', 'like', $term)
                            ->orWhere('name_ar', 'like', $term);
                    });
            });
        }

        $laboratories = $query->paginate(15)->withQueryString();

        return view('admin.laboratories.index', compact('laboratories', 'type'));
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
}
