<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\CharitableOrganization;
use App\Models\Doctor;
use App\Models\Laboratory;
use App\Models\Nurse;
use App\Models\Pharmacy;
use App\Models\Specialization;
use App\Models\User;
use App\Models\Governorate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class ServiceProviderRegisterController extends Controller
{
    private const TYPES = ['pharmacy', 'laboratory', 'radiology', 'nurse', 'doctor', 'charitable_organization'];

    private function documentValidationRules(): array
    {
        return [
            'license_number' => 'nullable|string|max:255',
            'papers' => 'nullable|array|max:15',
            'papers.*.file' => 'nullable|file|mimes:pdf,jpeg,jpg,png,webp|max:10240',
            'papers.*.description' => 'nullable|string|max:1000',
        ];
    }

    protected function attachRegistrationPapers(User $user, Request $request): void
    {
        foreach ($request->input('papers', []) as $idx => $row) {
            if (! is_array($row)) {
                continue;
            }
            $key = "papers.$idx.file";
            if (! $request->hasFile($key)) {
                continue;
            }
            $desc = isset($row['description']) ? (string) $row['description'] : '';
            $user->addMediaFromRequest($key)
                ->withCustomProperties(['description' => Str::limit($desc, 1000)])
                ->toMediaCollection('service_registration_documents');
        }
    }

    public function select()
    {
        if (Auth::guard('client')->check()) {
            return redirect()->route('client.dashboard');
        }
        if (Auth::check()) {
            return redirect()->route('login');
        }

        return view('auth.service-provider-select');
    }

    public function create(string $type)
    {
        if (Auth::guard('client')->check()) {
            return redirect()->route('client.dashboard');
        }
        if (Auth::check()) {
            return redirect()->route('login');
        }

        if (! in_array($type, self::TYPES, true)) {
            abort(404);
        }

        $governorates = in_array($type, ['pharmacy', 'laboratory', 'radiology', 'nurse', 'charitable_organization'], true)
            ? Governorate::where('is_active', true)->orderBy('name')->get()
            : collect();
        $specializations = $type === 'doctor'
            ? Specialization::orderBy('name')->get()
            : collect();

        $paperRows = old('papers');
        if (! is_array($paperRows) || $paperRows === []) {
            $paperRows = [['description' => '']];
        }

        return view('auth.service-provider-form', [
            'type' => $type,
            'governorates' => $governorates,
            'specializations' => $specializations,
            'paperRows' => $paperRows,
        ]);
    }

    public function store(Request $request, string $type)
    {
        if (Auth::guard('client')->check()) {
            return redirect()->route('client.dashboard');
        }
        if (Auth::check()) {
            return redirect()->route('login');
        }

        if (! in_array($type, self::TYPES, true)) {
            abort(404);
        }

        $passwordRule = ['required', 'string', 'confirmed', Password::min(8)];
        $passwordMessages = app()->getLocale() === 'ar'
            ? [
                'password.required' => 'كلمة المرور مطلوبة.',
                'password.confirmed' => 'تأكيد كلمة المرور غير مطابق. أعد كتابة الحقلين يدوياً.',
                'password.min' => 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.',
            ]
            : [
                'password.required' => 'Password is required.',
                'password.confirmed' => 'Password confirmation does not match. Re-type both password fields manually.',
                'password.min' => 'Password must be at least 8 characters.',
            ];

        $docRules = $this->documentValidationRules();

        if ($type === 'nurse') {
            $validated = $request->validate(array_merge([
                'account_name' => 'required|string|max:255',
                'phone_number' => 'required|string|max:50|unique:users,phone_number',
                'email' => 'nullable|email|max:255|unique:users,email',
                'password' => $passwordRule,
                'gender' => 'nullable|in:male,female',
                'address' => 'nullable|string',
                'area_ids' => 'required|array|min:1',
                'area_ids.*' => 'integer|exists:areas,id',
            ], $docRules), $passwordMessages);
        } elseif ($type === 'doctor') {
            $validated = $request->validate(array_merge([
                'account_name' => 'required|string|max:255',
                'phone_number' => 'required|string|max:50|unique:users,phone_number',
                'email' => 'nullable|email|max:255|unique:users,email',
                'password' => $passwordRule,
                'doctor_name' => 'required|string|max:255',
                'specialization_id' => 'required|exists:specializations,id',
                'brief' => 'nullable|string|max:5000',
            ], $docRules), $passwordMessages);
        } elseif ($type === 'charitable_organization') {
            $validated = $request->validate(array_merge([
                'account_name' => 'required|string|max:255',
                'phone_number' => 'required|string|max:50|unique:users,phone_number',
                'email' => 'nullable|email|max:255|unique:users,email',
                'password' => $passwordRule,
                'organization_name' => 'required|string|max:255',
                'governorate_id' => 'required|exists:governorates,id',
                'city_id' => 'required|exists:cities,id',
                'area_id' => 'required|exists:areas,id',
                'address' => 'required|string',
                'organization_phone' => 'nullable|string|max:20',
                'services_text' => 'nullable|string|max:5000',
            ], $docRules), $passwordMessages);
        } else {
            $base = [
                'account_name' => 'required|string|max:255',
                'phone_number' => 'required|string|max:50|unique:users,phone_number',
                'email' => 'nullable|email|max:255|unique:users,email',
                'password' => $passwordRule,
                'business_name' => 'required|string|max:255',
                'business_phone' => 'nullable|string|max:255',
                'business_email' => 'nullable|email|max:255',
                'address' => 'nullable|string',
                'governorate_id' => 'nullable|exists:governorates,id',
                'city_id' => 'nullable|exists:cities,id',
                'area_id' => 'nullable|exists:areas,id',
            ];
            $rules = array_merge($base, $docRules);
            if (in_array($type, ['laboratory', 'radiology'], true)) {
                $rules['manager_name'] = 'nullable|string|max:255';
            }
            $validated = $request->validate($rules, $passwordMessages);
        }

        DB::transaction(function () use ($type, $validated, $request) {
            $user = User::create([
                'name' => $validated['account_name'],
                'phone_number' => $validated['phone_number'],
                'email' => ! empty($validated['email'] ?? null) ? $validated['email'] : null,
                'password' => Hash::make($validated['password']),
                'is_active' => false,
                'registration_license_number' => in_array($type, ['nurse', 'doctor', 'charitable_organization'], true)
                    ? ($validated['license_number'] ?? null)
                    : null,
            ]);

            if ($type === 'pharmacy') {
                $pharmacy = Pharmacy::create([
                    'user_id' => $user->id,
                    'area_id' => $validated['area_id'] ?? null,
                    'name' => $validated['business_name'],
                    'phone' => $validated['business_phone'] ?? null,
                    'email' => $validated['business_email'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'license_number' => $validated['license_number'] ?? null,
                    'is_active' => false,
                ]);
                $user->update(['pharmacy_id' => $pharmacy->id]);
            } elseif ($type === 'laboratory' || $type === 'radiology') {
                $lab = Laboratory::create([
                    'user_id' => $user->id,
                    'area_id' => $validated['area_id'] ?? null,
                    'name' => $validated['business_name'],
                    'phone' => $validated['business_phone'] ?? null,
                    'email' => $validated['business_email'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'license_number' => $validated['license_number'] ?? null,
                    'manager_name' => $validated['manager_name'] ?? null,
                    'type' => $type === 'radiology' ? 'radiology' : 'test',
                    'is_active' => false,
                ]);
                $user->update(['laboratory_id' => $lab->id]);
            } elseif ($type === 'nurse') {
                $nurse = Nurse::create([
                    'user_id' => $user->id,
                    'gender' => $validated['gender'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'status' => 'inactive',
                    'area_ids' => $validated['area_ids'],
                ]);
                $user->update(['nurse_id' => $nurse->id]);
            } elseif ($type === 'doctor') {
                $slug = Str::slug($validated['doctor_name']);
                $baseSlug = $slug;
                $n = 0;
                while (Doctor::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . Str::lower(Str::random(5));
                    if (++$n > 20) {
                        $slug = $baseSlug . '-' . $user->id;
                        break;
                    }
                }
                Doctor::create([
                    'user_id' => $user->id,
                    'specialization_id' => $validated['specialization_id'],
                    'name' => $validated['doctor_name'],
                    'slug' => $slug,
                    'brief' => $validated['brief'] ?? null,
                ]);
            } elseif ($type === 'charitable_organization') {
                $phones = [];
                if (! empty(trim((string) ($validated['organization_phone'] ?? '')))) {
                    $phones[] = trim($validated['organization_phone']);
                }
                $services = null;
                if (! empty(trim((string) ($validated['services_text'] ?? '')))) {
                    $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $validated['services_text']))));
                    $services = $lines !== [] ? $lines : null;
                }

                $organization = CharitableOrganization::create([
                    'user_id' => $user->id,
                    'name' => $validated['organization_name'],
                    'governorate_id' => $validated['governorate_id'],
                    'city_id' => $validated['city_id'],
                    'area_id' => $validated['area_id'],
                    'address' => $validated['address'],
                    'phone_numbers' => $phones !== [] ? $phones : null,
                    'services' => $services,
                    'is_active' => false,
                ]);
                $user->update(['charitable_organization_id' => $organization->id]);
            }

            $this->attachRegistrationPapers($user, $request);
        });

        return redirect()->route('login')->with(
            'info',
            app()->getLocale() === 'ar'
                ? 'تم إرسال طلبك. سيتم تفعيل حسابك بعد مراجعة الإدارة.'
                : 'Your request was submitted. Your account will be activated after admin review.'
        );
    }
}
