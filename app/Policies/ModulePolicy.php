<?php

namespace App\Policies;

use App\Models\Module;
use App\Models\User;

class ModulePolicy
{
    public function viewAny(?User $user): bool
    {
        // Allow guests to view modules for SEO and discovery
        return true;
    }

    public function view(?User $user, Module $module): bool
    {
        // Guests can only view free modules
        if ($user === null) {
            return $module->is_free === true;
        }

        // Authenticated users can view all modules they have access to
        return $user->hasAnyRole(['admin', 'coach', 'student']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'coach']);
    }

    public function update(User $user, Module $module): bool
    {
        return $user->hasRole('admin') || ($user->hasRole('coach') && $module->coach_id === $user->id);
    }

    public function delete(User $user, Module $module): bool
    {
        return $user->hasRole('admin') || ($user->hasRole('coach') && $module->coach_id === $user->id);
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
