<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientRequest;
use App\Models\Client;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ClientRequestController extends Controller
{
    public function index()
    {
        return view('admin.client-requests.index');
    }

    public function show(ClientRequest $clientRequest)
    {
        $clientRequest->load(['client', 'address', 'lines.medicine', 'lines.medicalTest', 'offers']);
        return view('admin.client-requests.show', compact('clientRequest'));
    }

    public function destroy(ClientRequest $clientRequest)
    {
        $clientRequest->delete();

        return redirect()->route('admin.client-requests.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم حذف الطلب بنجاح' : 'Request deleted successfully');
    }

    public function data()
    {
        $requests = ClientRequest::with(['client', 'address'])->select('client_requests.*');

        return DataTables::of($requests)
            ->addColumn('client_name', function ($request) {
                return $request->client->name ?? '-';
            })
            ->addColumn('address_text', function ($request) {
                return $request->address->address ?? '-';
            })
            ->addColumn('status_badge', function ($request) {
                $colors = [
                    'pending' => 'bg-yellow-100 text-yellow-800',
                    'approved' => 'bg-green-100 text-green-800',
                    'rejected' => 'bg-red-100 text-red-800',
                ];
                $color = $colors[$request->status] ?? 'bg-gray-100 text-gray-800';
                return '<span class="px-2 py-1 text-xs font-semibold rounded-full ' . $color . '">' . ucfirst($request->status) . '</span>';
            })
            ->addColumn('actions', function ($request) {
                return view('admin.client-requests.actions', compact('request'))->render();
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }
}

