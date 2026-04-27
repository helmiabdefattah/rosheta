<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pharmacy;
use App\Models\Area;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class PharmacyController extends Controller
{
    public function index(Request $request)
    {
        $query = Pharmacy::with(['user', 'area.city.governorate'])->orderByDesc('id');

        if ($request->filled('search')) {
            $term = '%' . $request->string('search') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('id', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhereHas('area', function ($q) use ($term) {
                        $q->where('name', 'like', $term)
                            ->orWhere('name_ar', 'like', $term);
                    });
            });
        }

        $pharmacies = $query->paginate(15)->withQueryString();

        return view('admin.pharmacies.index', compact('pharmacies'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get();
        $areas = Area::with('city.governorate')->where('is_active', true)->get();

        return view('admin.pharmacies.create', compact('users', 'areas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'area_id' => 'nullable|exists:areas,id',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'is_active' => 'boolean',
            'account_email' => 'nullable|email|unique:users,email',
            'account_phone' => 'nullable|string|max:50|unique:users,phone_number',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        if (empty($validated['user_id'])) {
            $request->validate([
                'account_email' => 'required|email|unique:users,email',
                'account_phone' => 'required|string|max:50|unique:users,phone_number',
                'password' => 'required|string|min:8|confirmed',
            ]);
        }

        DB::transaction(function () use ($request, $validated) {
            $pharmacyData = [
                'name' => $validated['name'],
                'area_id' => $validated['area_id'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'address' => $validated['address'] ?? null,
                'lat' => $validated['lat'] ?? null,
                'lng' => $validated['lng'] ?? null,
                'is_active' => $request->boolean('is_active'),
                'user_id' => null,
            ];
            $pharmacy = Pharmacy::create($pharmacyData);

            if (! empty($validated['user_id'])) {
                User::whereKey($validated['user_id'])->update(['pharmacy_id' => $pharmacy->id]);
                $pharmacy->update(['user_id' => $validated['user_id']]);
            } else {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['account_email'],
                    'phone_number' => $validated['account_phone'],
                    'password' => $validated['password'],
                    'pharmacy_id' => $pharmacy->id,
                ]);
                $pharmacy->update(['user_id' => $user->id]);
            }
        });

        return redirect()->route('admin.pharmacies.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء الصيدلية بنجاح' : 'Pharmacy created successfully');
    }

    public function show(Pharmacy $pharmacy)
    {
        return view('admin.pharmacies.show', compact('pharmacy'));
    }

    public function edit(Pharmacy $pharmacy)
    {
        $users = User::orderBy('name')->get();
        $areas = Area::with('city.governorate')->where('is_active', true)->get();

        return view('admin.pharmacies.edit', compact('pharmacy', 'users', 'areas'));
    }

    public function update(Request $request, Pharmacy $pharmacy)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'area_id' => 'nullable|exists:areas,id',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'is_active' => 'boolean',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $oldUserId = $pharmacy->user_id;

        DB::transaction(function () use ($request, $validated, $pharmacy, $oldUserId) {
            $pharmacy->update([
                'name' => $validated['name'],
                'user_id' => $validated['user_id'] ?? null,
                'area_id' => $validated['area_id'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'address' => $validated['address'] ?? null,
                'lat' => $validated['lat'] ?? null,
                'lng' => $validated['lng'] ?? null,
                'is_active' => $request->boolean('is_active'),
            ]);

            $newUserId = $pharmacy->fresh()->user_id;
            if ((int) $oldUserId !== (int) $newUserId) {
                if ($oldUserId) {
                    User::whereKey($oldUserId)->where('pharmacy_id', $pharmacy->id)->update(['pharmacy_id' => null]);
                }
                if ($newUserId) {
                    User::whereKey($newUserId)->update(['pharmacy_id' => $pharmacy->id]);
                }
            }

            if ($request->filled('password') && $pharmacy->user_id) {
                optional(User::find($pharmacy->user_id))->update([
                    'password' => $validated['password'],
                ]);
            }
        });

        return redirect()->route('admin.pharmacies.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم تحديث الصيدلية بنجاح' : 'Pharmacy updated successfully');
    }

    public function destroy(Pharmacy $pharmacy)
    {
        $pharmacy->delete();

        return redirect()->route('admin.pharmacies.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم حذف الصيدلية بنجاح' : 'Pharmacy deleted successfully');
    }
}
