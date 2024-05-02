<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQaGradingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('qa_grading', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no', 20);
            $table->integer('agg_no');
            $table->smallInteger('classification_code')->nullable();
            $table->string('weight_group', 20)->nullable();
            $table->longText('narration')->nullable();
            $table->string('dentition', 50)->nullable();
            $table->string('fat_cover', 50)->nullable();
            $table->string('fat_color', 50)->nullable();
            $table->string('meat_color', 50)->nullable();
            $table->string('bruising', 50)->nullable();
            $table->string('muscle_conformation', 50)->nullable();
            $table->smallInteger('graded_by')->nullable();
            $table->datetime('slaughter_date');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('qa_grading');
    }
}
