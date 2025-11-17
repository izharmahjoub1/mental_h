<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->enum('level', ['GREEN', 'ORANGE', 'RED']);
            $table->string('type'); // questionnaire, sensor, combined
            $table->text('reason');
            $table->json('source_data')->nullable(); // Données source (questionnaire_id, sensor_reading_id, etc.)
            $table->boolean('is_acknowledged')->default(false);
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();

            $table->index(['patient_id', 'level', 'is_acknowledged']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};

