<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    /**
     * Seuls les clinicien peuvent voir la liste des patients
     */
    public function viewAny(User $user): bool
    {
        return $user->isClinician();
    }

    /**
     * Un clinicien peut voir n'importe quel patient
     * Un patient peut voir uniquement son propre profil
     */
    public function view(User $user, Patient $patient): bool
    {
        if ($user->isClinician()) {
            return true;
        }

        return $user->isPatient() && $user->patient?->id === $patient->id;
    }

    /**
     * Seuls les clinicien peuvent créer des patients
     */
    public function create(User $user): bool
    {
        return $user->isClinician();
    }

    /**
     * Seuls les clinicien peuvent modifier des patients
     */
    public function update(User $user, Patient $patient): bool
    {
        return $user->isClinician();
    }

    public function delete(User $user, Patient $patient): bool
    {
        return $user->isClinician();
    }
}

