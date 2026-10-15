<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A note pinned to a part of the body on a patient's clinical chart.
 * See the create migration for why these hang off the patient, not the visit.
 */
class ClinicalChartNote extends Model
{
    protected $fillable = [
        'client_id', 'appointment_id', 'doctor_id',
        'chart', 'region', 'condition', 'treatment', 'point_x', 'point_y', 'note',
    ];

    protected $casts = [
        'condition' => \App\Enums\DentalToothCondition::class,
        'treatment' => \App\Enums\DentalToothTreatment::class,
        'point_x' => 'float',
        'point_y' => 'float',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /** True when this note carries a pinned position rather than a whole part. */
    public function hasPoint(): bool
    {
        return $this->point_x !== null && $this->point_y !== null;
    }

    /**
     * A patient's current teeth marks, keyed by Palmer code (UR6, DL4…) for the
     * dental-lab chart. Each entry = the latest condition (colour) and treatment
     * (symbol) recorded on that tooth, so the lab chart can show tooth states the
     * same way the examination chart does.
     *
     * @return array<string, array{fill: string, symbol: string, label: string}>
     */
    public static function teethMarks(int $clientId): array
    {
        $notes = static::where('client_id', $clientId)
            ->where('chart', \App\Support\ClinicalChart::TEETH)
            ->where(fn ($q) => $q->whereNotNull('condition')->orWhereNotNull('treatment'))
            ->latest('id')
            ->get();

        $out = [];
        foreach ($notes->groupBy('region') as $region => $group) {
            $cond = optional($group->first(fn ($n) => $n->condition !== null))->condition;
            $treat = optional($group->first(fn ($n) => $n->treatment !== null))->treatment;
            if (! $cond && ! $treat) {
                continue;
            }

            // The lab chart is keyed by Palmer codes; chart notes store FDI.
            $code = \App\Support\DentalTooth::fromFdi((string) $region);
            if ($code === null) {
                continue;
            }

            $out[$code] = [
                'fill' => $cond?->color() ?? $treat->color(),
                'symbol' => $treat?->symbol() ?? $cond->symbol(),
                'label' => trim(($cond?->label() ?? '').' '.($treat?->label() ?? '')),
            ];
        }

        return $out;
    }
}
