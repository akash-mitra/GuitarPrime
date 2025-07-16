<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * Scope for filtering users by role
     */
    public function scopeWithRole($query, $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Get all purchases for this user
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Check if user has purchased a specific item
     */
    public function hasPurchased($purchasable): bool
    {
        return $this->purchases()
            ->where('purchasable_type', get_class($purchasable))
            ->where('purchasable_id', $purchasable->id)
            ->where('status', 'completed')
            ->exists();
    }

    /**
     * Check if user can access content (free or purchased)
     */
    public function canAccess($purchasable): bool
    {
        // Admin can access everything
        if ($this->hasRole('admin')) {
            return true;
        }

        // Free content is accessible to everyone
        if ($purchasable->isFree()) {
            return true;
        }

        // For coaches, check if they own the content
        if ($this->hasRole('coach')) {
            // For courses, check if the coach owns it
            if ($purchasable instanceof \App\Models\Course) {
                return $purchasable->coach_id === $this->id || $this->hasPurchased($purchasable);
            }

            // For modules, check if the coach owns the module directly or any course that contains this module
            if ($purchasable instanceof \App\Models\Module) {
                // Coach owns the module directly
                if ($purchasable->coach_id === $this->id) {
                    return true;
                }

                // Coach owns a course that contains this module
                $ownsModuleInCourse = $purchasable->courses()
                    ->where('coach_id', $this->id)
                    ->exists();

                return $ownsModuleInCourse || $this->hasAccessToModuleThroughCourse($purchasable);
            }
        }

        // For students and other cases
        if ($purchasable instanceof \App\Models\Course) {
            return $this->hasPurchased($purchasable);
        }

        // For modules, check if user has purchased any course containing this module OR the module directly
        if ($purchasable instanceof \App\Models\Module) {
            return $this->hasPurchased($purchasable) || $this->hasAccessToModuleThroughCourse($purchasable);
        }

        return false;
    }

    /**
     * Check if user has access to a module through any purchased course
     */
    public function hasAccessToModuleThroughCourse(\App\Models\Module $module): bool
    {
        // Get all courses that contain this module
        $courseIds = $module->courses()->pluck('courses.id');

        // Check if user has purchased any of these courses
        return $this->purchases()
            ->where('purchasable_type', \App\Models\Course::class)
            ->whereIn('purchasable_id', $courseIds)
            ->where('status', 'completed')
            ->exists();
    }
}
