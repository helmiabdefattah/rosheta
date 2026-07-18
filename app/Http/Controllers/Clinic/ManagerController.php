<?php

namespace App\Http\Controllers\Clinic;

use App\Http\Controllers\Clinic\Concerns\ClinicContext;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Collection;
use App\Models\InsuranceCollection;
use App\Models\InsuranceCompany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Doctor "Manager" area: money and insurance reporting for the doctor's clinics.
 * Everything is scoped to the authenticated doctor (all their clinics).
 */
class ManagerController extends Controller
{
    use ClinicContext;

    /** Landing page with the four report cards. */
    public function index(Request $request): View
    {
        $doctor = $this->clinicDoctor($request);
        $clinic = $this->activeClinic($doctor);

        return view('clinic.doctor.manager.index', compact('doctor', 'clinic'));
    }

    /** Patient payments collected in the period, with totals. */
    public function collections(Request $request): View
    {
        $doctor = $this->clinicDoctor($request);
        [$from, $to] = $this->period($request);

        $collections = Collection::query()
            ->whereHas('appointment', fn ($q) => $q->where('doctor_id', $doctor->id))
            ->with(['appointment.client', 'collector'])
            ->whereDate('collected_at', '>=', $from)
            ->whereDate('collected_at', '<=', $to)
            ->orderByDesc('collected_at')
            ->get();

        $total = (float) $collections->sum('amount');

        return view('clinic.doctor.manager.collections', [
            'collections' => $collections,
            'total' => $total,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
        ]);
    }

    /** Reservations (appointments) report with money + insurance, and totals. */
    public function patients(Request $request): View
    {
        $doctor = $this->clinicDoctor($request);
        [$from, $to] = $this->period($request);
        $status = $request->query('status');
        $companyId = $request->query('insurance_company_id');
        $search = trim((string) $request->query('search', ''));

        $appointments = Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->whereDate('scheduled_at', '>=', $from)
            ->whereDate('scheduled_at', '<=', $to)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($companyId, fn ($q) => $q->whereHas(
                'insurance',
                fn ($q2) => $q2->where('insurance_company_id', $companyId)
            ))
            // Match on the patient's name or phone number.
            ->when($search !== '', fn ($q) => $q->whereHas(
                'client',
                fn ($q2) => $q2->where('name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
            ))
            ->with(['client', 'items', 'collections', 'insurance.insuranceCompany'])
            ->orderByDesc('scheduled_at')
            ->get();

        $totals = [
            'due' => round($appointments->sum(fn (Appointment $a) => $a->dueAmount()), 2),
            'collected' => round($appointments->sum(fn (Appointment $a) => $a->collectedAmount()), 2),
            'insurance' => round($appointments->sum(fn (Appointment $a) => (float) ($a->insurance?->insurance_amount ?? 0)), 2),
        ];

        return view('clinic.doctor.manager.patients', [
            'appointments' => $appointments,
            'totals' => $totals,
            'companies' => $this->companies(),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'status' => $status,
            'companyId' => $companyId,
            'search' => $search,
            'statuses' => array_keys(Appointment::STATUSES),
        ]);
    }

    /** List + add lump-sum payouts received from insurance companies. */
    public function insuranceCollections(Request $request): View
    {
        $doctor = $this->clinicDoctor($request);
        [$from, $to] = $this->period($request);
        $companyId = $request->query('insurance_company_id');

        $collections = InsuranceCollection::query()
            ->where('doctor_id', $doctor->id)
            ->when($companyId, fn ($q) => $q->where('insurance_company_id', $companyId))
            ->whereDate('collected_on', '>=', $from)
            ->whereDate('collected_on', '<=', $to)
            ->with(['insuranceCompany', 'creator'])
            ->orderByDesc('collected_on')
            ->get();

        return view('clinic.doctor.manager.insurance-collections', [
            'collections' => $collections,
            'total' => (float) $collections->sum('amount'),
            'companies' => $this->companies(),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'companyId' => $companyId,
        ]);
    }

    public function storeInsuranceCollection(Request $request): RedirectResponse
    {
        $doctor = $this->clinicDoctor($request);

        $data = $request->validate([
            'insurance_company_id' => ['nullable', 'exists:insurance_companies,id'],
            'new_company_name' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999'],
            'collected_on' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        if (blank($data['insurance_company_id']) && blank($data['new_company_name'])) {
            return back()->withErrors(['insurance_company_id' => __('app.insurance.company_required')]);
        }

        if (filled($data['new_company_name'])) {
            $companyId = InsuranceCompany::firstOrCreate(
                ['name' => trim($data['new_company_name'])],
                ['is_active' => true],
            )->id;
        } else {
            $companyId = $data['insurance_company_id'];
        }

        InsuranceCollection::create([
            'doctor_id' => $doctor->id,
            'insurance_company_id' => $companyId,
            'amount' => $data['amount'],
            'collected_on' => $data['collected_on'],
            'note' => $data['note'] ?? null,
            'created_by' => $request->user()?->id,
        ]);

        return back()->with('status', __('app.manager.collection_added'));
    }

    /** Per-company claimed / collected / pending totals for the period. */
    public function insuranceReport(Request $request): View
    {
        $doctor = $this->clinicDoctor($request);
        [$from, $to] = $this->period($request);
        $companyId = $request->query('insurance_company_id');

        // Total claimed per company (insurance_amount on insured visits).
        $claimed = \App\Models\AppointmentInsurance::query()
            ->selectRaw('insurance_company_id, SUM(insurance_amount) as total')
            ->whereHas('appointment', fn ($q) => $q->where('doctor_id', $doctor->id)
                ->whereDate('scheduled_at', '>=', $from)
                ->whereDate('scheduled_at', '<=', $to))
            ->when($companyId, fn ($q) => $q->where('insurance_company_id', $companyId))
            ->groupBy('insurance_company_id')
            ->pluck('total', 'insurance_company_id');

        // Total collected per company (manual payouts).
        $collected = InsuranceCollection::query()
            ->selectRaw('insurance_company_id, SUM(amount) as total')
            ->where('doctor_id', $doctor->id)
            ->whereDate('collected_on', '>=', $from)
            ->whereDate('collected_on', '<=', $to)
            ->when($companyId, fn ($q) => $q->where('insurance_company_id', $companyId))
            ->groupBy('insurance_company_id')
            ->pluck('total', 'insurance_company_id');

        $ids = $claimed->keys()->merge($collected->keys())->unique()->values();
        $companies = InsuranceCompany::whereIn('id', $ids)->get()->keyBy('id');

        $rows = $ids->map(function ($id) use ($claimed, $collected, $companies) {
            $c = (float) ($claimed[$id] ?? 0);
            $p = (float) ($collected[$id] ?? 0);

            return [
                'company' => $companies[$id] ?? null,
                'claimed' => round($c, 2),
                'collected' => round($p, 2),
                'pending' => round($c - $p, 2),
            ];
        })->sortByDesc('pending')->values();

        $totals = [
            'claimed' => round($rows->sum('claimed'), 2),
            'collected' => round($rows->sum('collected'), 2),
            'pending' => round($rows->sum('pending'), 2),
        ];

        return view('clinic.doctor.manager.insurance-report', [
            'rows' => $rows,
            'totals' => $totals,
            'companies' => $this->companies(),
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'companyId' => $companyId,
        ]);
    }

    /** Parse ?from=&to= (Y-m-d); default to the current month. */
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

    private function companies()
    {
        return InsuranceCompany::orderBy('name')->get();
    }
}
