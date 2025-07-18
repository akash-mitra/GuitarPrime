<?php

namespace App\Policies;

use App\Models\Theme;
use App\Models\User;

class ThemePolicy
{
    public function viewAny(?User $user): bool
    {
        // Allow guests to view themes for SEO and discovery
        return true;
    }

    public function view(?User $user, Theme $theme): bool
    {
        // Allow guests to view individual themes for SEO and discovery
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, Theme $theme): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user, Theme $theme): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Theme $theme): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Theme $theme): bool
    {
        return false;
    }
}
