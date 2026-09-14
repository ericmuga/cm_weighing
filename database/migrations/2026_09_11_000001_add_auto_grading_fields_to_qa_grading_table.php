<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAutoGradingFieldsToQaGradingTable extends Migration
{
    public function up()
    {
        Schema::table('qa_grading', function (Blueprint $table) {
            // Raw visual-attribute score (Dentition + Fat Cover + Bruising + Fat Colour + Meat Colour + Muscle conformation).
            $table->smallInteger('verdict1')->nullable()->after('muscle_conformation');
            // Verdict 1 + weight points; used only to break ties between overlapping grade bands.
            $table->smallInteger('verdict2')->nullable()->after('verdict1');
            // What the scorecard alone computed, before any manual override.
            $table->smallInteger('auto_classification')->nullable()->after('verdict2');
            // True when verdict1 fell in more than one grade's band and weight had to settle it (or couldn't).
            $table->boolean('is_indeterminate')->nullable()->default(false)->after('auto_classification');
            // 'auto' = QA saved the value the scorecard suggested; 'manual' = QA changed it before saving.
            $table->string('classification_source', 10)->nullable()->after('is_indeterminate');
        });
    }

    public function down()
    {
        Schema::table('qa_grading', function (Blueprint $table) {
            $table->dropColumn(['verdict1', 'verdict2', 'auto_classification', 'is_indeterminate', 'classification_source']);
        });
    }
}
