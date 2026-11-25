<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * qa_grading.classification holds QA's own (manual) grade, next to
 * auto_classification (the scorecard's grade). Renamed so the two can't be
 * confused. sp_rename is used because renameColumn needs doctrine/dbal,
 * which this project doesn't install.
 *
 * Adds qa_classification_name, a computed column SQL Server fills in from
 * the id (1 -> Premium, ...), so the grade is readable straight from the
 * table. The app keeps reading and writing the integer id.
 *
 * Also records what each column holds as a SQL Server column description
 * (visible in SSMS under the column's properties).
 */
class RenameClassificationToQaClassificationOnQaGradingTable extends Migration
{
    /** qa_classification id => name; same names auto_classification uses. */
    private const NAMES = [
        1 => 'Premium', 2 => 'High Grade', 8 => 'FAQ', 9 => 'Standard', 3 => 'Commercial',
        4 => 'Poor C', 5 => '1st Grade', 6 => '2nd Grade', 7 => 'Class R', 10 => 'Condemned',
    ];

    private const DESCRIPTIONS = [
        'qa_classification'   => "QA's own grade, picked in the grading screen (id: 1 Premium, 2 High Grade, 8 FAQ, 9 Standard, 3 Commercial, 4 Poor C, 5 Lamb 1st, 6 Lamb 2nd, 7 Class R, 10 Condemned). Used by reports, codes and the downgrade check.",
        'qa_classification_name' => 'Name of the QA grade in qa_classification. Computed by SQL Server from the id; read-only.',
        'auto_classification' => "System grade from the scorecard (grade name, e.g. 'High Grade'). Advisory only; QA's grade is qa_classification.",
    ];

    public function up()
    {
        DB::statement("EXEC sp_rename 'qa_grading.classification', 'qa_classification', 'COLUMN'");

        $cases = collect(self::NAMES)->map(fn ($name, $id) => "WHEN {$id} THEN '{$name}'")->implode(' ');
        DB::statement("ALTER TABLE qa_grading ADD qa_classification_name AS (CASE qa_classification {$cases} END)");

        foreach (self::DESCRIPTIONS as $column => $text) {
            $this->setDescription($column, $text);
        }
    }

    public function down()
    {
        foreach (array_keys(self::DESCRIPTIONS) as $column) {
            $this->dropDescription($column);
        }

        DB::statement('ALTER TABLE qa_grading DROP COLUMN qa_classification_name');
        DB::statement("EXEC sp_rename 'qa_grading.qa_classification', 'classification', 'COLUMN'");
    }

    private function setDescription(string $column, string $text): void
    {
        $this->dropDescription($column);
        DB::statement(
            "EXEC sp_addextendedproperty @name = N'MS_Description', @value = ?,
                @level0type = N'SCHEMA', @level0name = N'dbo',
                @level1type = N'TABLE',  @level1name = N'qa_grading',
                @level2type = N'COLUMN', @level2name = ?",
            [$text, $column]
        );
    }

    private function dropDescription(string $column): void
    {
        $exists = DB::selectOne(
            "SELECT 1 AS found FROM sys.extended_properties ep
             JOIN sys.columns c ON c.object_id = ep.major_id AND c.column_id = ep.minor_id
             WHERE ep.name = 'MS_Description' AND ep.major_id = OBJECT_ID('dbo.qa_grading') AND c.name = ?",
            [$column]
        );

        if ($exists) {
            DB::statement(
                "EXEC sp_dropextendedproperty @name = N'MS_Description',
                    @level0type = N'SCHEMA', @level0name = N'dbo',
                    @level1type = N'TABLE',  @level1name = N'qa_grading',
                    @level2type = N'COLUMN', @level2name = ?",
                [$column]
            );
        }
    }
}
