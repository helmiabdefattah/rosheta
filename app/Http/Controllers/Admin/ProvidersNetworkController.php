<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ProvidersNetworkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin screen to search the external providers-network directory and import
 * the results (doctors/clinics) into the system.
 */
class ProvidersNetworkController extends Controller
{
    public function __construct(private ProvidersNetworkService $service)
    {
    }

    /** Search form + results. Filters come from the query string (shareable). */
    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $searched = $this->hasAnyFilter($filters);

        $results = [];
        $count = 0;
        $error = null;

        if ($searched) {
            $data = $this->service->search($filters);
            $error = $data['error'] ?? null;
            $results = $data['results'] ?? [];
            $count = $data['count'] ?? count($results);
        }

        return view('admin.providers-network.index', [
            'filters' => $filters,
            'searched' => $searched,
            'results' => $results,
            'count' => $count,
            'error' => $error,
            'governorates' => ProvidersNetworkService::GOVERNORATES,
            'providerTypes' => ProvidersNetworkService::PROVIDER_TYPES,
        ]);
    }

    /**
     * Re-run the search server-side (so we never trust client-posted rows) and
     * import either everything found or just the selected Provider_IDs.
     */
    public function import(Request $request): RedirectResponse
    {
        $filters = $this->filters($request);
        if (! $this->hasAnyFilter($filters)) {
            return back()->withErrors(['search' => 'اختر فلترًا واحدًا على الأقل قبل الاستيراد.']);
        }

        $data = $this->service->search($filters);
        $results = $data['results'] ?? [];

        // Import selected rows only, unless "import all" was pressed.
        if ($request->input('scope') !== 'all') {
            $ids = collect($request->input('provider_ids', []))->map(fn ($v) => (string) $v)->all();
            if (empty($ids)) {
                return back()->withErrors(['import' => 'حدد مقدمي خدمة للاستيراد أو اضغط "استيراد الكل".'])
                    ->withInput();
            }
            $results = array_values(array_filter($results, fn ($r) => in_array(
                (string) ($r['provider_id'] ?? $r['Provider_ID'] ?? ''), $ids, true
            )));
        }

        $summary = $this->service->import($results);

        $message = "تم الاستيراد: {$summary['created']} جديد، {$summary['updated']} محدّث"
            .($summary['skipped'] ? "، {$summary['skipped']} متجاهل" : '')
            ." (إجمالي {$summary['total']}).";

        return redirect()
            ->route('admin.providers-network.index', array_filter($filters, fn ($v) => $v !== '' && $v !== null && $v !== []))
            ->with('status', $message);
    }

    /** Pull the recognised filter values from the request. */
    private function filters(Request $request): array
    {
        return [
            'governorate' => (string) $request->input('governorate', ''),
            'city' => (string) $request->input('city', ''),
            'provider_type' => (string) $request->input('provider_type', ''),
            'search_query' => (string) $request->input('search_query', ''),
            'network_tier' => (string) $request->input('network_tier', ''),
            'quick_filters' => [],
        ];
    }

    private function hasAnyFilter(array $filters): bool
    {
        return (bool) collect($filters)
            ->only(['governorate', 'city', 'provider_type', 'search_query', 'network_tier'])
            ->first(fn ($v) => trim((string) $v) !== '');
    }
}
