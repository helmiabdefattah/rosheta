<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MedicalTest;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class MedicalTestController extends Controller
{
    public function index()
    {
        $defaultType = 'test';

        return view('admin.medical-tests.index', compact('defaultType'));
    }


    public function create()
    {
        return view('admin.medical-tests.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'test_name_en' => 'required|string|max:255',
            'test_name_ar' => 'required|string|max:255',
            'test_description' => 'nullable|string',
            'conditions' => 'nullable|string',
        ]);

        MedicalTest::create($validated);

        return redirect()->route('admin.medical-tests.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء الفحص الطبي بنجاح' : 'Medical test created successfully');
    }

    public function show(MedicalTest $medicalTest)
    {
        return view('admin.medical-tests.show', compact('medicalTest'));
    }

    public function edit(MedicalTest $medicalTest)
    {
        return view('admin.medical-tests.edit', compact('medicalTest'));
    }

    public function update(Request $request, MedicalTest $medicalTest)
    {
        $validated = $request->validate([
            'test_name_en' => 'required|string|max:255',
            'test_name_ar' => 'required|string|max:255',
            'test_description' => 'nullable|string',
            'conditions' => 'nullable|string',
        ]);

        $medicalTest->update($validated);

        return redirect()->route('admin.medical-tests.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم تحديث الفحص الطبي بنجاح' : 'Medical test updated successfully');
    }

    public function destroy(MedicalTest $medicalTest)
    {
        $medicalTest->delete();

        return redirect()->route('admin.medical-tests.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم حذف الفحص الطبي بنجاح' : 'Medical test deleted successfully');
    }

    public function data(Request $request)
    {
        $query = MedicalTest::query();

        // Filter by type if provided
        if ($request->has('type') && in_array($request->type, ['test', 'radiology'])) {
            $query->where('type', $request->type);
        }

        return DataTables::of($query)
            ->addColumn('actions', function ($row) {
                return view('admin.medical-tests.actions', ['medicalTest' => $row])->render();
            })
            ->rawColumns(['actions']) // <-- Tell DataTables this column contains raw HTML
            ->make(true);
    }

    }

