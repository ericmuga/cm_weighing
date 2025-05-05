<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateButcherySidesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('butchery_sides', function (Blueprint $table) {
            $table->id();
            $table->string('product_code', 50)->unique();
            $table->integer('carcass_count')->nullable();
            $table->double('scale_reading', 8, 2);
            $table->double('tareweight', 8, 2);
            $table->double('netweight', 8, 2);
            $table->tinyInteger('is_manual')->default(0);
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
        Schema::dropIfExists('butchery_sides');
    }
}
