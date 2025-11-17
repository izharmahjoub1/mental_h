<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionnaireResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'questionnaire_id',
        'patient_id',
        'assignment_id',
        'answers',
        'total_score',
        'interpretation',
    ];

    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'total_score' => 'integer',
        ];
    }

    // Relations
    public function questionnaire(): BelongsTo
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(QuestionnaireAssignment::class, 'assignment_id');
    }
}

