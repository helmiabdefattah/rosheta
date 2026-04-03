<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FcmTokenController extends Controller
{
    /**
     * Update FCM token for authenticated user (API - Mobile)
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'platform' => 'required|in:web,mobile',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = Auth::guard('sanctum')->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        // Update token based on platform
        if ($request->platform === 'web') {
            $user->fcm_token_web = $request->token;
        } else {
            $user->fcm_token_mobile = $request->token;
        }
        
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'FCM token updated successfully',
            'platform' => $request->platform,
        ]);
    }

    /**
     * Remove FCM token for mobile or web (API - Sanctum client).
     */
    public function remove(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'platform' => 'required|in:web,mobile',
        ])->validate();

        $user = Auth::guard('sanctum')->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        if ($validated['platform'] === 'web') {
            $user->fcm_token_web = null;
        } else {
            $user->fcm_token_mobile = null;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'FCM token removed successfully',
        ]);
    }
}
