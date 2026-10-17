<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Clinic\Concerns\TableExport;
use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Collection as PatientCollection;
use App\Models\DentalPatientPayment;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Finance overview: clinic income (patient collections + dental patient
 * payments) against expenses for a period, with a category breakdown. Gives a
 * simple profit-and-loss view scoped to the active clinic.
 */
class FinanceReportController extends Controller
{
    use ClinicContext;
    use TableExport;

    public function overview(Request $request): View
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));
        [$from, $to] = $this->period($request);

        $data = $this->compute($clinic, $from, $to);

        return view('clinic.finance.overview', $data + [
            'clinic' => $clinic,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);
    }

    /** CSV / PDF of the overview summary. */
    public function export(Request $request)
    {
        $clinic = $this->activeClinic($this->clinicDoctor($request));
        [$from, $to] = $this->period($request);
        $d = $this->compute($clinic, $from, $to);

        $cur = __('app.expenses.currency');
        $columns = [__('app.finance.line'), __('app.expenses.total').' ('.$cur.')'];

        $rows = [
            [__('app.finance.income_patient'), number_format($d['income']['patient'], 2)],
            [__('app.finance.income_dental'), number_format($d['income']['dental'], 2)],
            [__('app.finance.income_total'), number_format($d['income']['total'], 2)],
        ];
        foreach ($d['byCategory'] as $cat) {
            $rows[] = ['— '.$cat['name'], number_format($cat['total'], 2)];
        }
        $rows[] = [__('app.finance.expenses_total'), number_format($d['expenses']['gross'], 2)];

        $totals = [__('app.finance.net'), number_format($d['net'], 2)];

        return $this->tableExportResponse(
            $request, $clinic, 'finance-overview',
            __('app.finance.title'), $from->format('Y-m-d').' — '.$to->format('Y-m-d'),
            $columns, $rows, $totals
        );
    }

    /**
     * Crunch income, expenses and the category breakdown for a period.
     *
     * @return array{income: array, expenses: array, byCategory: \Illuminate\Support\Collection, net: float, maxCategory: float}
     */
    private function compute(Clinic $clinic, Carbon $from, Carbon $to): array
    {
        $fromDate = $from->toDateString();
        $toDate = $to->toDateString();

        $patient = (float) PatientCollection::query()
            ->whereHas('appointment', fn ($q) => $q->where('clinic_id', $clinic->id))
            ->whereDate('collected_at', '>=', $fromDate)
            ->whereDate('collected_at', '<=', $toDate)
            ->sum('amount');

        $dental = (float) DentalPatientPayment::query()
            ->whereHas('order', fn ($q) => $q->where('clinic_id', $clinic->id))
            ->whereDate('paid_at', '>=', $fromDate)
            ->whereDate('paid_at', '<=', $toDate)
            ->sum('amount');

        $income = [
            'patient' => round($patient, 2),
            'dental' => round($dental, 2),
            'total' => round($patient + $dental, 2),
        ];

        $expenses = Expense::forClinic($clinic->id)
            ->inPeriod($fromDate, $toDate)
            ->with(['payments', 'category'])
            ->get();

        $gross = round($expenses->sum(fn (Expense $e) => $e->gross()), 2);
        $paid = round($expenses->sum(fn (Expense $e) => $e->paidTotal()), 2);

        $byCategory = $expenses
            ->groupBy(fn (Expense $e) => $e->category?->name ?? __('app.expenses.f.uncategorised'))
            ->map(fn ($group, $name) => [
                'name' => $name,
                'total' => round($group->sum(fn (Expense $e) => $e->gross()), 2),
            ])
            ->sortByDesc('total')
            ->values();

        return [
            'income' => $income,
            'expenses' => ['gross' => $gross, 'paid' => $paid],
            'byCategory' => $byCategory,
            'net' => round($income['total'] - $gross, 2),
            'maxCategory' => (float) ($byCategory->max('total') ?: 0),
        ];
    }

    private function period(Request $request): array
    {
        $from = $this->parseDate($request->query('from')) ?? Carbon::now()->startOfMonth();
        $to = $this->parseDate($request->query('to')) ?? Carbon::now()->endOfMonth();
        if ($from->gt($to)) {
            [$from, $to] = [$to, $from];
        }

        return [$from->startOfDay(), $to->startOfDay()];
    }

    private function parseDate(?string $value): ?Carbon
    {
        if (! is_string($value) || ! Carbon::hasFormat($value, 'Y-m-d')) {
            return null;
        }

        return Carbon::createFromFormat('Y-m-d', $value);
    }
}
