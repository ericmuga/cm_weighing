<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIpAddressToScaleConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('scale_configs', function (Blueprint $table) {
            $table->string('ip_address')->nullable()->after('section');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('scale_configs', function (Blueprint $table) {
            $table->dropColumnIfExists('ip_address');
        });
    }
}
