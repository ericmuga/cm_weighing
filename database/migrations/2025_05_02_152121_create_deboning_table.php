<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeboningTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deboning', function (Blueprint $table) {
            $table->id();            
            $table->string('product_code', 50)->unique();
            $table->double('scale_reading', 8, 2);
            $table->double('tareweight', 8, 2);
            $table->double('netweight', 8, 2);
            $table->integer('process_code')->nullable();
            $table->integer('no_of_pieces')->nullable();
            $table->string('product_type')->nullable();
            $table->text('narration')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->tinyInteger('is_manual')->default(0);
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
        Schema::dropIfExists('deboning');
    }
}
