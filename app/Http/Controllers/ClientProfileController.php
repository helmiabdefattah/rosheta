<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use App\Models\InsuranceCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ClientProfileController extends Controller
{
    public function edit()
    {
        $client = Auth::guard('client')->user()->load('insuranceCompany');
        $insuranceCompanies = InsuranceCompany::where('is_active', true)
            ->orderBy('name')
            ->get();
        return view('client.profile.edit', compact('client', 'insuranceCompanies'));
    }

    public function update(Request $request)
    {
        $client = Auth::guard('client')->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($client) {
                    // Check if phone_number exists in clients table (excluding current client)
                    $existsInClients = Client::where('phone_number', $value)
                        ->where('id', '!=', $client->id)
                        ->exists();
                    
                    // Check if phone_number exists in users table
                    $existsInUsers = User::where('email', $value)->exists();
                    
                    if ($existsInClients || $existsInUsers) {
                        $fail('The phone number has already been taken.');
                    }
                },
            ],
            'email' => [
                'nullable',
                'email',
                function ($attribute, $value, $fail) use ($client) {
                    if ($value) {
                        // Check if email exists in clients table (excluding current client)
                        $existsInClients = Client::where('email', $value)
                            ->where('id', '!=', $client->id)
                            ->exists();
                        
                        // Check if email exists in users table
                        $existsInUsers = User::where('email', $value)->exists();
                        
                        if ($existsInClients || $existsInUsers) {
                            $fail('The email has already been taken.');
                        }
                    }
                },
            ],
            'insurance_company_id' => 'nullable|exists:insurance_companies,id',
            'insurance_company_name' => 'nullable|string|max:255',
        ], [
            'name.required' => 'The name field is required.',
            'phone_number.required' => 'The phone number field is required.',
        ]);

        // Handle insurance company - create new if name provided, otherwise use selected ID
        $insuranceCompanyId = null;
        if ($request->filled('insurance_company_name')) {
            $insuranceCompany = InsuranceCompany::firstOrCreate(
                ['name' => $request->insurance_company_name],
                ['is_active' => true]
            );
            $insuranceCompanyId = $insuranceCompany->id;
        } elseif ($request->filled('insurance_company_id')) {
            $insuranceCompanyId = $request->insurance_company_id;
        }

        $client->update([
            'name' => $validated['name'],
            'phone_number' => $validated['phone_number'],
            'email' => $validated['email'] ?? $client->email,
            'insurance_company_id' => $insuranceCompanyId,
        ]);

        return redirect()->route('client.profile.edit')
            ->with('success', app()->getLocale() === 'ar' 
                ? 'تم تحديث الملف الشخصي بنجاح' 
                : 'Profile updated successfully');
    }

    public function destroy(Request $request)
    {
        $client = Auth::guard('client')->user();

        // Validate password for security
        $request->validate([
            'password' => 'required|string',
        ]);

        // Verify password
        if (!Hash::check($request->password, $client->password)) {
            return back()->withErrors([
                'password' => app()->getLocale() === 'ar' 
                    ? 'كلمة المرور غير صحيحة' 
                    : 'The password is incorrect.'
            ])->withInput();
        }

        try {
            DB::transaction(function () use ($client) {
                // Delete related data
                $client->addresses()->delete();
                
                // Note: We keep client requests and orders for record-keeping
                // but you can uncomment these if you want to delete them too:
                // $client->requests()->delete();
                
                // Delete the client account
                $client->delete();
            });

            // Logout the user
            Auth::guard('client')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('welcome')
                ->with('success', app()->getLocale() === 'ar' 
                    ? 'تم حذف حسابك بنجاح' 
                    : 'Your account has been deleted successfully');
        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => app()->getLocale() === 'ar' 
                    ? 'حدث خطأ أثناء حذف الحساب. يرجى المحاولة مرة أخرى.' 
                    : 'An error occurred while deleting your account. Please try again.'
            ]);
        }
    }
}

