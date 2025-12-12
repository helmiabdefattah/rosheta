<?php

namespace App\Filament\Widgets;

use App\Models\ClientRequest; // Import your ClientRequest model
use Carbon\Carbon; // Use Carbon for date handling
use Filament\Widgets\ChartWidget;

class BlogPostsChart extends ChartWidget
{
    protected ?string $heading = 'Daily Client Requests'; // Non-static property, as fixed previously

    protected function getData(): array
    {
        // 1. Define the Date Range (Last 7 Days)
        $endDate = Carbon::now()->endOfDay();
        $startDate = Carbon::now()->subDays(6)->startOfDay();

        // 2. Query the Data
        $data = ClientRequest::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->pluck('count', 'date')
            ->all();

        // 3. Prepare the Labels and Data Points
        $labels = [];
        $counts = [];

        // Loop through the last 7 days to ensure zero counts are included
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $formattedDate = $date->toDateString(); // YYYY-MM-DD for key lookup
            $displayLabel = $date->format('D, M j'); // e.g., "Mon, Dec 9"

            $labels[] = $displayLabel;
            // Get the count from the queried data, default to 0 if no requests were made
            $counts[] = $data[$formattedDate] ?? 0;
        }

        // 4. Return the Chart Data Structure
        return [
            'datasets' => [
                [
                    'label' => 'Total Requests Created',
                    // Use the dynamically generated counts array
                    'data' => $counts,
                    // Optional: Customizing appearance for a line chart
                    'borderColor' => '#10B981', // Emerald color
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                ],
            ],
            // Use the dynamically generated labels array
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
