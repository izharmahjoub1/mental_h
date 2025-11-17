<?php

namespace App\Policies;

use App\Models\Alert;
use App\Models\User;

class AlertPolicy
{
    /**
     * Un clinicien peut voir toutes les alertes
     * Un patient peut voir uniquement ses alertes
     */
    public function view(User $user, Alert $alert): bool
    {
        if ($user->isClinician()) {
            return true;
        }

        return $user->isPatient() && $user->patient?->id === $alert->patient_id;
    }

    /**
     * Seuls les clinicien peuvent acquitter une alerte
     */
    public function acknowledge(User $user, Alert $alert): bool
    {
        return $user->isClinician();
    }
}

