<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ChangeAutoClassificationToStringOnQaGradingTable extends Migration
{
    /**
     * auto_classification (App\Models\CarcassGradingService's independent
     * scorecard suggestion) moves from a smallint ID to the grade name
     * itself (e.g. "High Grade"), so it reads directly without a LABELS
     * lookup. classification_code is untouched — it's a separate SKU-style
     * code (HG+170, CG-120, ...) already relied on by qaGradingReportExport.
     *
     * Raw SQL here (not Schema::table(...)->change()) because this project
     * doesn't have doctrine/dbal installed, which Laravel's column-change
     * helper requires.
     */
    public function up()
    {
        DB::statement('ALTER TABLE qa_grading ALTER COLUMN auto_classification VARCHAR(20) NULL');

        // Existing rows hold the old numeric IDs (now stringified by the
        // ALTER above, e.g. '2') — convert them to real names so historical
        // data stays meaningful instead of turning into digit strings.
        DB::statement("
            UPDATE qa_grading
            SET auto_classification = CASE auto_classification
                WHEN '1' THEN 'Premium'
                WHEN '2' THEN 'High Grade'
                WHEN '3' THEN 'Commercial'
                WHEN '4' THEN 'Poor C'
                WHEN '8' THEN 'FAQ'
                WHEN '9' THEN 'Standard'
                ELSE auto_classification
            END
            WHERE auto_classification IS NOT NULL
        ");
    }

    public function down()
    {
        // Only safe if no row holds a name string that isn't one of the six
        // known grades — reverses the data mapping first, then the type.
        DB::statement("
            UPDATE qa_grading
            SET auto_classification = CASE auto_classification
                WHEN 'Premium' THEN '1'
                WHEN 'High Grade' THEN '2'
                WHEN 'Commercial' THEN '3'
                WHEN 'Poor C' THEN '4'
                WHEN 'FAQ' THEN '8'
                WHEN 'Standard' THEN '9'
                ELSE auto_classification
            END
            WHERE auto_classification IS NOT NULL
        ");

        DB::statement('ALTER TABLE qa_grading ALTER COLUMN auto_classification SMALLINT NULL');
    }
}
