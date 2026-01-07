<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\Laboratory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LaboratorySupportTicketController extends Controller
{
    /**
     * Show the support ticket form.
     */
    public function create()
    {
        return view('laboratories.support-tickets.create');
    }

    /**
     * Store the support ticket.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $laboratory = Laboratory::where('user_id', $user->id)->firstOrFail();

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
            'ticketable_type' => Laboratory::class,
            'ticketable_id' => $laboratory->id,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'type' => $validated['type'],
            'priority' => $validated['priority'],
            'status' => 'open',
        ]);

        return redirect()->route('laboratories.support-tickets.create')
            ->with('success', app()->getLocale() === 'ar' 
                ? 'شكراً لك! تم إرسال تذكرتك بنجاح. سنراجعها قريباً.' 
                : 'Thank you! Your ticket has been submitted successfully. We will review it soon.');
    }

    /**
     * Show the laboratory's support ticket history.
     */
    public function index()
    {
        $user = Auth::user();
        $laboratory = Laboratory::where('user_id', $user->id)->firstOrFail();
        
        $tickets = SupportTicket::where('ticketable_type', Laboratory::class)
            ->where('ticketable_id', $laboratory->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('laboratories.support-tickets.index', compact('tickets'));
    }
}
