<?php

namespace App\Http\Controllers;

use App\Models\ClientRequest;
use App\Models\Laboratory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LaboratoryRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $laboratory = Laboratory::find($user->laboratory_id);

        if (!$laboratory) {
            return redirect()->route('admin.dashboard')
                ->with('error', app()->getLocale() === 'ar' ? 'أنت غير مرتبط بأي معمل.' : 'You are not associated with any laboratory.');
        }


        $requestTypes = $laboratory->type === 'test' ? ['test'] :
            ($laboratory->type === 'radiology' ? ['radiology'] : ['test', 'radiology']);

        // Get all requests that match laboratory's type(s) and don't have offers from this laboratory
        $query = ClientRequest::whereIn('type', $requestTypes)
            ->whereDoesntHave('offers', function($q) use ($laboratory) {
                $q->where('laboratory_id', $laboratory->id);
            })
            ->with([
                'client',
                'address.area.city.governorate',
                'lines' => function($q) use ($requestTypes) {
                    $q->whereIn('item_type', $requestTypes)->with('medicalTest');
                },
                'offers' => function($q) use ($laboratory) {
                    $q->where('laboratory_id', $laboratory->id);
                }
            ])
            ->orderBy('created_at', 'desc');

        // Filter by status if provided
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by search if provided
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhereHas('client', function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('phone_number', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('request_type') && $request->request_type && in_array($request->request_type, $requestTypes)) {
            $query->where('type', $request->request_type);
        }
        $requests = $query->paginate(15);
//        dd($requests);

        return view('laboratories.requests.index', compact('requests', 'laboratory'));
    }}

