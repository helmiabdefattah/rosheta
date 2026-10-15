<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Saves the current user's clinic-workspace navigation preference
 * ('buttons' or 'menu'). Written from the quick ☰ toggle (AJAX) and from the
 * clinic settings form.
 */
class NavPreferenceController extends Controller
{
    public function update(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'nav_style' => ['required', Rule::in(['buttons', 'menu'])],
        ]);

        $request->user()->update(['nav_style' => $data['nav_style']]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['nav_style' => $data['nav_style']]);
        }

        return back()->with('status', __('app.menu.saved'));
    }
}
