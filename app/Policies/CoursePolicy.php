<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'coach', 'student']);
    }

    public function view(User $user, Course $course): bool
    {
        return $user->hasAnyRole(['admin', 'coach', 'student']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'coach']);
    }

    public function update(User $user, Course $course): bool
    {
        return $user->hasRole('admin') || ($user->hasRole('coach') && $course->coach_id === $user->id);
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->hasRole('admin');
    }

    public function approve(User $user, Course $course): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Course $course): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Course $course): bool
    {
        return false;
    }
}
