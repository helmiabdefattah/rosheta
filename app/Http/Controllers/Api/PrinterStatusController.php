<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The staff mobile app reports whether it currently has a Bluetooth ticket
 * printer connected. We stamp the timestamp on every clinic of the user's
 * doctor so the kiosk can decide whether to auto-print (skip the browser ticket).
 */
class PrinterStatusController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'connected' => ['required', 'boolean'],
        ]);

        $user = Auth::guard('sanctum')->user();
        if (! $user) {
            return response()->json(['ok' => false], 401);
        }

        $doctor = method_exists($user, 'clinicDoctor') ? $user->clinicDoctor() : null;
        if (! $doctor) {
            return response()->json(['ok' => true, 'clinics' => 0]);
        }

        $timestamp = $data['connected'] ? now() : null;
        $count = $doctor->clinics()->update(['printer_connected_at' => $timestamp]);

        return response()->json(['ok' => true, 'clinics' => $count]);
    }
}
