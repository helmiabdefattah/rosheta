<?php
    namespace App\Http\Controllers;

    use App\Mail\ResetPasswordCodeMail;
    use App\Models\Client;
    use App\Models\PasswordResetCode;
    use Carbon\Carbon;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\Mail;

    class AuthResetController extends Controller
    {
    public function sendCode(Request $request)
    {
    $request->validate([
    'phone_number' => 'required|exists:clients,phone_number',
    ]);

    $user = Client::where('phone_number', $request->phone_number)->first();

    // generate 6-digit code
    $code = rand(100000, 999999);

    PasswordResetCode::where('phone', $request->phone_number)->delete();

    PasswordResetCode::create([
    'phone' => $request->phone_number,
    'code' => $code,
    'expires_at' => Carbon::now()->addMinutes(10),
    ]);

    Mail::to($user->email)->send(new ResetPasswordCodeMail($code));

    return response()->json(['message' => 'Reset code sent to your email.']);
    }

    // 2️⃣ Verify Code
    public function checkCode(Request $request)
    {

    $request->validate([
    'phone_number' => 'required',
    'code' => 'required|digits:6'
    ]);

    $record = PasswordResetCode::where('phone', $request->phone_number)
    ->where('code', $request->code)
    ->first();

    if (!$record) {
    return response()->json(['message' => 'Invalid code.'], 422);
    }

    if (Carbon::now()->isAfter($record->expires_at)) {
    return response()->json(['message' => 'Code expired.'], 422);
    }

    return response()->json(['message' => 'Code verified. You can reset your password now.']);
    }

    // 3️⃣ Reset Password
    public function resetPassword(Request $request)
    {
    $request->validate([

    'password' => 'required|min:6|confirmed',
    ]);

    $record = PasswordResetCode::where('phone', $request->phone_number)
    ->first();

    if (!$record) {
    return response()->json(['message' => 'Invalid code.'], 422);
    }

    $user = Client::where('phone_number', $request->phone_number)->first();
    $user->update([
    'password' => Hash::make($request->password),
    ]);

    // delete code
    PasswordResetCode::where('phone', $request->phone_number)->delete();

    return response()->json(['message' => 'Password reset successfully.']);
    }
    }
