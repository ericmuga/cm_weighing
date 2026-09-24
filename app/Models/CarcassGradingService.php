<?php

namespace App\Models;

/**
 * Beef (item_code BG1021) carcass grading scorecard, derived from
 * "Grading template formulation (2).xlsx":
 *
 *   - Verdict 1: fixed points for 6 visual attributes (Dentition, Fat Cover,
 *     Bruising/Blemishes, Fat Colour, Meat Colour, Muscle conformation),
 *     summed. This is the same sum whether you look at the "worst case" or
 *     "best case" tab — those two tabs just show the lowest/highest Verdict 1
 *     each grade's profile can produce.
 *   - Every grade has a Verdict 1 band (min..max). Because the bands
 *     overlap (e.g. FAQ and Standard share the same worst-case attribute
 *     combination, both scoring 10), a carcass's Verdict 1 can land in more
 *     than one grade's band at once — that tie is the "indeterminate" state.
 *   - Carcass weight is banded into the same 5 tiers the grades use
 *     (Premium=5 .. Commercial=1) and is the supreme, final say — per the
 *     sheet ("Weight cannot improve the grading") it can only ever pull the
 *     grade down to what the weight tier supports, never up.
 *   - Weight is now mandatory to finalize a grade: even when verdict1 lands
 *     unambiguously in exactly one grade's band, compute() only returns that
 *     as a *provisional* suggestion (awaiting_weight = true) until weight is
 *     known, since weight could still pull it down further. A carcass can be
 *     graded (parameters + a manual classification) before weight is known —
 *     QAController::recomputeAutoGrading() re-runs compute() once weight
 *     arrives, to finalize it.
 *   - A carcass whose weight doesn't fall in any defined band (heavier than
 *     Premium's 300kg ceiling), or whose verdict1 still can't be resolved to
 *     a single grade even with weight applied, is flagged (is_indeterminate)
 *     for QA to review rather than guessed at.
 *   - A handful of attribute values are hard overrides that bypass verdict1/
 *     verdict2 scoring entirely (see resolveOverride()): fat cover = None,
 *     bruising = Detained/Condemned, and muscle = Poorly conformed. These
 *     don't need weight and apply unconditionally; when more than one is
 *     true at once the worst outcome wins (Condemned beats Poor C beats
 *     Commercial).
 */
class CarcassGradingService
{
    public const PREMIUM    = 1;
    public const HIGHGRADE  = 2;
    public const COMMERCIAL = 3;
    public const POOR_C     = 4;
    public const FAQ        = 8;
    public const STANDARD   = 9;
    // Not a sellable grade — bruising = Condemned short-circuits straight to
    // this, bypassing scoring and weight entirely.
    public const CONDEMNED  = 10;

    public const LABELS = [
        self::PREMIUM    => 'Premium',
        self::HIGHGRADE  => 'High Grade',
        self::FAQ        => 'FAQ',
        self::STANDARD   => 'Standard',
        self::COMMERCIAL => 'Commercial',
        self::POOR_C     => 'Poor C',
        self::CONDEMNED  => 'Condemned',
    ];

    // Option values (see grading-v2.blade.php) that trigger a hard override
    // per "Grading template formulation (2).xlsx"'s additional rules, rather
    // than feeding into the normal verdict1/verdict2 scoring.
    private const FAT_COVER_NONE     = 0;
    private const BRUISING_DETAINED  = 4;
    private const BRUISING_CONDEMNED = 5;
    private const MUSCLE_POOR        = 3;

    /**
     * Grade => tier (also the weight tier it requires) and Verdict 1 band.
     * Ranked best (tier 5) to worst (tier 0).
     */
    private const GRADE_BANDS = [
        self::PREMIUM    => ['tier' => 5, 'min' => 17, 'max' => 18],
        self::HIGHGRADE  => ['tier' => 4, 'min' => 13, 'max' => 17],
        self::FAQ        => ['tier' => 3, 'min' => 10, 'max' => 16],
        self::STANDARD   => ['tier' => 2, 'min' => 10, 'max' => 16],
        self::COMMERCIAL => ['tier' => 1, 'min' => 8,  'max' => 14],
        // Poor C has no weight determinant of its own in the sheet — Commercial's
        // (tier 1, "below 120kg") is satisfied by any real weight, so at the
        // shared boundary score of 8 Commercial always wins on the merits; Poor C
        // is reached only when the visual score alone is below Commercial's floor.
        self::POOR_C     => ['tier' => 0, 'min' => 0,  'max' => 7],
    ];

    /** Carcass weight (kg, inclusive lower bound) => tier. Checked highest first. */
    private const WEIGHT_BANDS = [
        ['min' => 200, 'tier' => 5], // Premium: 200-300kg
        ['min' => 170, 'tier' => 4], // High Grade: 170-199.9kg
        ['min' => 150, 'tier' => 3], // FAQ: 150-169.9kg
        ['min' => 120, 'tier' => 2], // Standard: 120-149.9kg
        ['min' => 0,   'tier' => 1], // Commercial: below 120kg
    ];

    /** Premium's weight band has an upper bound; above it the carcass falls off the defined bands entirely. */
    private const MAX_WEIGHT = 300;

    /**
     * Submitted option value => points, per attribute field. Values match the
     * existing dropdown option values in grading-v2.blade.php (unchanged, so
     * historical rows keep their meaning); fat_cover (0, 4) and fat_color (3)
     * are the two option values added to complete the scorecard. Bruising
     * option 2 ("Extensive bruises") was retired from the form; 4 and 5 were
     * repurposed to Detained/Condemned, per "Grading template formulation (2).xlsx".
     */
    public const POINTS = [
        'dentition'  => [1 => 1, 2 => 2, 3 => 3, 4 => 3, 5 => 3],
        'fat_cover'  => [4 => 4, 1 => 3, 2 => 2, 3 => 1, 0 => 0],
        'fat_color'  => [1 => 3, 3 => 2, 2 => 1],
        'meat_color' => [1 => 2, 2 => 1],
        'bruising'   => [0 => 3, 1 => 2, 3 => 1, 4 => 1, 5 => 0],
        'muscle'     => [1 => 3, 2 => 2, 3 => 1],
    ];

    /**
     * This scorecard only applies to cattle. Lamb (BG1900) and Goat (BG1202)
     * use their own, unrelated classification schemes.
     */
    public static function appliesTo(?string $itemCode): bool
    {
        return $itemCode === 'BG1021';
    }

    /**
     * @param array $attributes ['dentition'=>, 'fat_cover'=>, 'fat_color'=>, 'meat_color'=>, 'bruising'=>, 'muscle'=>]
     * @param float|null $weight settlement weight in kg (slaughter_data.settlement_weight)
     * @return array{
     *     verdict1: int|null,
     *     verdict2: int|null,
     *     classification: int|null,
     *     is_indeterminate: bool,
     *     awaiting_weight: bool,
     *     candidates: int[],
     *     missing: string[],
     * }
     */
    public static function compute(array $attributes, ?float $weight): array
    {
        [$verdict1, $missing] = self::scoreAttributes($attributes);

        $result = [
            'verdict1' => $verdict1,
            'verdict2' => null,
            'classification' => null,
            'is_indeterminate' => false,
            'awaiting_weight' => false,
            'candidates' => [],
            'missing' => $missing,
        ];

        // Weight is computed up front (for the record, per the class
        // docblock) even when an override below ends up deciding the grade
        // — it never overrides an override, but it's still worth logging.
        $hasWeight = $weight !== null && $weight > 0;
        $weightTier = $hasWeight ? self::weightTier($weight) : null;
        if ($hasWeight && $weightTier !== null && $verdict1 !== null) {
            $result['verdict2'] = $verdict1 + $weightTier;
        }

        // Hard overrides bypass verdict1/verdict2 scoring entirely and don't
        // need weight — worst-first, so the most severe one wins when more
        // than one applies at once (see resolveOverride()).
        $override = self::resolveOverride($attributes);
        if ($override !== null) {
            $result['classification'] = $override;

            return $result;
        }

        if ($verdict1 === null) {
            return $result;
        }

        $candidates = [];
        foreach (self::GRADE_BANDS as $grade => $band) {
            if ($verdict1 >= $band['min'] && $verdict1 <= $band['max']) {
                $candidates[] = $grade;
            }
        }
        usort($candidates, fn ($a, $b) => self::GRADE_BANDS[$b]['tier'] <=> self::GRADE_BANDS[$a]['tier']);
        $result['candidates'] = $candidates;

        if (empty($candidates)) {
            // Not reachable given the point table above, but fail safe rather
            // than silently leaving classification null.
            $result['classification'] = self::POOR_C;
            $result['is_indeterminate'] = true;

            return $result;
        }

        // Weight is now mandatory to finalize a normal-path grade. Without it
        // (or if it falls outside every defined band, e.g. > 300kg), offer
        // the best-case candidate as a provisional suggestion only — weight
        // can still only ever pull it down from here, never raise it.
        if (!$hasWeight) {
            $result['classification'] = $candidates[0];
            $result['awaiting_weight'] = true;

            return $result;
        }

        if ($weightTier === null) {
            $result['classification'] = $candidates[0];
            $result['is_indeterminate'] = true;

            return $result;
        }

        if (count($candidates) === 1) {
            $grade = $candidates[0];
            // Weight can only ever pull the grade down, never up.
            if ($weightTier < self::GRADE_BANDS[$grade]['tier']) {
                $grade = self::gradeForTier($weightTier);
            }
            $result['classification'] = $grade;

            return $result;
        }

        // Tied bands (e.g. FAQ/Standard overlap) — weight breaks the tie, and
        // only downward: pick the best one the actual weight tier supports.
        $supported = array_values(array_filter(
            $candidates,
            fn ($grade) => self::GRADE_BANDS[$grade]['tier'] <= $weightTier
        ));

        if ($supported) {
            usort($supported, fn ($a, $b) => self::GRADE_BANDS[$b]['tier'] <=> self::GRADE_BANDS[$a]['tier']);
            $result['classification'] = $supported[0];

            return $result;
        }

        // Weight rules out every tied candidate (it's below all of them) —
        // weight is the supreme verdict (rule g), so it resolves the grade
        // directly, same as the single-candidate downgrade above. This is a
        // decisive result, not an ambiguous one, so no is_indeterminate flag.
        $result['classification'] = self::gradeForTier($weightTier);

        return $result;
    }

    public static function label(?int $classification): string
    {
        return self::LABELS[$classification] ?? '--';
    }

    /**
     * Hard overrides, per "Grading template formulation (2).xlsx"'s
     * additional rules — unconditional, don't need weight, and checked
     * worst-first so the most severe one wins when several are true at once:
     * a condemned carcass beats Poor C (detained or poorly conformed
     * muscle), which beats a fat-cover-driven Commercial.
     */
    private static function resolveOverride(array $attributes): ?int
    {
        $bruising = self::intOrNull($attributes['bruising'] ?? null);
        $muscle = self::intOrNull($attributes['muscle'] ?? null);
        $fatCover = self::intOrNull($attributes['fat_cover'] ?? null);

        if ($bruising === self::BRUISING_CONDEMNED) {
            return self::CONDEMNED;
        }

        if ($bruising === self::BRUISING_DETAINED || $muscle === self::MUSCLE_POOR) {
            return self::POOR_C;
        }

        if ($fatCover === self::FAT_COVER_NONE) {
            return self::COMMERCIAL;
        }

        return null;
    }

    private static function intOrNull($value): ?int
    {
        return ($value === null || $value === '') ? null : (int) $value;
    }

    /**
     * @return array{0: int|null, 1: string[]} [verdict1 total or null if any attribute is missing/invalid, missing field names]
     */
    private static function scoreAttributes(array $attributes): array
    {
        $total = 0;
        $missing = [];

        foreach (self::POINTS as $field => $map) {
            $value = $attributes[$field] ?? null;
            if ($value === null || $value === '') {
                $missing[] = $field;
                continue;
            }
            $value = (int) $value;
            if (!array_key_exists($value, $map)) {
                $missing[] = $field;
                continue;
            }
            $total += $map[$value];
        }

        return [$missing ? null : $total, $missing];
    }

    private static function weightTier(float $weight): ?int
    {
        if ($weight > self::MAX_WEIGHT) {
            // Heavier than Premium's band allows — falls off the defined
            // weight bands entirely, flag for QA review rather than guess.
            return null;
        }

        foreach (self::WEIGHT_BANDS as $band) {
            if ($weight >= $band['min']) {
                return $band['tier'];
            }
        }

        return 1;
    }

    private static function gradeForTier(int $tier): int
    {
        foreach (self::GRADE_BANDS as $grade => $band) {
            if ($band['tier'] === $tier) {
                return $grade;
            }
        }

        return self::POOR_C;
    }
}
