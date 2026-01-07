<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    /**
     * Display a listing of support tickets.
     */
    public function index(Request $request)
    {
        $query = SupportTicket::with(['ticketable', 'assignedAdmin']);

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type') && $request->type !== '') {
            $query->where('type', $request->type);
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority !== '') {
            $query->where('priority', $request->priority);
        }

        // Filter by ticketable type
        if ($request->has('ticketable_type') && $request->ticketable_type !== '') {
            $query->where('ticketable_type', $request->ticketable_type);
        }

        // Search
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(15);

        $admins = User::whereNull('laboratory_id')
            ->whereNull('pharmacy_id')
            ->whereNull('nurse_id')
            ->get();

        return view('admin.support-tickets.index', compact('tickets', 'admins'));
    }

    /**
     * Show the form for responding to a support ticket.
     */
    public function show(SupportTicket $supportTicket)
    {
        $supportTicket->load(['ticketable', 'assignedAdmin']);
        $admins = User::whereNull('laboratory_id')
            ->whereNull('pharmacy_id')
            ->whereNull('nurse_id')
            ->get();
        return view('admin.support-tickets.show', compact('supportTicket', 'admins'));
    }

    /**
     * Update support ticket status and add admin response.
     */
    public function update(Request $request, SupportTicket $supportTicket)
    {
        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
            'priority' => 'required|in:low,medium,high,urgent',
            'admin_response' => 'nullable|string|max:5000',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $updateData = [
            'status' => $validated['status'],
            'priority' => $validated['priority'],
        ];

        if ($request->filled('assigned_to')) {
            $updateData['assigned_to'] = $validated['assigned_to'];
        }

        if ($request->filled('admin_response')) {
            $updateData['admin_response'] = $validated['admin_response'];
        }

        if ($validated['status'] === 'resolved' && !$supportTicket->resolved_at) {
            $updateData['resolved_at'] = now();
        }

        $supportTicket->update($updateData);

        return redirect()->route('admin.support-tickets.index')
            ->with('success', app()->getLocale() === 'ar' 
                ? 'تم تحديث التذكرة بنجاح' 
                : 'Support ticket updated successfully');
    }

    /**
     * Delete support ticket.
     */
    public function destroy(SupportTicket $supportTicket)
    {
        $supportTicket->delete();

        return redirect()->route('admin.support-tickets.index')
            ->with('success', app()->getLocale() === 'ar' 
                ? 'تم حذف التذكرة بنجاح' 
                : 'Support ticket deleted successfully');
    }
}
