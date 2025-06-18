<?php

namespace App\Providers;

use App\Models\Course;
use App\Models\Module;
use App\Models\Theme;
use App\Policies\CoursePolicy;
use App\Policies\ModulePolicy;
use App\Policies\ThemePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Theme::class, ThemePolicy::class);
        Gate::policy(Course::class, CoursePolicy::class);
        Gate::policy(Module::class, ModulePolicy::class);
    }
}
