<?php

namespace App\Policies;

use App\Models\Cita;
use App\Models\User;

class CitaPolicy
{
    public function update(User $user, Cita $cita): bool
    {
        return (int) $user->id === (int) $cita->patient_id;
    }

    public function delete(User $user, Cita $cita): bool
    {
        return (int) $user->id === (int) $cita->patient_id;
    }
}
