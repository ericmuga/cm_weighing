<?php

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Grading parameters editable from QA > Settings, stored in the
 * grading_settings table. Any key without a row uses DEFAULTS, so grading
 * keeps working on an empty (or missing) table.
 *
 * Used by LambGradingService (PHP), the grading-v2 live preview (JS, via
 * all()), the weigh-in screen's lamb weigh codes and QAController's lamb
 * weight rank — all read the same values, so they can't drift apart.
 */
class GradingSetting
{
    private const CACHE_KEY = 'grading_settings_v1';

    /**
     * key => [default, label, unit, group]. Order here is the order on the
     * settings page.
     */
    public const DEFINITIONS = [
        // Lamb weight bands (kg)
        'lamb_min_weight'        => [11, 'Minimum weight — below this is Class R', 'kg', 'Lamb weight bands'],
        'lamb_class1_min_weight' => [15, 'Class 1 starts at', 'kg', 'Lamb weight bands'],
        'lamb_max_weight'        => [35, 'Class 1 ends at — above this is Class 2', 'kg', 'Lamb weight bands'],

        // Lamb weight points added to Verdict 1
        'lamb_points_class1'     => [9, 'Points for Class 1 weight', 'pts', 'Lamb weight points'],
        'lamb_points_class2'     => [6, 'Points for Class 2 weight (minimum up to Class 1 start)', 'pts', 'Lamb weight points'],
        'lamb_points_below_min'  => [3, 'Points below minimum weight', 'pts', 'Lamb weight points'],

        // Lamb Verdict 2 score bands
        'lamb_band_c1_min'       => [21, 'Class 1 — lowest score', 'pts', 'Lamb score bands (Verdict 2)'],
        'lamb_band_c1_max'       => [23, 'Class 1 — highest score', 'pts', 'Lamb score bands (Verdict 2)'],
        'lamb_band_c2_min'       => [15, 'Class 2 — lowest score', 'pts', 'Lamb score bands (Verdict 2)'],
        'lamb_band_c2_max'       => [20, 'Class 2 — highest score', 'pts', 'Lamb score bands (Verdict 2)'],
        'lamb_band_r_min'        => [8,  'Class R — lowest score', 'pts', 'Lamb score bands (Verdict 2)'],
        'lamb_band_r_max'        => [17, 'Class R — highest score', 'pts', 'Lamb score bands (Verdict 2)'],
    ];

    /** @return array<string, float> every key, stored value or default */
    public static function all(): array
    {
        $stored = Cache::rememberForever(self::CACHE_KEY, function () {
            try {
                return DB::table('grading_settings')->pluck('value', 'key')->all();
            } catch (\Throwable $e) {
                // Table not migrated yet — run on defaults rather than fail grading.
                Log::warning('grading_settings unavailable, using defaults: ' . $e->getMessage());

                return [];
            }
        });

        $values = [];
        foreach (self::DEFINITIONS as $key => [$default]) {
            $values[$key] = array_key_exists($key, $stored) ? (float) $stored[$key] : (float) $default;
        }

        return $values;
    }

    public static function get(string $key): float
    {
        return self::all()[$key];
    }

    /**
     * Save changed values and record each change. Returns the keys that changed.
     *
     * @param array<string, float> $values
     */
    public static function saveMany(array $values, ?int $userId, ?string $username): array
    {
        $current = self::all();
        $changed = [];

        DB::transaction(function () use ($values, $current, $userId, $username, &$changed) {
            foreach ($values as $key => $value) {
                if (!array_key_exists($key, self::DEFINITIONS)) {
                    continue;
                }
                $value = round((float) $value, 2);
                if (abs($value - $current[$key]) < 0.001) {
                    continue;
                }

                DB::table('grading_settings')->updateOrInsert(
                    ['key' => $key],
                    ['value' => $value, 'updated_by' => $userId, 'updated_at' => now(), 'created_at' => now()]
                );
                DB::table('grading_setting_changes')->insert([
                    'key' => $key,
                    'old_value' => $current[$key],
                    'new_value' => $value,
                    'changed_by' => $userId,
                    'changed_by_username' => $username,
                    'created_at' => now(),
                ]);
                $changed[] = $key;
            }
        });

        Cache::forget(self::CACHE_KEY);

        return $changed;
    }

    /** Whether this username is listed in GRADING_SETTINGS_USERS. */
    public static function canEdit(?string $username): bool
    {
        return $username !== null && $username !== ''
            && in_array(strtolower($username), config('grading.settings_users', []), true);
    }

    /**
     * Cross-field rules. Returns error messages keyed by setting key.
     *
     * @param array<string, float> $v
     */
    public static function validateValues(array $v): array
    {
        $errors = [];

        // Points and scores are stored in whole-number verdict columns.
        foreach (self::DEFINITIONS as $key => [, , $unit]) {
            if ($unit === 'pts' && floor($v[$key]) != $v[$key]) {
                $errors[$key] = 'Must be a whole number.';
            }
        }

        if ($v['lamb_min_weight'] >= $v['lamb_class1_min_weight']) {
            $errors['lamb_class1_min_weight'] = 'Class 1 must start above the minimum weight.';
        }
        if ($v['lamb_class1_min_weight'] > $v['lamb_max_weight']) {
            $errors['lamb_max_weight'] = 'Class 1 must end at or above where it starts.';
        }
        foreach (['c1' => 'Class 1', 'c2' => 'Class 2', 'r' => 'Class R'] as $band => $name) {
            if ($v["lamb_band_{$band}_min"] > $v["lamb_band_{$band}_max"]) {
                $errors["lamb_band_{$band}_max"] = "$name highest score must be at least its lowest score.";
            }
        }

        return $errors;
    }
}
