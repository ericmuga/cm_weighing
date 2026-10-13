<?php

namespace App\Models;

/**
 * Lamb (item_code BG1900) carcass grading scorecard, from the lamb sheet of
 * the grading template. Unlike beef (CarcassGradingService), weight is part
 * of the score rather than a cap:
 *
 *   - Verdict 1: points for 5 visual attributes (Fat Cover, Bruising,
 *     Fat Colour, Meat Colour, Muscle conformation) — no dentition. Max 14.
 *   - Verdict 2: Verdict 1 + weight points (14-30kg = 9, 11-13.9kg = 6,
 *     below 11kg = 3). The grade bands are on Verdict 2:
 *     Class 1 = 21..23, Class 2 = 15..20, Class R = 8..17.
 *   - Class 2 and Class R overlap at 15..17. Class R only reaches that high
 *     with the below-11kg weight, which is already a hard rule for R, so the
 *     overlap resolves to Class 2.
 *   - Hard rules, worst first: Condemned bruising -> Condemned; Severe or
 *     Detained bruising, Deep yellow fat, Poorly conformed muscle, or under
 *     11kg -> Class R; over 30kg -> Class 2.
 *
 * Returns the same shape as CarcassGradingService::compute() so callers can
 * treat both the same way.
 */
class LambGradingService
{
    // Same ids the grading form and reports already use for lamb.
    public const CLASS_1   = 5;
    public const CLASS_2   = 6;
    public const CLASS_R   = 7;
    public const CONDEMNED = CarcassGradingService::CONDEMNED;

    public const LABELS = [
        self::CLASS_1   => '1st Grade',
        self::CLASS_2   => '2nd Grade',
        self::CLASS_R   => 'Class R',
        self::CONDEMNED => 'Condemned',
    ];

    // Option values from grading-v2.blade.php that trigger a hard rule.
    private const FAT_COLOR_DEEP_YELLOW = 2;
    private const BRUISING_SEVERE       = 3;
    private const BRUISING_DETAINED     = 4;
    private const BRUISING_CONDEMNED    = 5;
    private const MUSCLE_POOR           = 3;

    /** Verdict 2 bands, best first. Class 2 wins the 15..17 overlap with Class R. */
    private const GRADE_BANDS = [
        self::CLASS_1 => ['min' => 21, 'max' => 23],
        self::CLASS_2 => ['min' => 15, 'max' => 20],
        self::CLASS_R => ['min' => 8,  'max' => 17],
    ];

    private const MIN_WEIGHT = 11; // below this -> Class R
    private const MAX_WEIGHT = 30; // above this -> Class 2

    /**
     * Submitted option value => points. Option values match the beef form's
     * dropdowns; Marbling (4) and None (0) fat cover aren't on the lamb sheet,
     * so they count as missing.
     */
    public const POINTS = [
        'fat_cover'  => [1 => 3, 2 => 2, 3 => 1],
        'bruising'   => [0 => 3, 1 => 2, 3 => 1, 4 => 1, 5 => 0],
        'fat_color'  => [1 => 3, 3 => 2, 2 => 1],
        'meat_color' => [1 => 2, 2 => 1],
        'muscle'     => [1 => 3, 2 => 2, 3 => 1],
    ];

    public static function appliesTo(?string $itemCode): bool
    {
        return $itemCode === 'BG1900';
    }

    /**
     * @param array $attributes ['fat_cover'=>, 'fat_color'=>, 'meat_color'=>, 'bruising'=>, 'muscle'=>] (dentition ignored)
     * @param float|null $weight settlement weight in kg
     */
    public static function compute(array $attributes, ?float $weight): array
    {
        [$verdict1, $missing] = self::scoreAttributes($attributes);

        $hasWeight = $weight !== null && $weight > 0;
        $weightPoints = $hasWeight ? self::weightPoints($weight) : null;

        $result = [
            'verdict1' => $verdict1,
            'verdict2' => ($verdict1 !== null && $weightPoints !== null) ? $verdict1 + $weightPoints : null,
            'classification' => null,
            'is_indeterminate' => false,
            'awaiting_weight' => false,
            'candidates' => [],
            'missing' => $missing,
        ];

        $override = self::resolveOverride($attributes, $hasWeight ? $weight : null);
        if ($override !== null) {
            $result['classification'] = $override;

            return $result;
        }

        if ($verdict1 === null) {
            return $result;
        }

        // Without weight, suggest what the best weight band would give —
        // provisional until the weigh-in is recorded.
        if (!$hasWeight) {
            $result['classification'] = self::gradeForScore($verdict1 + 9, $result);
            $result['awaiting_weight'] = true;

            return $result;
        }

        $result['classification'] = self::gradeForScore($result['verdict2'], $result);

        return $result;
    }

    public static function label(?int $classification): string
    {
        return self::LABELS[$classification] ?? '--';
    }

    /** Worst first, so the most severe rule wins when several apply. */
    private static function resolveOverride(array $attributes, ?float $weight): ?int
    {
        $bruising = self::intOrNull($attributes['bruising'] ?? null);
        $fatColor = self::intOrNull($attributes['fat_color'] ?? null);
        $muscle = self::intOrNull($attributes['muscle'] ?? null);

        if ($bruising === self::BRUISING_CONDEMNED) {
            return self::CONDEMNED;
        }

        if ($bruising === self::BRUISING_SEVERE
            || $bruising === self::BRUISING_DETAINED
            || $fatColor === self::FAT_COLOR_DEEP_YELLOW
            || $muscle === self::MUSCLE_POOR
            || ($weight !== null && $weight < self::MIN_WEIGHT)) {
            return self::CLASS_R;
        }

        if ($weight !== null && $weight > self::MAX_WEIGHT) {
            return self::CLASS_2;
        }

        return null;
    }

    /** Best band containing the score; records every matching band as candidates. */
    private static function gradeForScore(int $score, array &$result): int
    {
        foreach (self::GRADE_BANDS as $grade => $band) {
            if ($score >= $band['min'] && $score <= $band['max']) {
                $result['candidates'][] = $grade;
            }
        }

        if (empty($result['candidates'])) {
            // Below every band — not reachable without a hard rule firing
            // first, but fail safe rather than leave it ungraded.
            $result['is_indeterminate'] = true;

            return self::CLASS_R;
        }

        return $result['candidates'][0];
    }

    private static function weightPoints(float $weight): ?int
    {
        if ($weight > self::MAX_WEIGHT) {
            return null; // hard rule (Class 2), no points on the sheet
        }
        if ($weight >= 14) {
            return 9;
        }
        if ($weight >= self::MIN_WEIGHT) {
            return 6;
        }

        return 3;
    }

    private static function intOrNull($value): ?int
    {
        return ($value === null || $value === '') ? null : (int) $value;
    }

    /** @return array{0: int|null, 1: string[]} */
    private static function scoreAttributes(array $attributes): array
    {
        $total = 0;
        $missing = [];

        foreach (self::POINTS as $field => $map) {
            $value = $attributes[$field] ?? null;
            if ($value === null || $value === '' || !array_key_exists((int) $value, $map)) {
                $missing[] = $field;
                continue;
            }
            $total += $map[(int) $value];
        }

        return [$missing ? null : $total, $missing];
    }
}
