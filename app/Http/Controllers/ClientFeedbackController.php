<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientFeedbackController extends Controller
{
    /**
     * Show the feedback form.
     */
    public function create()
    {
        return view('client.feedback.create');
    }

    /**
     * Store the feedback.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|min:10|max:5000',
            'type' => 'required|in:bug,suggestion,complaint,compliment,other',
        ], [
            'message.required' => app()->getLocale() === 'ar' 
                ? 'الرجاء إدخال رسالتك' 
                : 'Please enter your message.',
            'message.min' => app()->getLocale() === 'ar' 
                ? 'يجب أن تكون الرسالة على الأقل 10 أحرف' 
                : 'The message must be at least 10 characters.',
            'message.max' => app()->getLocale() === 'ar' 
                ? 'يجب ألا تتجاوز الرسالة 5000 حرف' 
                : 'The message must not exceed 5000 characters.',
            'type.required' => app()->getLocale() === 'ar' 
                ? 'الرجاء اختيار نوع الملاحظة' 
                : 'Please select a feedback type.',
        ]);

        $client = Auth::guard('client')->user();

        Feedback::create([
            'client_id' => $client->id,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'type' => $validated['type'],
            'status' => 'pending',
        ]);

        return redirect()->route('client.feedback.create')
            ->with('success', app()->getLocale() === 'ar' 
                ? 'شكراً لك! تم إرسال ملاحظتك بنجاح. سنراجعها قريباً.' 
                : 'Thank you! Your feedback has been submitted successfully. We will review it soon.');
    }

    /**
     * Show the client's feedback history.
     */
    public function index()
    {
        $client = Auth::guard('client')->user();
        $feedbacks = Feedback::where('client_id', $client->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('client.feedback.index', compact('feedbacks'));
    }
}
