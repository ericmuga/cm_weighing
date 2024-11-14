<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->string('item_code', 20);
            $table->string('batch_no', 20)->nullable();
            $table->double('scale_reading', 8, 2);
            $table->double('net_weight', 8, 2);
            $table->integer('no_of_pieces')->nullable();
            $table->string('from_location_code', 10);
            $table->string('to_location_code', 10);
            $table->string('transfer_type', 20);           
            $table->string('narration', 255)->nullable();
            $table->boolean('manual_weight')->default(0);
            $table->foreignId('user_id')->constrained('users');
            $table->double('received_weight', 8, 2);
            $table->integer('received_pieces')->nullable();
            $table->foreignId('received_by')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transfers');
    }
}
