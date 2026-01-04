<?php

namespace App\Http\Controllers;

use App\Models\Nurse;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NurseController extends Controller
{
    public function index()
    {
        $nurses = Nurse::with('user')->orderByDesc('id')->paginate(15);

        $allAreaIds = collect($nurses->items())
            ->flatMap(fn ($n) => is_array($n->area_ids) ? $n->area_ids : [])
            ->filter()
            ->unique()
            ->values();

        $areaMap = $allAreaIds->isNotEmpty()
            ? Area::with('city.governorate')->whereIn('id', $allAreaIds)->get()->keyBy('id')
            : collect();

        return view('admin.nurses.index', compact('nurses', 'areaMap'));
    }

    public function show(Nurse $nurse)
    {
        $nurse->load('user');
        return view('admin.nurses.show', compact('nurse'));
    }

    public function edit(Nurse $nurse)
    {
        $nurse->load('user');
        $areas = Area::with('city.governorate')
            ->where('is_active', true)
            ->get();

        return view('nurse.edit', compact('nurse', 'areas'));
    }

    public function update(Request $request, Nurse $nurse)
    {
        $user = $nurse->user;

        /** USER VALIDATION */
        $validatedUser = $request->validate([
            'name'         => 'required|string|max:255',
            'phone_number' => 'required|string|max:50|unique:users,phone_number,' . $user->id,
            'email'        => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'avatar'       => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
        ]);

        /** NURSE VALIDATION */
        $validatedNurse = $request->validate([
            'gender'               => 'nullable|in:male,female',
            'date_of_birth'        => 'nullable|date',
            'address'              => 'nullable|string',
            'area_ids'             => 'nullable|array',
            'area_ids.*'           => 'integer|exists:areas,id',
            'qualification'        => 'nullable|in:bachelor,diploma,technical_institute',
            'education_place'      => 'nullable|string|max:255',
            'graduation_year'      => 'nullable|integer|min:1950|max:' . (date('Y') + 1),
            'years_of_experience'  => 'nullable|integer|min:0|max:70',
            'current_workplace'    => 'nullable|string|max:255',
            'certifications'       => 'nullable|string',
            'skills'               => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $user, $nurse, $validatedUser, $validatedNurse) {

            /** UPDATE USER DATA */
            $user->update([
                'name'         => $validatedUser['name'],
                'phone_number' => $validatedUser['phone_number'],
                'email'        => $validatedUser['email'] ?? null,
            ]);

            /** AVATAR VIA SPATIE MEDIA LIBRARY */
            if ($request->hasFile('avatar')) {
                $user->clearMediaCollection('avatar');
                $user
                    ->addMediaFromRequest('avatar')
                    ->toMediaCollection('avatar');
            }

            /** UPDATE NURSE DATA */
            $nurse->update([
                'gender'              => $validatedNurse['gender'] ?? null,
                'date_of_birth'       => $validatedNurse['date_of_birth'] ?? null,
                'address'             => $validatedNurse['address'] ?? null,
                'area_ids'            => $validatedNurse['area_ids'] ?? null,
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
            ->route('client.nurse.dashboard')
            ->with(
                'success',
                app()->getLocale() === 'ar'
                    ? 'تم تحديث بيانات الممرض/ة'
                    : 'Nurse updated successfully'
            );
    }

    public function destroy(Nurse $nurse)
    {
        $user = $nurse->user;

        DB::transaction(function () use ($nurse, $user) {
            $nurse->delete();

            if ($user) {
                $user->clearMediaCollection('avatar');
                $user->delete();
            }
        });

        return back()->with(
            'success',
            app()->getLocale() === 'ar'
                ? 'تم حذف الممرض/ة'
                : 'Nurse deleted successfully'
        );
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
