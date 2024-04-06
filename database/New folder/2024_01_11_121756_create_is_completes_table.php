<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('is_completes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('set_id')->nullable();
            $table->bigInteger('day_exercise_id')->nullable();
            $table->bigInteger('day_id')->nullable();
            $table->bigInteger('program_id')->nullable();
            $table->bigInteger('user_id');
            $table->string('is_completed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('is_completes');
    }
};
