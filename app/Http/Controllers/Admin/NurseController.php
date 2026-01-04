<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Nurse;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class NurseController extends Controller
{
    public function index()
    {
        $nurses = Nurse::with(['user.media'])
            ->orderByDesc('id')
            ->paginate(15);

        // Build area map
        $allAreaIds = collect($nurses->items())
            ->flatMap(fn ($n) => is_array($n->area_ids) ? $n->area_ids : [])
            ->filter()
            ->unique()
            ->values();

        $areaMap = $allAreaIds->isNotEmpty()
            ? Area::with('city.governorate')
                ->whereIn('id', $allAreaIds)
                ->get()
                ->keyBy('id')
            : collect();

        return view('admin.nurses.index', compact('nurses', 'areaMap'));
    }

    public function create()
    {
        $areas = Area::with('city.governorate')
            ->where('is_active', true)
            ->get();

        return view('admin.nurses.create', compact('areas'));
    }

    public function store(Request $request)
    {
        $validatedUser = $request->validate([
            'name'         => 'required|string|max:255',
            'phone_number' => 'required|string|max:50|unique:users,phone_number',
            'email'        => 'nullable|email|max:255|unique:users,email',
            'avatar'       => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
        ]);

        $validatedNurse = $request->validate([
            'gender'              => 'nullable|in:male,female',
            'date_of_birth'       => 'nullable|date',
            'address'             => 'nullable|string',
            'status'              => 'required|in:active,inactive',
            'area_ids'            => 'nullable|array',
            'area_ids.*'          => 'integer|exists:areas,id',
            'qualification'       => 'nullable|in:bachelor,diploma,technical_institute',
            'education_place'     => 'nullable|string|max:255',
            'graduation_year'     => 'nullable|integer|min:1950|max:' . (date('Y') + 1),
            'years_of_experience' => 'nullable|integer|min:0|max:70',
            'current_workplace'   => 'nullable|string|max:255',
            'certifications'      => 'nullable|string',
            'skills'              => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $validatedUser, $validatedNurse) {

            // 1️⃣ Create User
            $user = User::create([
                'name'         => $validatedUser['name'],
                'phone_number' => $validatedUser['phone_number'],
                'email'        => $validatedUser['email'] ?? null,
                'password'     => Hash::make('password'),
            ]);

            // 2️⃣ Avatar (Spatie)
            if ($request->hasFile('avatar')) {
                $user
                    ->addMedia($request->file('avatar'))
                    ->toMediaCollection('avatar');
            }

            // 3️⃣ Create Nurse
            $nurse = Nurse::create([
                'user_id'             => $user->id,
                'gender'              => $validatedNurse['gender'] ?? null,
                'date_of_birth'       => $validatedNurse['date_of_birth'] ?? null,
                'address'             => $validatedNurse['address'] ?? null,
                'status'              => $validatedNurse['status'],
                'area_ids'            => $validatedNurse['area_ids'] ?? [],
                'qualification'       => $validatedNurse['qualification'] ?? null,
                'education_place'     => $validatedNurse['education_place'] ?? null,
                'graduation_year'     => $validatedNurse['graduation_year'] ?? null,
                'years_of_experience' => $validatedNurse['years_of_experience'] ?? null,
                'current_workplace'   => $validatedNurse['current_workplace'] ?? null,
                'certifications'      => $this->stringToArray($validatedNurse['certifications'] ?? null),
                'skills'              => $this->stringToArray($validatedNurse['skills'] ?? null),
            ]);

            // 4️⃣ Update user with nurse_id
            $user->update([
                'nurse_id' => $nurse->id
            ]);

        });

        return redirect()
            ->route('admin.nurses.index')
            ->with('success', __('Nurse created successfully'));
    }

// Optional: Create a client record for nurse login
    public function show(Nurse $nurse)
    {
        $nurse->load('user.media');
        return view('admin.nurses.show', compact('nurse'));
    }

    public function edit(Nurse $nurse)
    {
        $nurse->load('user.media');

        $areas = Area::with('city.governorate')
            ->where('is_active', true)
            ->get();

        return view('admin.nurses.edit', compact('nurse', 'areas'));
    }

    public function update(Request $request, Nurse $nurse)
    {
        $user = $nurse->user;

        $validatedUser = $request->validate([
            'name'         => 'required|string|max:255',
            'phone_number' => 'required|string|max:50|unique:users,phone_number,' . $user->id,
            'email'        => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'avatar'       => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
        ]);

        $validatedNurse = $request->validate([
            'gender'              => 'nullable|in:male,female',
            'date_of_birth'       => 'nullable|date',
            'address'             => 'nullable|string',
            'status'              => 'required|in:active,inactive',
            'area_ids'            => 'nullable|array',
            'area_ids.*'          => 'integer|exists:areas,id',
            'qualification'       => 'nullable|in:bachelor,diploma,technical_institute',
            'education_place'     => 'nullable|string|max:255',
            'graduation_year'     => 'nullable|integer|min:1950|max:' . (date('Y') + 1),
            'years_of_experience' => 'nullable|integer|min:0|max:70',
            'current_workplace'   => 'nullable|string|max:255',
            'certifications'      => 'nullable|string',
            'skills'              => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $user, $nurse, $validatedUser, $validatedNurse) {

            // Update User
            $user->update([
                'name'         => $validatedUser['name'],
                'phone_number' => $validatedUser['phone_number'],
                'email'        => $validatedUser['email'] ?? null,
            ]);

            // Update avatar (Spatie)
            if ($request->hasFile('avatar')) {
                $user->clearMediaCollection('avatar');

                $user
                    ->addMedia($request->file('avatar'))
                    ->toMediaCollection('avatar');
            }

            // Update Nurse
            $nurse->update([
                'gender'              => $validatedNurse['gender'] ?? null,
                'date_of_birth'       => $validatedNurse['date_of_birth'] ?? null,
                'address'             => $validatedNurse['address'] ?? null,
                'status'              => $validatedNurse['status'],
                'area_ids'            => $validatedNurse['area_ids'] ?? [],
                'qualification'       => $validatedNurse['qualification'] ?? null,
                'education_place'     => $validatedNurse['education_place'] ?? null,
                'graduation_year'     => $validatedNurse['graduation_year'] ?? null,
                'years_of_experience' => $validatedNurse['years_of_experience'] ?? null,
                'current_workplace'   => $validatedNurse['current_workplace'] ?? null,
                'certifications'      => $this->stringToArray($validatedNurse['certifications'] ?? null),
                'skills'              => $this->stringToArray($validatedNurse['skills'] ?? null),
            ]);
        });

        return redirect()
            ->route('admin.nurses.index')
            ->with('success', __('Nurse updated successfully'));
    }

    public function destroy(Nurse $nurse)
    {
        DB::transaction(function () use ($nurse) {
            $user = $nurse->user;

            $nurse->delete();

            if ($user) {
                $user->clearMediaCollection('avatar');
                $user->delete();
            }
        });

        return back()->with('success', __('Nurse deleted successfully'));
    }

    private function stringToArray(?string $value): ?array
    {
        if (!$value) return null;

        return array_values(
            array_filter(
                array_map('trim', preg_split('/[,\\n]+/', $value))
            )
        );
    }
}
