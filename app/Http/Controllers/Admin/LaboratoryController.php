<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Laboratory;
use App\Models\User;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class LaboratoryController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type', 'all');
        if (! in_array($type, ['all', 'test', 'radiology'], true)) {
            $type = 'all';
        }

        $query = Laboratory::with(['user', 'area.city.governorate'])->orderByDesc('id');

        if ($type === 'test') {
            $query->where('type', 'test');
        } elseif ($type === 'radiology') {
            $query->where('type', 'radiology');
        }

        if ($request->filled('search')) {
            $term = '%' . $request->string('search') . '%';
            $query->where(function ($q) use ($term) {
                $q->where('id', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhereHas('user', function ($q) use ($term) {
                        $q->where('name', 'like', $term)->orWhere('email', 'like', $term);
                    })
                    ->orWhereHas('area', function ($q) use ($term) {
                        $q->where('name', 'like', $term)
                            ->orWhere('name_ar', 'like', $term);
                    });
            });
        }

        $laboratories = $query->paginate(15)->withQueryString();

        return view('admin.laboratories.index', compact('laboratories', 'type'));
    }

    public function create()
    {
        $users = User::orderBy('name')->get();
        $areas = Area::with('city.governorate')->where('is_active', true)->get();

        return view('laboratories.create', compact('users', 'areas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:radiology,test',
            'user_id' => 'nullable|exists:users,id',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'area_id' => 'nullable|exists:areas,id',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'license_number' => 'nullable|string|max:255',
            'manager_name' => 'nullable|string|max:255',
            'manager_license' => 'nullable|string|max:255',
            'opening_time' => 'nullable|date_format:H:i',
            'closing_time' => 'nullable|date_format:H:i',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
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
            $labData = [
                'name' => $validated['name'],
                'type' => $validated['type'],
                'phone' => $validated['phone'] ?? null,
                'email' => $validated['email'] ?? null,
                'address' => $validated['address'] ?? null,
                'area_id' => $validated['area_id'] ?? null,
                'lat' => $validated['lat'] ?? null,
                'lng' => $validated['lng'] ?? null,
                'license_number' => $validated['license_number'] ?? null,
                'manager_name' => $validated['manager_name'] ?? null,
                'manager_license' => $validated['manager_license'] ?? null,
                'opening_time' => $validated['opening_time'] ?? null,
                'closing_time' => $validated['closing_time'] ?? null,
                'is_active' => $request->boolean('is_active'),
                'notes' => $validated['notes'] ?? null,
                'user_id' => null,
            ];
            if ($request->has('location') && is_array($request->location)) {
                $labData['location'] = $request->location;
            }
            $laboratory = Laboratory::create($labData);

            if (! empty($validated['user_id'])) {
                User::whereKey($validated['user_id'])->update(['laboratory_id' => $laboratory->id]);
                $laboratory->update(['user_id' => $validated['user_id']]);
            } else {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['account_email'],
                    'phone_number' => $validated['account_phone'],
                    'password' => $validated['password'],
                    'laboratory_id' => $laboratory->id,
                ]);
                $laboratory->update(['user_id' => $user->id]);
            }
        });

        return redirect()->route('admin.laboratories.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم إنشاء المعمل بنجاح' : 'Laboratory created successfully');
    }

    public function edit(Laboratory $laboratory)
    {
        $users = User::orderBy('name')->get();
        $areas = Area::with('city.governorate')->where('is_active', true)->get();

        return view('laboratories.edit', compact('laboratory', 'users', 'areas'));
    }

    public function update(Request $request, Laboratory $laboratory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:radiology,test',
            'user_id' => 'nullable|exists:users,id',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'area_id' => 'nullable|exists:areas,id',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'license_number' => 'nullable|string|max:255',
            'manager_name' => 'nullable|string|max:255',
            'manager_license' => 'nullable|string|max:255',
            'opening_time' => 'nullable|date_format:H:i',
            'closing_time' => 'nullable|date_format:H:i',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $oldUserId = $laboratory->user_id;

        DB::transaction(function () use ($request, $validated, $laboratory, $oldUserId) {
            $data = collect($validated)->except(['password'])->all();
            $data['is_active'] = $request->boolean('is_active');
            if ($request->has('location') && is_array($request->location)) {
                $data['location'] = $request->location;
            }
            $laboratory->update($data);

            $newUserId = $laboratory->fresh()->user_id;
            if ((int) $oldUserId !== (int) $newUserId) {
                if ($oldUserId) {
                    User::whereKey($oldUserId)->where('laboratory_id', $laboratory->id)->update(['laboratory_id' => null]);
                }
                if ($newUserId) {
                    User::whereKey($newUserId)->update(['laboratory_id' => $laboratory->id]);
                }
            }

            if ($request->filled('password') && $laboratory->user_id) {
                optional(User::find($laboratory->user_id))->update([
                    'password' => $validated['password'],
                ]);
            }
        });

        return redirect()->route('admin.laboratories.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم تحديث المعمل بنجاح' : 'Laboratory updated successfully');
    }

    public function destroy(Laboratory $laboratory)
    {
        $laboratory->delete();

        return redirect()->route('admin.laboratories.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم حذف المعمل بنجاح' : 'Laboratory deleted successfully');
    }
}
