<?php

namespace Tests\Unit;

use App\Models\CarcassGradingService as G;
use Tests\TestCase;

/**
 * Verifies app/Models/CarcassGradingService.php against the worked examples in
 * "Grading template formulation (2).xlsx" (BEST CASE / WORST CASE tabs).
 */
class CarcassGradingServiceTest extends TestCase
{
    public function testPremiumBestCaseScoresEighteenAndGradesPremiumWhenHeavy()
    {
        $r = G::compute(['dentition' => 3, 'fat_cover' => 4, 'fat_color' => 1, 'meat_color' => 1, 'bruising' => 0, 'muscle' => 1], 250);

        $this->assertSame(18, $r['verdict1']);
        $this->assertSame(G::PREMIUM, $r['classification']);
        $this->assertFalse($r['is_indeterminate']);
    }

    public function testScoreSeventeenTiesPremiumAndHighgradeAndWeightBreaksTheTie()
    {
        $attrs = ['dentition' => 3, 'fat_cover' => 1, 'fat_color' => 1, 'meat_color' => 1, 'bruising' => 0, 'muscle' => 1];

        $heavy = G::compute($attrs, 250); // tier 5 -> fully supports Premium
        $this->assertSame(17, $heavy['verdict1']);
        $this->assertSame([G::PREMIUM, G::HIGHGRADE], $heavy['candidates']);
        $this->assertSame(G::PREMIUM, $heavy['classification']);
        $this->assertFalse($heavy['is_indeterminate']);

        $mid = G::compute($attrs, 190); // tier 4 -> weight caps it down to High Grade
        $this->assertSame(G::HIGHGRADE, $mid['classification']);
        $this->assertFalse($mid['is_indeterminate']);

        $light = G::compute($attrs, 130); // tier 2 -> below both tied grades' requirements
        $this->assertSame(G::HIGHGRADE, $light['classification']);
        $this->assertTrue($light['is_indeterminate']);
    }

    public function testPremiumWeightFloorIs200kgNotTheOld220kg()
    {
        // Verdict1=17 ties Premium and High Grade (see the test above), so
        // weight alone decides which one wins — the sharpest place to pin
        // down exactly where the Premium floor sits.
        $attrs = ['dentition' => 3, 'fat_cover' => 1, 'fat_color' => 1, 'meat_color' => 1, 'bruising' => 0, 'muscle' => 1];

        $this->assertSame(G::HIGHGRADE, G::compute($attrs, 199)['classification']); // just under the floor -> tier 4
        $this->assertSame(G::PREMIUM, G::compute($attrs, 200)['classification']); // at the floor -> tier 5
        $this->assertSame(G::PREMIUM, G::compute($attrs, 205)['classification']); // would've been High Grade under the old 220kg floor
    }

    public function testFaqAndStandardShareAnIdenticalWorstCaseCombinationAndTieOnScoreTen()
    {
        // FAQ and Standard's worst-case profiles are the exact same attribute
        // combination in the sheet, both scoring 10 — Commercial's band (8-14)
        // also covers 10, so all three are candidates.
        $attrs = ['dentition' => 1, 'fat_cover' => 2, 'fat_color' => 3, 'meat_color' => 2, 'bruising' => 1, 'muscle' => 2];

        $r = G::compute($attrs, 100);
        $this->assertSame(10, $r['verdict1']);
        $this->assertSame([G::FAQ, G::STANDARD, G::COMMERCIAL], $r['candidates']);

        $this->assertSame(G::FAQ, G::compute($attrs, 160)['classification']); // tier 3
        $this->assertSame(G::STANDARD, G::compute($attrs, 140)['classification']); // tier 2

        $tooLight = G::compute($attrs, 90); // tier 1 -> only Commercial's floor is met
        $this->assertSame(G::COMMERCIAL, $tooLight['classification']);
        $this->assertFalse($tooLight['is_indeterminate']);
    }

    public function testCommercialFloorResolvesCleanlyAtTheSharedBoundaryWithPoorC()
    {
        // Commercial's own weight requirement ("below 120kg" = tier 1) is met by
        // any real weight, so verdict1=8 always resolves to Commercial rather
        // than tying with Poor C.
        $attrs = ['dentition' => 1, 'fat_cover' => 3, 'fat_color' => 2, 'meat_color' => 2, 'bruising' => 3, 'muscle' => 1];

        $r = G::compute($attrs, 100);
        $this->assertSame(8, $r['verdict1']);
        $this->assertSame([G::COMMERCIAL], $r['candidates']);
        $this->assertSame(G::COMMERCIAL, $r['classification']);
        $this->assertFalse($r['is_indeterminate']);
    }

    public function testBelowCommercialsFloorIsAlwaysPoorCRegardlessOfWeight()
    {
        $attrs = ['dentition' => 2, 'fat_cover' => 3, 'fat_color' => 2, 'meat_color' => 2, 'bruising' => 4, 'muscle' => 3];

        $r = G::compute($attrs, 300); // even a heavy carcass can't outweigh a genuinely poor visual score
        $this->assertSame(6, $r['verdict1']);
        $this->assertSame([G::POOR_C], $r['candidates']);
        $this->assertSame(G::POOR_C, $r['classification']);
        $this->assertFalse($r['is_indeterminate']);
    }

    public function testMissingAttributeYieldsNoVerdictOrClassification()
    {
        $r = G::compute(['dentition' => 1, 'fat_cover' => 1, 'fat_color' => 1, 'meat_color' => 1, 'bruising' => 0], 150);

        $this->assertNull($r['verdict1']);
        $this->assertNull($r['classification']);
    }

    public function testUnambiguousVerdictGradesWithoutNeedingWeightAtAll()
    {
        // Score 18 only ever falls in Premium's band (17-18) — nothing to tie
        // against, so weight isn't needed to resolve it.
        $attrs = ['dentition' => 3, 'fat_cover' => 4, 'fat_color' => 1, 'meat_color' => 1, 'bruising' => 0, 'muscle' => 1];

        $r = G::compute($attrs, null);
        $this->assertSame(18, $r['verdict1']);
        $this->assertSame([G::PREMIUM], $r['candidates']);
        $this->assertSame(G::PREMIUM, $r['classification']);
        $this->assertNull($r['verdict2']); // weight unknown, so it's never computed
        $this->assertFalse($r['is_indeterminate']);
    }

    public function testMissingWeightLeavesATiedVerdictUnresolved()
    {
        // Score 17 ties Premium and High Grade (see
        // testScoreSeventeenTiesPremiumAndHighgradeAndWeightBreaksTheTie) — this
        // is the one case weight is actually required for.
        $r = G::compute(['dentition' => 3, 'fat_cover' => 1, 'fat_color' => 1, 'meat_color' => 1, 'bruising' => 0, 'muscle' => 1], null);

        $this->assertSame(17, $r['verdict1']);
        $this->assertSame([G::PREMIUM, G::HIGHGRADE], $r['candidates']);
        $this->assertNull($r['classification']);
        $this->assertNull($r['verdict2']);
        $this->assertFalse($r['is_indeterminate']); // unresolved, not the same as a failed tie-break
    }

    public function testAppliesToOnlyMatchesBeef()
    {
        $this->assertTrue(G::appliesTo('BG1021'));
        $this->assertFalse(G::appliesTo('BG1900')); // lamb
        $this->assertFalse(G::appliesTo('BG1202')); // goat
        $this->assertFalse(G::appliesTo(null));
    }
}
