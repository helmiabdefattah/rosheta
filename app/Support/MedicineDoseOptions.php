<?php

namespace App\Support;

/**
 * Dose choices offered for a medicine, picked from its dosage form.
 *
 * A doctor writing "1 tablet" for a syrup is a prescribing error the pharmacy
 * has to phone about, so the dose field on the prescription form offers only
 * units that make sense for the form the selected medicine actually comes in.
 * The list is a starting point, never a cage: the field still accepts free text
 * for anything unusual.
 */
class MedicineDoseOptions
{
    /**
     * Dosage form (as stored in medicines.dosage_form) => dose choices.
     * Forms are matched loosely, so "film coated tablet" still finds "tablet".
     */
    private const BY_FORM = [
        'tablet' => ['1/4 tablet', '1/2 tablet', '1 tablet', '1.5 tablet', '2 tablets', '3 tablets'],
        'capsule' => ['1 capsule', '2 capsules', '3 capsules'],
        'syrup' => ['2.5 ml', '5 ml', '7.5 ml', '10 ml', '15 ml', '1 teaspoon', '1 tablespoon'],
        'suspension' => ['2.5 ml', '5 ml', '10 ml', '15 ml', '1 teaspoon'],
        'solution' => ['2.5 ml', '5 ml', '10 ml', '15 ml'],
        'oral drops' => ['5 drops', '10 drops', '15 drops', '20 drops'],
        'eye drops' => ['1 drop', '2 drops', '1 drop each eye', '2 drops each eye'],
        'ear drops' => ['2 drops', '3 drops', '4 drops'],
        'nasal drops' => ['1 drop each nostril', '2 drops each nostril'],
        'sachet' => ['1/2 sachet', '1 sachet', '2 sachets'],
        'suppository' => ['1/2 suppository', '1 suppository'],
        'vial' => ['1/2 vial', '1 vial', '2 vials'],
        'ampoule' => ['1/2 ampoule', '1 ampoule', '2 ampoules'],
        'syringe' => ['1 syringe'],
        'pen' => ['as per pen scale'],
        'spray' => ['1 puff', '2 puffs', '1 spray each nostril', '2 sprays each nostril'],
        'inhaler' => ['1 puff', '2 puffs'],
        'cream' => ['thin layer', 'apply locally', 'fingertip unit'],
        'ointment' => ['thin layer', 'apply locally'],
        'gel' => ['thin layer', 'apply locally'],
        'lotion' => ['apply locally', 'apply to scalp'],
        'shampoo' => ['apply to scalp'],
        'serum' => ['a few drops', 'apply locally'],
        'mouth wash' => ['10 ml rinse', '15 ml rinse'],
        'vaginal douche' => ['1 application'],
        'powder' => ['1 sachet', '1 scoop'],
        'bottle' => ['5 ml', '10 ml', '15 ml'],
        'piece' => ['1 piece', '2 pieces'],
    ];

    /** Offered when the form is unknown or empty, so the field is never bare. */
    private const FALLBACK = ['1 unit', '2 units', '5 ml', '10 ml', 'as directed'];

    /**
     * @return list<string>
     */
    public static function for(?string $dosageForm): array
    {
        $form = trim(mb_strtolower((string) $dosageForm));

        if ($form === '') {
            return self::FALLBACK;
        }

        if (isset(self::BY_FORM[$form])) {
            return self::BY_FORM[$form];
        }

        // "film coated tablet", "prolonged release capsule", "nasal spray"…
        foreach (self::BY_FORM as $known => $options) {
            if (str_contains($form, $known)) {
                return $options;
            }
        }

        return self::FALLBACK;
    }
}
