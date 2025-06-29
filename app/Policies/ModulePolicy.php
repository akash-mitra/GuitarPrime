<?php

namespace App\Policies;

use App\Models\Module;
use App\Models\User;

class ModulePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'coach', 'student']);
    }

    public function view(User $user, Module $module): bool
    {
        return $user->hasAnyRole(['admin', 'coach', 'student']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'coach']);
    }

    public function update(User $user, Module $module): bool
    {
        return $user->hasAnyRole(['admin', 'coach']);
    }

    public function delete(User $user, Module $module): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Module $module): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Module $module): bool
    {
        return false;
    }
}
