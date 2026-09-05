<?php

namespace App\Demo;

use App\Models\DemoSession;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Hard-deletes one demo tenant: every row, every uploaded file, every cache
 * key and every login session belonging to it.
 *
 * Deletes run child -> parent explicitly rather than leaning on cascades.
 * Several relationships in this schema are ON DELETE SET NULL, not CASCADE
 * (attachments.appointment_id, patient_tests.appointment_id and .doctor_id,
 * users.doctor_id and .clinic_id, doctor_off_dates.clinic_id), and four tables
 * carry no foreign key at all (notifications, media, personal_access_tokens,
 * sessions). Relying on the database alone would leave those behind — with
 * their files.
 *
 * FOREIGN_KEY_CHECKS is deliberately NOT disabled: if the order is wrong we
 * want a loud error, not silent orphans.
 */
class DemoPurger
{
    /** Tenant-owned tables reached through appointments, in delete order. */
    private const APPOINTMENT_CHILDREN = [
        'examination_field_values',
        'appointment_items',
        'appointment_insurances',
        'collections',
        'medical_requests',
    ];

    /** Tenant-owned tables reached through the doctor, in delete order. */
    private const DOCTOR_CHILDREN = [
        'billable_items',
        'examination_fields',
        'insurance_collections',
        'doctor_off_dates',
    ];

    public function connection(): ConnectionInterface
    {
        return DB::connection(config('demo.connection'));
    }

    /**
     * Purge the tenant behind a demo session and close the session record.
     *
     * @return array<string,int> rows deleted per table, for logging and tests
     */
    public function purgeSession(DemoSession $session, string $reason = 'purged'): array
    {
        $deleted = [];

        if ($session->doctor_id !== null) {
            $deleted = $this->purgeDoctor((int) $session->doctor_id);
        }

        $session->forceFill([
            'doctor_id' => null,
            'doctor_user_id' => null,
            'assistant_user_id' => null,
            'ended_at' => $session->ended_at ?? now(),
            'end_reason' => $session->end_reason ?? $reason,
            'purged_at' => now(),
        ])->save();

        Log::info('Demo tenant purged', [
            'demo_session' => $session->id,
            'reason' => $session->end_reason,
            'deleted' => array_filter($deleted),
        ]);

        return $deleted;
    }

    /**
     * @return array<string,int>
     */
    public function purgeDoctor(int $doctorId): array
    {
        $db = $this->connection();
        $deleted = [];

        $clinicIds = $db->table('clinics')->where('doctor_id', $doctorId)->pluck('id')->all();
        $appointmentIds = $db->table('appointments')->where('doctor_id', $doctorId)->pluck('id')->all();
        $userIds = $this->tenantUserIds($doctorId, $clinicIds);
        $clientIds = $this->tenantClientIds($doctorId);

        // Files first: once the rows are gone we can no longer find the paths.
        $this->deleteFiles($doctorId, $appointmentIds, $clientIds, $userIds);

        $db->transaction(function () use ($db, $doctorId, $clinicIds, $appointmentIds, $userIds, $clientIds, &$deleted) {
            // --- prescriptions (items cascade, but be explicit about order) ---
            $prescriptionIds = $db->table('prescriptions')->where('doctor_id', $doctorId)
                ->orWhereIn('appointment_id', $appointmentIds)->pluck('id')->all();

            $deleted['prescription_items'] = $this->deleteIn($db, 'prescription_items', 'prescription_id', $prescriptionIds);
            $deleted['prescriptions'] = $this->deleteIn($db, 'prescriptions', 'id', $prescriptionIds);

            // --- everything hanging off an appointment ---
            foreach (self::APPOINTMENT_CHILDREN as $table) {
                $deleted[$table] = $this->deleteIn($db, $table, 'appointment_id', $appointmentIds);
            }

            // diagnoses is referenced by prescriptions.diagnosis_id, so it goes after.
            $deleted['diagnoses'] = $this->deleteIn($db, 'diagnoses', 'appointment_id', $appointmentIds);

            // SET NULL relationships: these survive their parent, so target them directly.
            $deleted['patient_tests'] = $db->table('patient_tests')
                ->where('doctor_id', $doctorId)
                ->orWhereIn('appointment_id', $appointmentIds)
                ->orWhereIn('client_id', $clientIds)
                ->delete();

            $deleted['attachments'] = $db->table('attachments')
                ->whereIn('appointment_id', $appointmentIds)
                ->orWhereIn('uploaded_by', $userIds)
                ->orWhere(function ($q) use ($clientIds) {
                    $q->where('attachable_type', \App\Models\Client::class)->whereIn('attachable_id', $clientIds);
                })
                ->delete();

            $deleted['appointments'] = $this->deleteIn($db, 'appointments', 'id', $appointmentIds);

            // --- doctor-keyed tables with no clinic path ---
            $conversationIds = $db->table('conversations')->where('doctor_id', $doctorId)->pluck('id')->all();
            $deleted['chat_messages'] = $this->deleteIn($db, 'chat_messages', 'conversation_id', $conversationIds);
            $deleted['conversations'] = $this->deleteIn($db, 'conversations', 'id', $conversationIds);

            $planIds = $db->table('medical_plans')->where('doctor_id', $doctorId)->pluck('id')->all();
            $deleted['medical_plan_items'] = $this->deleteIn($db, 'medical_plan_items', 'medical_plan_id', $planIds);
            $deleted['medical_plans'] = $this->deleteIn($db, 'medical_plans', 'id', $planIds);

            foreach (self::DOCTOR_CHILDREN as $table) {
                $deleted[$table] = $db->table($table)->where('doctor_id', $doctorId)->delete();
            }

            // --- clinic-keyed tables ---
            $deleted['clinic_doctor_working_hours'] = $this->deleteIn($db, 'clinic_doctor_working_hours', 'clinic_id', $clinicIds);
            $deleted['clinic_working_hours'] = $this->deleteIn($db, 'clinic_working_hours', 'clinic_id', $clinicIds);
            $deleted['clinic_doctor'] = $this->deleteIn($db, 'clinic_doctor', 'clinic_id', $clinicIds);

            // --- marketplace rows the onboarding seeder creates for lab results ---
            $requestIds = $this->deleteMarketplaceTrail($db, $clientIds, $deleted);

            // --- patient-owned rows with no cascade ---
            $deleted['reviews'] = $this->deleteIn($db, 'reviews', 'client_id', $clientIds);
            $deleted['bonus_points'] = $this->deleteIn($db, 'bonus_points', 'client_id', $clientIds);
            $deleted['feedbacks'] = $this->deleteIn($db, 'feedbacks', 'client_id', $clientIds);
            $deleted['quotes'] = $this->deleteIn($db, 'quotes', 'client_id', $clientIds);
            $deleted['client_addresses'] = $this->deleteIn($db, 'client_addresses', 'client_id', $clientIds);

            // --- polymorphic / FK-less tables ---
            $deleted['notifications'] = $this->deletePolymorphic($db, 'notifications', 'notifiable', [
                \App\Models\User::class => $userIds,
                \App\Models\Client::class => $clientIds,
            ]);

            $deleted['media'] = $this->deletePolymorphic($db, 'media', 'model', [
                \App\Models\User::class => $userIds,
                \App\Models\Doctor::class => [$doctorId],
            ]);

            $deleted['personal_access_tokens'] = $this->deletePolymorphic($db, 'personal_access_tokens', 'tokenable', [
                \App\Models\User::class => $userIds,
                \App\Models\Client::class => $clientIds,
            ]);

            $deleted['sessions'] = $this->deleteIn($db, 'sessions', 'user_id', $userIds);

            // --- the tenant itself ---
            $deleted['clients'] = $this->deleteIn($db, 'clients', 'id', $clientIds);
            $deleted['users'] = $this->deleteIn($db, 'users', 'id', $userIds);
            $deleted['clinics'] = $this->deleteIn($db, 'clinics', 'id', $clinicIds);
            $deleted['doctors'] = $db->table('doctors')->where('id', $doctorId)->delete();

            unset($requestIds);
        });

        return $deleted;
    }

    /**
     * offers.client_request_id and orders.client_request_id are ON DELETE
     * NO ACTION, so a demo patient with a seeded lab result cannot be deleted
     * until the offer and order rows go first — otherwise MySQL error 1451.
     *
     * @param  array<string,int>  $deleted
     */
    protected function deleteMarketplaceTrail(ConnectionInterface $db, array $clientIds, array &$deleted): array
    {
        $requestIds = $db->table('client_requests')->whereIn('client_id', $clientIds)->pluck('id')->all();

        $offerIds = $db->table('offers')->whereIn('client_request_id', $requestIds)->pluck('id')->all();
        $orderIds = $db->table('orders')->whereIn('client_request_id', $requestIds)->pluck('id')->all();

        $deleted['order_lines'] = $this->deleteIn($db, 'order_lines', 'order_id', $orderIds);
        $deleted['orders'] = $this->deleteIn($db, 'orders', 'id', $orderIds);
        $deleted['offer_lines'] = $this->deleteIn($db, 'offer_lines', 'offer_id', $offerIds);
        $deleted['offers'] = $this->deleteIn($db, 'offers', 'id', $offerIds);
        $deleted['client_request_lines'] = $this->deleteIn($db, 'client_request_lines', 'client_request_id', $requestIds);
        $deleted['client_requests'] = $this->deleteIn($db, 'client_requests', 'id', $requestIds);

        return $requestIds;
    }

    /** Doctor's own login, their assistants, and anyone attached to their clinics. */
    protected function tenantUserIds(int $doctorId, array $clinicIds): array
    {
        $db = $this->connection();

        $ids = $db->table('users')->where('doctor_id', $doctorId)->pluck('id')->all();

        if ($clinicIds !== []) {
            $ids = array_merge($ids, $db->table('users')->whereIn('clinic_id', $clinicIds)->pluck('id')->all());
        }

        $ownerId = $db->table('doctors')->where('id', $doctorId)->value('user_id');

        if ($ownerId !== null) {
            $ids[] = $ownerId;
        }

        return array_values(array_unique(array_filter($ids)));
    }

    /**
     * Patients belonging to this tenant.
     *
     * `clients` is a global table with no tenant key, so membership is derived
     * from the clinical rows that point at it — and a patient is only deleted
     * if no OTHER doctor has ever seen them. In the demo database that is
     * always true, but the check keeps this safe if the seeder ever reuses a
     * patient across tenants.
     */
    protected function tenantClientIds(int $doctorId): array
    {
        $db = $this->connection();

        $ids = collect()
            ->merge($db->table('appointments')->where('doctor_id', $doctorId)->pluck('client_id'))
            ->merge($db->table('diagnoses')->where('doctor_id', $doctorId)->pluck('client_id'))
            ->merge($db->table('prescriptions')->where('doctor_id', $doctorId)->pluck('client_id'))
            ->merge($db->table('medical_requests')->where('doctor_id', $doctorId)->pluck('client_id'))
            ->merge($db->table('patient_tests')->where('doctor_id', $doctorId)->pluck('client_id'))
            ->merge($db->table('conversations')->where('doctor_id', $doctorId)->pluck('client_id'))
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return [];
        }

        $sharedWithOtherDoctor = $db->table('appointments')
            ->whereIn('client_id', $ids)
            ->where('doctor_id', '!=', $doctorId)
            ->pluck('client_id')
            ->unique();

        return $ids->diff($sharedWithOtherDoctor)->values()->all();
    }

    /** Remove uploaded files before their rows disappear. */
    protected function deleteFiles(int $doctorId, array $appointmentIds, array $clientIds, array $userIds): void
    {
        $db = $this->connection();

        $paths = $db->table('attachments')
            ->whereIn('appointment_id', $appointmentIds)
            ->orWhereIn('uploaded_by', $userIds)
            ->orWhere(function ($q) use ($clientIds) {
                $q->where('attachable_type', \App\Models\Client::class)->whereIn('attachable_id', $clientIds);
            })
            ->pluck('file_path')
            ->filter()
            ->all();

        foreach ($paths as $path) {
            try {
                Storage::disk('public')->delete($path);
            } catch (\Throwable $e) {
                Log::warning('Demo purge could not delete a file', [
                    'doctor_id' => $doctorId, 'path' => $path, 'error' => $e->getMessage(),
                ]);
            }
        }

        // Anything written under the tenant's own prefix, if the demo disk is in use.
        try {
            Storage::disk(config('demo.disk'))->deleteDirectory(
                trim((string) config('demo.storage_prefix'), '/')."/{$doctorId}"
            );
        } catch (\Throwable) {
            // The demo disk is optional; nothing to clean if it is not configured.
        }
    }

    /*
     * There is deliberately no cache-invalidation step here.
     *
     * The clinic workspace caches nothing per tenant — the only Cache usage in
     * the application is WebviewBridgeController's short-lived `webview_bridge:`
     * nonces, which expire on their own and live under the demo cache prefix
     * anyway. Inventing keys to forget would be theatre, and forgetting them on
     * the wrong connection trips the production write guard.
     *
     * If per-clinic caching is ever added, invalidate it here.
     */

    protected function deleteIn(ConnectionInterface $db, string $table, string $column, array $ids): int
    {
        if ($ids === []) {
            return 0;
        }

        return $db->table($table)->whereIn($column, $ids)->delete();
    }

    /**
     * @param  array<class-string, array<int>>  $map
     */
    protected function deletePolymorphic(ConnectionInterface $db, string $table, string $morph, array $map): int
    {
        $count = 0;

        foreach ($map as $type => $ids) {
            if ($ids === []) {
                continue;
            }

            $count += $db->table($table)
                ->where("{$morph}_type", $type)
                ->whereIn("{$morph}_id", $ids)
                ->delete();
        }

        return $count;
    }
}
