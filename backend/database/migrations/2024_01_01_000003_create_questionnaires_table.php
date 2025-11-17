<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questionnaires', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // PHQ-9, GAD-7, etc.
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('questions'); // Structure flexible pour les questions
            $table->json('scoring_rules')->nullable(); // Règles de scoring
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questionnaires');
    }
};

