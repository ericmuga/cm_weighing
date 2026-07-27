<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsDowngradedToQaGradingTable extends Migration
{
    public function up()
    {
        Schema::table('qa_grading', function (Blueprint $table) {
            $table->tinyInteger('is_downgraded')->nullable()->after('classification_code');
        });
    }

    public function down()
    {
        Schema::table('qa_grading', function (Blueprint $table) {
            $table->dropColumn('is_downgraded');
        });
    }
}
