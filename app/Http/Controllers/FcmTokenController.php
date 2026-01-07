<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FcmTokenController extends Controller
{
    /**
     * Update FCM token for authenticated user (Web Dashboard)
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

        // Try to get user from different guards
        $user = null;
        
        // Check client guard first
        if (Auth::guard('client')->check()) {
            $user = Auth::guard('client')->user();
        } 
        // Then check regular auth (admin, lab, nurse)
        elseif (Auth::check()) {
            $user = Auth::user();
        }

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
     * Remove FCM token (on logout or token invalidation)
     */
    public function remove(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'platform' => 'required|in:web,mobile',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = null;
        
        if (Auth::guard('client')->check()) {
            $user = Auth::guard('client')->user();
        } elseif (Auth::check()) {
            $user = Auth::user();
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        // Remove token based on platform
        if ($request->platform === 'web') {
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
