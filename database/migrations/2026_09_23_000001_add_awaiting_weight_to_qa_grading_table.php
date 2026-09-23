<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAwaitingWeightToQaGradingTable extends Migration
{
    public function up()
    {
        Schema::table('qa_grading', function (Blueprint $table) {
            // True when the scorecard produced a provisional suggestion only
            // (settlement weight wasn't known yet) — distinct from
            // is_indeterminate, which flags a grade that stayed ambiguous
            // even after weight was applied.
            $table->boolean('awaiting_weight')->nullable()->default(false)->after('is_indeterminate');
        });
    }

    public function down()
    {
        Schema::table('qa_grading', function (Blueprint $table) {
            $table->dropColumn('awaiting_weight');
        });
    }
}
