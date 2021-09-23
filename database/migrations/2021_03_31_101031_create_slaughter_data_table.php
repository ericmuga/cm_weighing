<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSlaughterDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('slaughter_data', function (Blueprint $table) {
            $table->id();
            $table->string('agg_no', 20);
            $table->string('receipt_no', 20);
            $table->string('item_code', 20);
            $table->string('vendor_no', 20);
            $table->string('vendor_name');
            $table->double('sideA_weight', 8, 2)->default(0.00);
            $table->double('sideB_weight', 8, 2)->default(0.00);
            $table->double('total_weight', 8, 2);
            $table->double('tare_weight', 8, 2);
            $table->double('total_net', 8, 2);
            $table->double('settlement_weight', 8, 2);
            $table->string('classification_code', 20)->nullable();
            $table->string('fat_group', 20)->nullable();
            $table->string('narration', 255)->nullable();
            $table->bigInteger('grading_user')->nullable();
            $table->timestamp('graded_at')->nullable();
            $table->tinyInteger('deleted')->default(0);
            $table->foreignId('user_id')->constrained('users');
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
        Schema::dropIfExists('slaughter_data');
    }
}
