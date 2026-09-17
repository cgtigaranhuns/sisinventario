<?php

namespace App\Policies;

use App\Models\Bem;
use Illuminate\Auth\Access\Response;
use App\Models\User;

class BemPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('Ver Bem');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Bem $bem): bool
    {
        return $user->hasPermissionTo('Ver Bem');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('Criar Bem');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Bem $bem): bool
    {
        return $user->hasPermissionTo('Editar Bem');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Bem $bem): bool
    {
        return $user->hasPermissionTo('Deletar Bem');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Bem $bem)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Bem $bem)
    {
        return false;
    }
}
