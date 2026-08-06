<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weighbridge_stations', function (Blueprint $table) {
            $table->id();
            $table->string('station_name');
            $table->string('indicator_model')->nullable();
            $table->string('communication_type', 30)->default('RS232');
            $table->string('com_port', 20)->nullable();
            $table->unsignedInteger('baud_rate')->default(9600);
            $table->unsignedTinyInteger('data_bits')->default(8);
            $table->string('parity', 20)->default('none');
            $table->unsignedTinyInteger('stop_bits')->default(1);
            $table->string('flow_control', 20)->default('none');
            $table->string('status', 20)->default('active')->index();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weighbridge_stations');
    }
};
