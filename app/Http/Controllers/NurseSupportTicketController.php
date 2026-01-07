<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\Nurse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NurseSupportTicketController extends Controller
{
    /**
     * Show the support ticket form.
     */
    public function create()
    {
        return view('nurse.support-tickets.create');
    }

    /**
     * Store the support ticket.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        // Get nurse using nurse_id from user or find by user_id
        if ($user->nurse_id) {
            $nurse = Nurse::findOrFail($user->nurse_id);
        } else {
            $nurse = Nurse::where('user_id', $user->id)->firstOrFail();
        }

        $validated = $request->validate([
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|min:10|max:5000',
            'type' => 'required|in:technical,billing,feature_request,complaint,other',
            'priority' => 'required|in:low,medium,high,urgent',
        ], [
            'message.required' => app()->getLocale() === 'ar' 
                ? 'الرجاء إدخال رسالتك' 
                : 'Please enter your message.',
            'message.min' => app()->getLocale() === 'ar' 
                ? 'يجب أن تكون الرسالة على الأقل 10 أحرف' 
                : 'The message must be at least 10 characters.',
            'type.required' => app()->getLocale() === 'ar' 
                ? 'الرجاء اختيار نوع التذكرة' 
                : 'Please select a ticket type.',
            'priority.required' => app()->getLocale() === 'ar' 
                ? 'الرجاء اختيار الأولوية' 
                : 'Please select a priority.',
        ]);

        SupportTicket::create([
            'ticketable_type' => Nurse::class,
            'ticketable_id' => $nurse->id,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'type' => $validated['type'],
            'priority' => $validated['priority'],
            'status' => 'open',
        ]);

        return redirect()->route('nurse.support-tickets.create')
            ->with('success', app()->getLocale() === 'ar' 
                ? 'شكراً لك! تم إرسال تذكرتك بنجاح. سنراجعها قريباً.' 
                : 'Thank you! Your ticket has been submitted successfully. We will review it soon.');
    }

    /**
     * Show the nurse's support ticket history.
     */
    public function index()
    {
        $user = Auth::user();
        // Get nurse using nurse_id from user or find by user_id
        if ($user->nurse_id) {
            $nurse = Nurse::findOrFail($user->nurse_id);
        } else {
            $nurse = Nurse::where('user_id', $user->id)->firstOrFail();
        }
        
        $tickets = SupportTicket::where('ticketable_type', Nurse::class)
            ->where('ticketable_id', $nurse->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('nurse.support-tickets.index', compact('tickets'));
    }
}
