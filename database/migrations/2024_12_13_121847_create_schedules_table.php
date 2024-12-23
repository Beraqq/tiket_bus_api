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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('schedule_id')->unique();
            $table->string('bus_code');
            $table->string('route_id');
            $table->foreign('bus_code')->references('bus_code')->on('buses')->onDelete('cascade');
            $table->foreign('route_id')->references('route_id')->on('routes')->onDelete('cascade');
            $table->date('departure_date');
            $table->time('departure_time');
            $table->integer('available_seats');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
