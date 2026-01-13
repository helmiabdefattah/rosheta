<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ClientQuoteController extends Controller
{
    /**
     * Display a listing of the client's quotes.
     */
    public function index()
    {
        $client = Auth::guard('client')->user();
        
        $quotes = Quote::where('client_id', $client->id)
            ->with(['quotable'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('client.quotes.index', compact('quotes'));
    }

    /**
     * Store a new quote.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'model_type' => ['required', 'string', Rule::in(['App\\Models\\Laboratory', 'App\\Models\\Pharmacy'])],
            'model_id' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) use ($request) {
                    $modelType = $request->input('model_type');
                    if ($modelType === 'App\\Models\\Laboratory') {
                        if (!\App\Models\Laboratory::where('id', $value)->exists()) {
                            $fail(app()->getLocale() === 'ar' ? 'المختبر المحدد غير موجود.' : 'The selected laboratory does not exist.');
                        }
                    } elseif ($modelType === 'App\\Models\\Pharmacy') {
                        if (!\App\Models\Pharmacy::where('id', $value)->exists()) {
                            $fail(app()->getLocale() === 'ar' ? 'الصيدلية المحددة غير موجودة.' : 'The selected pharmacy does not exist.');
                        }
                    }
                },
            ],
            'quote' => 'required|string|max:5000',
        ]);

        $quote = Quote::create([
            'model_type' => $validated['model_type'],
            'model_id' => $validated['model_id'],
            'client_id' => Auth::guard('client')->id(),
            'quote' => $validated['quote'],
        ]);

        return response()->json([
            'success' => true,
            'message' => app()->getLocale() === 'ar' 
                ? 'تم إرسال الاستفسار بنجاح' 
                : 'Quote sent successfully',
            'quote' => $quote,
        ]);
    }
}
