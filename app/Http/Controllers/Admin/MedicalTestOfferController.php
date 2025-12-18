<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MedicalTestOffer;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class MedicalTestOfferController extends Controller
{
    public function index()
    {
        return view('admin.medical-test-offers.index');
    }

    public function show(MedicalTestOffer $medicalTestOffer)
    {
        $medicalTestOffer->load(['laboratory', 'medicalTest', 'lines']);
        return view('admin.medical-test-offers.show', compact('medicalTestOffer'));
    }

    public function destroy(MedicalTestOffer $medicalTestOffer)
    {
        $medicalTestOffer->delete();

        return redirect()->route('admin.medical-test-offers.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم حذف العرض بنجاح' : 'Test offer deleted successfully');
    }

    public function data()
    {
        $offers = MedicalTestOffer::with(['laboratory', 'medicalTest'])->select('medical_test_offers.*');

        return DataTables::of($offers)
            ->addColumn('laboratory_name', function ($offer) {
                return $offer->laboratory->name ?? '-';
            })
            ->addColumn('test_name', function ($offer) {
                return $offer->medicalTest->test_name_en ?? '-';
            })
            ->addColumn('price_formatted', function ($offer) {
                return $offer->price ? 'EGP ' . number_format($offer->price, 2) : '-';
            })
            ->addColumn('actions', function ($offer) {
                return view('admin.medical-test-offers.actions', compact('offer'))->render();
            })
            ->rawColumns(['actions'])
            ->make(true);
    }
}

