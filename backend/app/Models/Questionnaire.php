<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Questionnaire extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'title',
        'description',
        'questions',
        'scoring_rules',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'questions' => 'array',
            'scoring_rules' => 'array',
            'is_active' => 'boolean',
        ];
    }

    // Relations
    public function assignments(): HasMany
    {
        return $this->hasMany(QuestionnaireAssignment::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(QuestionnaireResponse::class);
    }
}

