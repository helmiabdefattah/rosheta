<?php

namespace App\Support;

use App\Models\Specialization;

/**
 * Which body chart a specialization examines on, and what its parts are.
 *
 * Specialisation slugs in this database are mostly generated hashes, so the
 * match is on the name — Arabic first, since that is what they are stored in,
 * with English keywords so a later entry typed in English still resolves.
 *
 * A specialisation with no chart simply gets no panel; that is the default, not
 * a failure. To add one, add its keywords and a region list here.
 */
class ClinicalChart
{
    public const TEETH = 'teeth';
    public const SKELETON = 'skeleton';
    public const EYES = 'eyes';

    /** chart => keywords that select it, matched against the normalised name. */
    private const KEYWORDS = [
        self::TEETH => ['اسنان', 'سنان', 'فم واسنان', 'dental', 'dentist', 'odonto'],
        self::SKELETON => ['عظام', 'عظم', 'كسور', 'ortho', 'skelet', 'bone', 'fracture'],
        self::EYES => ['رمد', 'عيون', 'عين', 'ophthalm', 'optom', 'eye'],
    ];

    /**
     * The chart for a specialisation, or null when it does not use one.
     */
    public static function forSpecialization(?Specialization $specialization): ?string
    {
        $name = self::normalise((string) ($specialization?->name ?? ''));

        if ($name === '') {
            return null;
        }

        foreach (self::KEYWORDS as $chart => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($name, self::normalise($keyword))) {
                    return $chart;
                }
            }
        }

        return null;
    }

    /** True when this chart pins a point inside the part, rather than selecting it whole. */
    public static function usesPoints(string $chart): bool
    {
        return $chart === self::EYES;
    }

    /**
     * Every region a chart accepts, as region key => label. Also the allow-list
     * the controller validates against, so a forged region cannot be stored.
     *
     * @return array<string, string>
     */
    public static function regions(string $chart): array
    {
        return match ($chart) {
            self::TEETH => self::teeth(),
            self::SKELETON => self::skeleton(),
            self::EYES => self::eyes(),
            default => [],
        };
    }

    public static function title(string $chart): string
    {
        return match ($chart) {
            self::TEETH => __('app.chart.teeth_title'),
            self::SKELETON => __('app.chart.skeleton_title'),
            self::EYES => __('app.chart.eyes_title'),
            default => __('app.chart.title'),
        };
    }

    /**
     * The 32 adult teeth in FDI notation: quadrant digit then position, so 11 is
     * the upper-right central incisor and 48 the lower-right third molar. It is
     * the notation dentists already write in, so the labels need no translating.
     *
     * @return array<string, string>
     */
    private static function teeth(): array
    {
        $teeth = [];

        foreach ([1, 2, 3, 4] as $quadrant) {
            foreach (range(1, 8) as $position) {
                $code = $quadrant.$position;
                $teeth[$code] = $code;
            }
        }

        return $teeth;
    }

    /** @return array<string, string> */
    private static function skeleton(): array
    {
        return [
            'skull' => __('app.chart.bone.skull'),
            'mandible' => __('app.chart.bone.mandible'),
            'cervical-spine' => __('app.chart.bone.cervical_spine'),
            'clavicle-right' => __('app.chart.bone.clavicle_right'),
            'clavicle-left' => __('app.chart.bone.clavicle_left'),
            'shoulder-right' => __('app.chart.bone.shoulder_right'),
            'shoulder-left' => __('app.chart.bone.shoulder_left'),
            'ribs' => __('app.chart.bone.ribs'),
            'thoracic-spine' => __('app.chart.bone.thoracic_spine'),
            'lumbar-spine' => __('app.chart.bone.lumbar_spine'),
            'humerus-right' => __('app.chart.bone.humerus_right'),
            'humerus-left' => __('app.chart.bone.humerus_left'),
            'elbow-right' => __('app.chart.bone.elbow_right'),
            'elbow-left' => __('app.chart.bone.elbow_left'),
            'forearm-right' => __('app.chart.bone.forearm_right'),
            'forearm-left' => __('app.chart.bone.forearm_left'),
            'wrist-right' => __('app.chart.bone.wrist_right'),
            'wrist-left' => __('app.chart.bone.wrist_left'),
            'hand-right' => __('app.chart.bone.hand_right'),
            'hand-left' => __('app.chart.bone.hand_left'),
            'pelvis' => __('app.chart.bone.pelvis'),
            'hip-right' => __('app.chart.bone.hip_right'),
            'hip-left' => __('app.chart.bone.hip_left'),
            'femur-right' => __('app.chart.bone.femur_right'),
            'femur-left' => __('app.chart.bone.femur_left'),
            'knee-right' => __('app.chart.bone.knee_right'),
            'knee-left' => __('app.chart.bone.knee_left'),
            'leg-right' => __('app.chart.bone.leg_right'),
            'leg-left' => __('app.chart.bone.leg_left'),
            'ankle-right' => __('app.chart.bone.ankle_right'),
            'ankle-left' => __('app.chart.bone.ankle_left'),
            'foot-right' => __('app.chart.bone.foot_right'),
            'foot-left' => __('app.chart.bone.foot_left'),
        ];
    }

    /** @return array<string, string> */
    private static function eyes(): array
    {
        return [
            'eye-right' => __('app.chart.eye.right'),
            'eye-left' => __('app.chart.eye.left'),
        ];
    }

    /**
     * Arabic is written with several interchangeable spellings — أ/إ/آ for ا,
     * ى for ي, ة for ه — so both sides of the comparison are flattened before
     * matching, along with case and the definite article's spacing.
     */
    private static function normalise(string $value): string
    {
        $value = mb_strtolower(trim($value));

        return strtr($value, [
            'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا',
            'ى' => 'ي', 'ة' => 'ه', 'ؤ' => 'و', 'ئ' => 'ي',
        ]);
    }
}
