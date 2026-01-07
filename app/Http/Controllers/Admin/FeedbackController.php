<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * Display a listing of feedback.
     */
    public function index(Request $request)
    {
        $query = Feedback::with('client');

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type') && $request->type !== '') {
            $query->where('type', $request->type);
        }

        // Search
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%")
                  ->orWhereHas('client', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%");
                  });
            });
        }

        $feedbacks = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.feedback.index', compact('feedbacks'));
    }

    /**
     * Show the form for responding to feedback.
     */
    public function show(Feedback $feedback)
    {
        $feedback->load('client');
        return view('admin.feedback.show', compact('feedback'));
    }

    /**
     * Update feedback status and add admin response.
     */
    public function update(Request $request, Feedback $feedback)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,reviewed,resolved,closed',
            'admin_response' => 'nullable|string|max:5000',
        ]);

        $updateData = [
            'status' => $validated['status'],
        ];

        if ($request->filled('admin_response')) {
            $updateData['admin_response'] = $validated['admin_response'];
            $updateData['reviewed_at'] = now();
        }

        $feedback->update($updateData);

        return redirect()->route('admin.feedback.index')
            ->with('success', app()->getLocale() === 'ar' 
                ? 'تم تحديث الملاحظة بنجاح' 
                : 'Feedback updated successfully');
    }

    /**
     * Delete feedback.
     */
    public function destroy(Feedback $feedback)
    {
        $feedback->delete();

        return redirect()->route('admin.feedback.index')
            ->with('success', app()->getLocale() === 'ar' 
                ? 'تم حذف الملاحظة بنجاح' 
                : 'Feedback deleted successfully');
    }
}
