<?php

namespace App\Http\Controllers;

use App\Models\Laboratory;
use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LaboratoryQuoteController extends Controller
{
    /**
     * Display a listing of quotes for the laboratory.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get the laboratory for the authenticated user
        $laboratory = null;
        if ($user->laboratory_id) {
            $laboratory = Laboratory::find($user->laboratory_id);
        }

        // If user doesn't have a laboratory
        if (!$laboratory) {
            return redirect()->route('laboratories.dashboard')
                ->with('error', app()->getLocale() === 'ar'
                    ? 'أنت غير مرتبط بأي معمل.'
                    : 'You are not associated with any laboratory.');
        }

        // Get all quotes for this laboratory
        $quotes = Quote::where('model_type', Laboratory::class)
            ->where('model_id', $laboratory->id)
            ->with('client')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('laboratories.quotes.index', compact('laboratory', 'quotes'));
    }

    /**
     * Update the reply for a quote.
     */
    public function update(Request $request, Quote $quote)
    {
        $user = Auth::user();
        
        // Get the laboratory for the authenticated user
        $laboratory = null;
        if ($user->laboratory_id) {
            $laboratory = Laboratory::find($user->laboratory_id);
        }

        // Verify the quote belongs to this laboratory
        if (!$laboratory || 
            $quote->model_type !== Laboratory::class || 
            $quote->model_id !== $laboratory->id) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => app()->getLocale() === 'ar'
                        ? 'غير مصرح لك بتعديل هذا الاستفسار.'
                        : 'You are not authorized to update this quote.'
                ], 403);
            }
            return back()->with('error', app()->getLocale() === 'ar'
                ? 'غير مصرح لك بتعديل هذا الاستفسار.'
                : 'You are not authorized to update this quote.');
        }

        $validated = $request->validate([
            'reply' => 'required|string|max:5000',
        ]);

        $quote->update([
            'reply' => $validated['reply'],
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => app()->getLocale() === 'ar'
                    ? 'تم إرسال الرد بنجاح'
                    : 'Reply sent successfully',
                'quote' => $quote->fresh(['client']),
            ]);
        }

        return back()->with('success', app()->getLocale() === 'ar'
            ? 'تم إرسال الرد بنجاح'
            : 'Reply sent successfully');
    }
}
