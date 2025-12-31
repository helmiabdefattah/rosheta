<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Nurse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class NurseController extends Controller
{
	public function index()
	{
		$nurses = Nurse::with('client')->orderByDesc('id')->paginate(15);
		return view('admin.nurses.index', compact('nurses'));
	}

	public function create()
	{
		return view('admin.nurses.create');
	}

	public function store(Request $request)
	{
		$validatedClient = $request->validate([
			'name' => 'required|string|max:255',
			'phone_number' => 'required|string|max:50|unique:clients,phone_number',
			'email' => 'nullable|email|max:255|unique:clients,email',
			'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
		]);

		$validatedNurse = $request->validate([
			'gender' => 'nullable|in:male,female',
			'date_of_birth' => 'nullable|date',
			'address' => 'nullable|string',
			'qualification' => 'nullable|in:bachelor,diploma,technical_institute',
			'education_place' => 'nullable|string|max:255',
			'graduation_year' => 'nullable|integer|min:1950|max:' . (date('Y') + 1),
			'years_of_experience' => 'nullable|integer|min:0|max:70',
			'current_workplace' => 'nullable|string|max:255',
			'certifications' => 'nullable|string',
			'skills' => 'nullable|string',
		]);

		DB::transaction(function () use ($request, $validatedClient, $validatedNurse) {
			$avatarPath = null;
			if ($request->hasFile('avatar')) {
				$avatarPath = $request->file('avatar')->store('clients/avatars', 'public');
			}

			$client = Client::create([
				'name' => $validatedClient['name'],
				'phone_number' => $validatedClient['phone_number'],
				'email' => $validatedClient['email'] ?? null,
				'avatar' => $avatarPath,
				// generate random password; can be reset later
				'password' => Hash::make(str()->random(12)),
			]);

			Nurse::create([
				'client_id' => $client->id,
				'gender' => $validatedNurse['gender'] ?? null,
				'date_of_birth' => $validatedNurse['date_of_birth'] ?? null,
				'address' => $validatedNurse['address'] ?? null,
				'qualification' => $validatedNurse['qualification'] ?? null,
				'education_place' => $validatedNurse['education_place'] ?? null,
				'graduation_year' => $validatedNurse['graduation_year'] ?? null,
				'years_of_experience' => $validatedNurse['years_of_experience'] ?? null,
				'current_workplace' => $validatedNurse['current_workplace'] ?? null,
				'certifications' => $this->stringToArray($validatedNurse['certifications'] ?? null),
				'skills' => $this->stringToArray($validatedNurse['skills'] ?? null),
			]);
		});

		return redirect()->route('admin.nurses.index')
			->with('success', app()->getLocale() === 'ar' ? 'تم إضافة الممرض/ة بنجاح' : 'Nurse created successfully');
	}

	public function show(Nurse $nurse)
	{
		$nurse->load('client');
		return view('admin.nurses.show', compact('nurse'));
	}

	public function edit(Nurse $nurse)
	{
		$nurse->load('client');
		return view('admin.nurses.edit', compact('nurse'));
	}

	public function update(Request $request, Nurse $nurse)
	{
		$client = $nurse->client;

		$validatedClient = $request->validate([
			'name' => 'required|string|max:255',
			'phone_number' => 'required|string|max:50|unique:clients,phone_number,' . $client->id,
			'email' => 'nullable|email|max:255|unique:clients,email,' . $client->id,
			'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
		]);

		$validatedNurse = $request->validate([
			'gender' => 'nullable|in:male,female',
			'date_of_birth' => 'nullable|date',
			'address' => 'nullable|string',
			'qualification' => 'nullable|in:bachelor,diploma,technical_institute',
			'education_place' => 'nullable|string|max:255',
			'graduation_year' => 'nullable|integer|min:1950|max:' . (date('Y') + 1),
			'years_of_experience' => 'nullable|integer|min:0|max:70',
			'current_workplace' => 'nullable|string|max:255',
			'certifications' => 'nullable|string',
			'skills' => 'nullable|string',
		]);

		DB::transaction(function () use ($request, $client, $nurse, $validatedClient, $validatedNurse) {
			if ($request->hasFile('avatar')) {
				if ($client->avatar) {
					Storage::disk('public')->delete($client->avatar);
				}
				$client->avatar = $request->file('avatar')->store('clients/avatars', 'public');
			}
			$client->name = $validatedClient['name'];
			$client->phone_number = $validatedClient['phone_number'];
			$client->email = $validatedClient['email'] ?? null;
			$client->save();

			$nurse->update([
				'gender' => $validatedNurse['gender'] ?? null,
				'date_of_birth' => $validatedNurse['date_of_birth'] ?? null,
				'address' => $validatedNurse['address'] ?? null,
				'qualification' => $validatedNurse['qualification'] ?? null,
				'education_place' => $validatedNurse['education_place'] ?? null,
				'graduation_year' => $validatedNurse['graduation_year'] ?? null,
				'years_of_experience' => $validatedNurse['years_of_experience'] ?? null,
				'current_workplace' => $validatedNurse['current_workplace'] ?? null,
				'certifications' => $this->stringToArray($validatedNurse['certifications'] ?? null),
				'skills' => $this->stringToArray($validatedNurse['skills'] ?? null),
			]);
		});

		return redirect()->route('admin.nurses.index')
			->with('success', app()->getLocale() === 'ar' ? 'تم تحديث بيانات الممرض/ة' : 'Nurse updated successfully');
	}

	public function destroy(Nurse $nurse)
	{
		$client = $nurse->client;
		$nurse->delete();
		if ($client) {
			if ($client->avatar) {
				Storage::disk('public')->delete($client->avatar);
			}
			$client->delete();
		}
		return back()->with('success', app()->getLocale() === 'ar' ? 'تم حذف الممرض/ة' : 'Nurse deleted successfully');
	}

	private function stringToArray(?string $value): ?array
	{
		if (!$value) return null;
		// Split by comma and trim
		$parts = array_filter(array_map('trim', preg_split('/[,\\n]+/', $value)));
		return array_values($parts);
	}
}


