<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientRequest;
use Illuminate\Http\Request;

class ClientRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = ClientRequest::with(['client', 'address'])
            ->orderByDesc('id');

        if ($request->filled('search')) {
            $term = '%' . $request->string('search') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('id', 'like', $term)
                    ->orWhere('status', 'like', $term)
                    ->orWhere('type', 'like', $term)
                    ->orWhereHas('client', function ($q) use ($term) {
                        $q->where('name', 'like', $term);
                    })
                    ->orWhereHas('address', function ($q) use ($term) {
                        $q->where('address', 'like', $term);
                    });
            });
        }

        $clientRequests = $query->paginate(15)->withQueryString();

        return view('admin.client-requests.index', compact('clientRequests'));
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
}
