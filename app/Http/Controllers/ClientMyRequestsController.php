<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\ClientRequest;
use App\Models\HomeNurseRequest;
use App\Models\InsuranceCompany;
use App\Models\Offer;
use App\Models\Order;
use App\Models\ClientAddress;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientMyRequestsController extends Controller
{
    /**
     * Pharmacy / lab requests can be changed only while still pending.
     * (Approved / rejected are final for the client workflow.)
     */
    private function canMutateClientRequest(ClientRequest $r): bool
    {
        return $r->status === 'pending';
    }

    /**
     * Home nursing requests can be changed only while pending (before scheduling/offers finalize flow).
     */
    private function canMutateNurseRequest(HomeNurseRequest $r): bool
    {
        return $r->status === 'pending';
    }

    private function appointmentStartsAt(Appointment $a): Carbon
    {
        $t = $a->appointment_time;
        $timeStr = $t instanceof \DateTimeInterface ? $t->format('H:i:s') : (string) $t;

        return Carbon::parse($a->appointment_date->format('Y-m-d').' '.$timeStr);
    }

    /** Notes can be edited only while the booking is still pending. */
    private function canMutateAppointment(Appointment $a): bool
    {
        return $a->status === 'pending';
    }

    /** Cancel (set status to cancelled) for pending or confirmed future appointments. */
    private function canCancelAppointment(Appointment $a): bool
    {
        if (! in_array($a->status, ['pending', 'confirmed'], true)) {
            return false;
        }

        return $this->appointmentStartsAt($a)->isFuture();
    }

    public function index(Request $request)
    {
        $client = Auth::guard('client')->user();
        abort_unless($client, 403);

        $type = $request->query('type', 'all');
        $allowed = ['all', 'medicine', 'test', 'radiology', 'nurse', 'clinic'];
        if (! in_array($type, $allowed, true)) {
            $type = 'all';
        }

        $rows = collect();

        if ($type === 'all' || in_array($type, ['medicine', 'test', 'radiology'], true)) {
            $q = ClientRequest::where('client_id', $client->id)->with(['address.area', 'lines']);
            if ($type !== 'all') {
                $q->where('type', $type);
            }
            foreach ($q->orderByDesc('created_at')->get() as $cr) {
                $rows->push([
                    'kind' => 'pharmacy_lab',
                    'id' => $cr->id,
                    'created_at' => $cr->created_at,
                    'model' => $cr,
                ]);
            }
        }

        if ($type === 'all' || $type === 'nurse') {
            foreach (
                HomeNurseRequest::where('client_id', $client->id)
                    ->with(['address.area'])
                    ->orderByDesc('created_at')
                    ->get() as $nr
            ) {
                $rows->push([
                    'kind' => 'nurse',
                    'id' => $nr->id,
                    'created_at' => $nr->created_at,
                    'model' => $nr,
                ]);
            }
        }

        if ($type === 'all' || $type === 'clinic') {
            foreach (
                Appointment::where('client_id', $client->id)
                    ->with(['clinic', 'doctor'])
                    ->orderByDesc('created_at')
                    ->get() as $appt
            ) {
                $rows->push([
                    'kind' => 'clinic',
                    'id' => $appt->id,
                    'created_at' => $appt->created_at,
                    'model' => $appt,
                ]);
            }
        }

        $sorted = $rows->sortByDesc(fn ($r) => $r['created_at']->timestamp)->values();

        $page = max(1, (int) $request->query('page', 1));
        $perPage = 12;
        $total = $sorted->count();
        $slice = $sorted->slice(($page - 1) * $perPage, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $slice,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('client.requests.index', [
            'rows' => $paginator,
            'filterType' => $type,
        ]);
    }

    public function showClientRequest(ClientRequest $clientRequest)
    {
        $client = Auth::guard('client')->user();
        abort_unless($client && (int) $clientRequest->client_id === (int) $client->id, 403);

        $clientRequest->load(['address.area', 'lines.medicine', 'lines.medicalTest', 'offers']);

        return view('client.requests.show-client-request', [
            'requestModel' => $clientRequest,
            'canMutate' => $this->canMutateClientRequest($clientRequest),
        ]);
    }

    public function editClientRequest(ClientRequest $clientRequest)
    {
        $client = Auth::guard('client')->user();
        abort_unless($client && (int) $clientRequest->client_id === (int) $client->id, 403);
        abort_unless($this->canMutateClientRequest($clientRequest), 403);

        $addresses = ClientAddress::where('client_id', $client->id)
            ->with(['city', 'area'])
            ->orderByDesc('id')
            ->get();

        $insuranceCompanies = InsuranceCompany::where('is_active', true)->orderBy('name')->get();

        return view('client.requests.edit-client-request', [
            'requestModel' => $clientRequest,
            'addresses' => $addresses,
            'insuranceCompanies' => $insuranceCompanies,
        ]);
    }

    public function updateClientRequest(Request $request, ClientRequest $clientRequest)
    {
        $client = Auth::guard('client')->user();
        abort_unless($client && (int) $clientRequest->client_id === (int) $client->id, 403);
        abort_unless($this->canMutateClientRequest($clientRequest), 403);

        $validated = $request->validate([
            'note' => 'nullable|string|max:2000',
            'client_address_id' => 'nullable|exists:client_addresses,id',
            'insurance_company_id' => 'nullable|exists:insurance_companies,id',
            'insurance_company_name' => 'nullable|string|max:255',
            'pregnant' => 'nullable|boolean',
            'diabetic' => 'nullable|boolean',
            'heart_patient' => 'nullable|boolean',
            'high_blood_pressure' => 'nullable|boolean',
        ]);

        $insuranceCompanyId = $validated['insurance_company_id'] ?? null;
        if ($request->filled('insurance_company_name')) {
            $company = \App\Models\InsuranceCompany::firstOrCreate(
                ['name' => $request->insurance_company_name],
                ['is_active' => true]
            );
            $insuranceCompanyId = $company->id;
        }

        if ($validated['client_address_id'] ?? null) {
            abort_unless(
                ClientAddress::where('id', $validated['client_address_id'])->where('client_id', $client->id)->exists(),
                403
            );
        }

        $clientRequest->update([
            'note' => $validated['note'] ?? null,
            'client_address_id' => $validated['client_address_id'] ?? null,
            'insurance_company_id' => $insuranceCompanyId,
            'pregnant' => $request->boolean('pregnant'),
            'diabetic' => $request->boolean('diabetic'),
            'heart_patient' => $request->boolean('heart_patient'),
            'high_blood_pressure' => $request->boolean('high_blood_pressure'),
        ]);

        return redirect()
            ->route('client.requests.pharmacy-lab.show', $clientRequest)
            ->with('success', app()->getLocale() === 'ar' ? 'تم تحديث الطلب.' : 'Request updated.');
    }

    public function destroyClientRequest(ClientRequest $clientRequest)
    {
        $client = Auth::guard('client')->user();
        abort_unless($client && (int) $clientRequest->client_id === (int) $client->id, 403);
        abort_unless($this->canMutateClientRequest($clientRequest), 403);

        if (Order::where('client_request_id', $clientRequest->id)->exists()) {
            return back()->withErrors([
                'delete' => app()->getLocale() === 'ar'
                    ? 'لا يمكن حذف الطلب لوجود طلب شراء مرتبط به.'
                    : 'This request cannot be deleted because it has a linked order.',
            ]);
        }

        DB::transaction(function () use ($clientRequest) {
            $offers = Offer::where('client_request_id', $clientRequest->id)->get();
            foreach ($offers as $offer) {
                $offer->lines()->delete();
                $offer->delete();
            }
            $clientRequest->lines()->delete();
            $clientRequest->delete();
        });

        return redirect()
            ->route('client.requests.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم حذف الطلب.' : 'Request deleted.');
    }

    public function destroyNurseRequest(HomeNurseRequest $home_nurse_request)
    {
        $client = Auth::guard('client')->user();
        abort_unless($client && (int) $home_nurse_request->client_id === (int) $client->id, 403);
        abort_unless($this->canMutateNurseRequest($home_nurse_request), 403);

        DB::transaction(function () use ($home_nurse_request) {
            $home_nurse_request->visits()->delete();
            $home_nurse_request->offers()->delete();
            $home_nurse_request->delete();
        });

        return redirect()
            ->route('client.requests.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم حذف طلب التمريض.' : 'Nursing request deleted.');
    }

    public function showAppointment(Appointment $appointment)
    {
        $client = Auth::guard('client')->user();
        abort_unless($client && $appointment->client_id && (int) $appointment->client_id === (int) $client->id, 403);

        $appointment->load(['clinic.area', 'clinic.city', 'doctor.specialization']);

        return view('client.requests.show-appointment', [
            'appointment' => $appointment,
            'canMutate' => $this->canMutateAppointment($appointment),
            'canCancel' => $this->canCancelAppointment($appointment),
        ]);
    }

    public function editAppointment(Appointment $appointment)
    {
        $client = Auth::guard('client')->user();
        abort_unless($client && $appointment->client_id && (int) $appointment->client_id === (int) $client->id, 403);
        abort_unless($this->canMutateAppointment($appointment), 403);

        return view('client.requests.edit-appointment', [
            'appointment' => $appointment->load(['clinic', 'doctor']),
        ]);
    }

    public function updateAppointment(Request $request, Appointment $appointment)
    {
        $client = Auth::guard('client')->user();
        abort_unless($client && $appointment->client_id && (int) $appointment->client_id === (int) $client->id, 403);
        abort_unless($this->canMutateAppointment($appointment), 403);

        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        $appointment->update([
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('client.requests.clinic.show', $appointment)
            ->with('success', app()->getLocale() === 'ar' ? 'تم تحديث الموعد.' : 'Appointment updated.');
    }

    public function cancelAppointment(Appointment $appointment)
    {
        $client = Auth::guard('client')->user();
        abort_unless($client && $appointment->client_id && (int) $appointment->client_id === (int) $client->id, 403);
        abort_unless($this->canCancelAppointment($appointment), 403);

        $appointment->update(['status' => 'cancelled']);

        return redirect()
            ->route('client.requests.index')
            ->with('success', app()->getLocale() === 'ar' ? 'تم إلغاء الموعد.' : 'Appointment cancelled.');
    }
}
