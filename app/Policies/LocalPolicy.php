<?php

namespace App\Policies;

use App\Models\Local;
use Illuminate\Auth\Access\Response;
use App\Models\User;

class LocalPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('Ver Local');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Local $local): bool
    {
        return $user->hasPermissionTo('Ver Local');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('Criar Local');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Local $local): bool
    {
        return $user->hasPermissionTo('Editar Local');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Local $local): bool
    {
        return $user->hasPermissionTo('Excluir Local');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Local $local)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Local $local)
    {
        return false;
    }
}
