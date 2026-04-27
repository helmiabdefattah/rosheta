<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Governorate;
use Illuminate\Http\Request;

class GovernorateController extends Controller
{
    public function index(Request $request)
    {
        $query = Governorate::withCount('cities')->orderByDesc('id');

        if ($request->filled('search')) {
            $term = '%' . $request->string('search') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('id', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('name_ar', 'like', $term)
                    ->orWhere('sort_order', 'like', $term);
            });
        }

        $governorates = $query->paginate(15)->withQueryString();

        return view('admin.governorates.index', compact('governorates'));
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
}
