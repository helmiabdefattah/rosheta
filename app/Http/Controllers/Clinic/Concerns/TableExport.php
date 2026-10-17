<?php

namespace App\Http\Controllers\Clinic\Concerns;

use App\Models\Clinic;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Generic "export a table as PDF (mpdf) or Excel-friendly CSV" plumbing, usable
 * by any clinic module. The dental module has its own near-identical trait
 * (DentalExports) kept for backwards compatibility; new modules use this one.
 */
trait TableExport
{
    /**
     * The reporting window: the given from/to dates, or the current month when
     * either is missing. Returns [start, end(inclusive), human label].
     *
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    protected function tableExportPeriod(Request $request): array
    {
        $from = $request->filled('from') ? Carbon::parse($request->date('from')) : now()->startOfMonth();
        $to = $request->filled('to') ? Carbon::parse($request->date('to')) : now()->endOfMonth();

        $from = $from->startOfDay();
        $to = $to->endOfDay();

        return [$from, $to, $from->format('Y-m-d').' — '.$to->format('Y-m-d')];
    }

    /**
     * Return a built table as a downloadable PDF (mpdf, RTL-aware) or as an
     * Excel-friendly UTF-8 CSV when ?format is not "pdf".
     *
     * @param  array<int, string>  $columns
     * @param  array<int, array<int, string|int|null>>  $rows
     * @param  array<int, string>  $totals
     */
    protected function tableExportResponse(Request $request, Clinic $clinic, string $filenamePrefix, string $title, string $periodLabel, array $columns, array $rows, array $totals = [])
    {
        $filename = $filenamePrefix.'-'.now()->format('Ymd-His');

        if ($request->input('format') === 'pdf') {
            $tmp = storage_path('app/mpdf');
            if (! is_dir($tmp)) {
                @mkdir($tmp, 0775, true);
            }

            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4-L',
                'tempDir' => $tmp,
                'directionality' => app()->getLocale() === 'ar' ? 'rtl' : 'ltr',
            ]);
            $mpdf->autoScriptToLang = true;
            $mpdf->autoLangToFont = true;
            $mpdf->WriteHTML(view('clinic.exports.table', compact('clinic', 'title', 'periodLabel', 'columns', 'rows', 'totals'))->render());

            return response($mpdf->Output($filename.'.pdf', \Mpdf\Output\Destination::STRING_RETURN), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$filename.'.pdf"',
            ]);
        }

        // Excel-friendly CSV: UTF-8 BOM so Arabic opens correctly in Excel.
        $out = fopen('php://temp', 'r+');
        fputcsv($out, $columns);
        foreach ($rows as $row) {
            fputcsv($out, $row);
        }
        if ($totals) {
            fputcsv($out, $totals);
        }
        rewind($out);
        $csv = "\xEF\xBB\xBF".stream_get_contents($out);
        fclose($out);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'.csv"',
        ]);
    }
}
