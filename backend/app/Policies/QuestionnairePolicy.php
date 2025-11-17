<?php

namespace App\Policies;

use App\Models\Questionnaire;
use App\Models\User;

class QuestionnairePolicy
{
    /**
     * Tous les utilisateurs authentifiés peuvent voir les questionnaires
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Questionnaire $questionnaire): bool
    {
        return true;
    }

    /**
     * Seuls les clinicien peuvent assigner des questionnaires
     */
    public function assign(User $user, Questionnaire $questionnaire): bool
    {
        return $user->isClinician();
    }
}

