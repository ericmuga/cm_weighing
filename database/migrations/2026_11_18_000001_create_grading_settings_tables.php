<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGradingSettingsTables extends Migration
{
    public function up()
    {
        // Editable grading parameters (weight bands, points, score bands).
        // A missing row falls back to GradingSetting::DEFAULTS, so the table
        // can start empty.
        Schema::create('grading_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->decimal('value', 10, 2);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        // Who changed what, and when — settings drive live grading.
        Schema::create('grading_setting_changes', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100);
            $table->decimal('old_value', 10, 2)->nullable();
            $table->decimal('new_value', 10, 2);
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->string('changed_by_username', 100)->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('grading_setting_changes');
        Schema::dropIfExists('grading_settings');
    }
}
