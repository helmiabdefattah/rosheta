<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class MedicineController extends Controller
{
    public function index()
    {
        return view('admin.medicines.index');
    }

    public function create()
    {
        return view('admin.medicines.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'arabic' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'company' => 'nullable|string|max:255',
            'active_ingredient' => 'nullable|string|max:255',
            'dosage_form' => 'nullable|string|max:255',
            'route' => 'nullable|string|max:255',
        ]);

        Medicine::create($validated);

        return redirect()->route('admin.medicines.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء الدواء بنجاح' : 'Medicine created successfully');
    }

    public function show(Medicine $medicine)
    {
        return view('admin.medicines.show', compact('medicine'));
    }

    public function edit(Medicine $medicine)
    {
        return view('admin.medicines.edit', compact('medicine'));
    }

    public function update(Request $request, Medicine $medicine)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'arabic' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'company' => 'nullable|string|max:255',
            'active_ingredient' => 'nullable|string|max:255',
            'dosage_form' => 'nullable|string|max:255',
            'route' => 'nullable|string|max:255',
        ]);

        $medicine->update($validated);

        return redirect()->route('admin.medicines.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم تحديث الدواء بنجاح' : 'Medicine updated successfully');
    }

    public function destroy(Medicine $medicine)
    {
        $medicine->delete();

        return redirect()->route('admin.medicines.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم حذف الدواء بنجاح' : 'Medicine deleted successfully');
    }

    public function data()
    {
        $medicines = Medicine::select('medicines.*');

        return DataTables::of($medicines)
            ->addColumn('price_formatted', function ($medicine) {
                return $medicine->price ? 'EGP ' . number_format($medicine->price, 2) : '-';
            })
            ->addColumn('actions', function ($medicine) {
                return view('admin.medicines.actions', compact('medicine'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }
}

