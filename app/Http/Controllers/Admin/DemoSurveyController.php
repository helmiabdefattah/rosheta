<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DemoSurvey;
use Illuminate\Http\Request;

/**
 * What visitors said on their way out of the demo sandbox.
 *
 * Read-only apart from deleting a row: there is nobody to reply to — a demo
 * visitor leaves no account, no email and no phone behind — so unlike client
 * feedback these have no status to work through, only a total to read and
 * text to act on.
 */
class DemoSurveyController extends Controller
{
    public function index(Request $request)
    {
        $query = DemoSurvey::with('session');

        // Useful / not useful.
        if ($request->filled('useful')) {
            $query->where('was_useful', $request->input('useful') === 'yes');
        }

        // Only the ones that actually said something.
        if ($request->input('has_text') === '1') {
            $query->where(function ($q) {
                $q->whereNotNull('wants_added')->orWhereNotNull('wants_removed');
            });
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('wants_added', 'like', "%{$search}%")
                    ->orWhere('wants_removed', 'like', "%{$search}%")
                    ->orWhere('specialty', 'like', "%{$search}%");
            });
        }

        $surveys = $query->latest()->paginate(15)->withQueryString();

        // Counted across the whole table, not the current filter: the point of
        // the header is "how is the demo doing", which a filtered ratio would
        // answer wrongly.
        $stats = [
            'total' => DemoSurvey::count(),
            'useful' => DemoSurvey::where('was_useful', true)->count(),
            'not_useful' => DemoSurvey::where('was_useful', false)->count(),
            'with_text' => DemoSurvey::where(function ($q) {
                $q->whereNotNull('wants_added')->orWhereNotNull('wants_removed');
            })->count(),
        ];

        return view('admin.demo-surveys.index', compact('surveys', 'stats'));
    }

    public function destroy(DemoSurvey $demoSurvey)
    {
        $demoSurvey->delete();

        return redirect()->route('admin.demo-surveys.index')
            ->with('success', app()->getLocale() === 'ar'
                ? 'تم حذف الرأي بنجاح'
                : 'Survey response deleted');
    }
}
