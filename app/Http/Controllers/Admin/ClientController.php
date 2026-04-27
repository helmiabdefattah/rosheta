<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::with(['addresses.city', 'addresses.area'])->orderByDesc('id');

        if ($request->filled('search')) {
            $term = '%' . $request->string('search') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('id', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('phone_number', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhereHas('addresses', function ($q) use ($term) {
                        $q->where('address', 'like', $term)
                            ->orWhereHas('city', function ($q) use ($term) {
                                $q->where('name', 'like', $term)->orWhere('name_ar', 'like', $term);
                            })
                            ->orWhereHas('area', function ($q) use ($term) {
                                $q->where('name', 'like', $term)->orWhere('name_ar', 'like', $term);
                            });
                    });
            });
        }

        $clients = $query->paginate(15)->withQueryString();

        return view('admin.clients.index', compact('clients'));
    }

    public function create()
    {
        return view('admin.clients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'email' => 'nullable|email|max:255|unique:clients',
            'password' => 'required|string|min:8',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        Client::create($validated);

        return redirect()->route('admin.clients.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء العميل بنجاح' : 'Client created successfully');
    }

    public function show(Client $client)
    {
        $client->load(['addresses.city', 'addresses.area', 'requests']);
        return view('admin.clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        return view('admin.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'email' => 'nullable|email|max:255|unique:clients,email,' . $client->id,
            'password' => 'nullable|string|min:8',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $client->update($validated);

        return redirect()->route('admin.clients.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم تحديث العميل بنجاح' : 'Client updated successfully');
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->route('admin.clients.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم حذف العميل بنجاح' : 'Client deleted successfully');
    }
}
