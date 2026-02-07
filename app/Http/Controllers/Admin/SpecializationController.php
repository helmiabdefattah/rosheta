<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Specialization;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SpecializationController extends Controller
{
    public function index()
    {
        return view('admin.specializations.index');
    }

    public function create()
    {
        return view('admin.specializations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:specializations,slug',
            'brief' => 'nullable|string',
        ]);
        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }
        Specialization::create($validated);
        return redirect()->route('admin.specializations.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء التخصص بنجاح' : 'Specialization created successfully');
    }

    public function show(Specialization $specialization)
    {
        $specialization->loadCount('doctors');
        return view('admin.specializations.show', compact('specialization'));
    }

    public function edit(Specialization $specialization)
    {
        return view('admin.specializations.edit', compact('specialization'));
    }

    public function update(Request $request, Specialization $specialization)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:specializations,slug,' . $specialization->id,
            'brief' => 'nullable|string',
        ]);
        if (!empty($validated['slug'])) {
            $specialization->slug = $validated['slug'];
        } else {
            $validated['slug'] = Str::slug($validated['name']);
        }
        $specialization->update($validated);
        return redirect()->route('admin.specializations.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم تحديث التخصص بنجاح' : 'Specialization updated successfully');
    }

    public function destroy(Specialization $specialization)
    {
        $specialization->delete();
        return redirect()->route('admin.specializations.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم حذف التخصص' : 'Specialization deleted');
    }

    public function data()
    {
        $query = Specialization::query()->select('specializations.*');
        return \Yajra\DataTables\Facades\DataTables::of($query)
            ->addColumn('doctors_count', fn ($s) => $s->doctors()->count())
            ->addColumn('actions', fn ($s) => view('admin.specializations.actions', ['specialization' => $s])->render())
            ->rawColumns(['actions'])
            ->make(true);
    }
}
