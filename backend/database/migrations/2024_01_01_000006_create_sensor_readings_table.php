<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->string('sensor_type'); // blood_pressure, heart_rate, temperature, etc.
            $table->string('value'); // Valeur lue (peut être JSON pour valeurs complexes)
            $table->string('unit')->nullable(); // mmHg, bpm, °C, etc.
            $table->json('metadata')->nullable(); // Données supplémentaires
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['patient_id', 'sensor_type', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensor_readings');
    }
};

