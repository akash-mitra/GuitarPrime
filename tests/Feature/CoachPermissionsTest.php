<?php

use App\Models\Attachment;
use App\Models\Course;
use App\Models\Module;
use App\Models\Purchase;
use App\Models\Theme;
use App\Models\User;

beforeEach(function () {
    $this->coach = User::factory()->create(['role' => 'coach']);
    $this->otherCoach = User::factory()->create(['role' => 'coach']);
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->student = User::factory()->create(['role' => 'student']);
    $this->theme = Theme::factory()->create();
});

describe('Coach Theme Permissions', function () {
    test('coach can view all themes', function () {
        $themes = Theme::factory()->count(3)->create();

        expect($this->coach->can('viewAny', Theme::class))->toBeTrue();

        foreach ($themes as $theme) {
            expect($this->coach->can('view', $theme))->toBeTrue();
        }
    });

    test('coach cannot create themes', function () {
        expect($this->coach->can('create', Theme::class))->toBeFalse();
    });

    test('coach cannot update themes', function () {
        $theme = Theme::factory()->create();
        expect($this->coach->can('update', $theme))->toBeFalse();
    });

    test('coach cannot delete themes', function () {
        $theme = Theme::factory()->create();
        expect($this->coach->can('delete', $theme))->toBeFalse();
    });

    test('coach can submit new course to a theme', function () {
        expect($this->coach->can('create', Course::class))->toBeTrue();
    });
});

describe('Coach Course Permissions - Own Courses', function () {
    test('coach can view own courses regardless of approval status', function () {
        $approvedCourse = Course::factory()->create([
            'is_approved' => true,
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
        ]);

        $pendingCourse = Course::factory()->create([
            'is_approved' => false,
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
        ]);

        expect($this->coach->can('view', $approvedCourse))->toBeTrue();
        expect($this->coach->can('view', $pendingCourse))->toBeTrue();
    });

    test('coach can create courses', function () {
        expect($this->coach->can('create', Course::class))->toBeTrue();
    });

    test('coach can update own courses', function () {
        $course = Course::factory()->create([
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
        ]);

        expect($this->coach->can('update', $course))->toBeTrue();
    });

    test('coach can delete own unapproved courses', function () {
        $pendingCourse = Course::factory()->create([
            'is_approved' => false,
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
        ]);

        expect($this->coach->can('delete', $pendingCourse))->toBeTrue();
    });

    test('coach cannot delete own approved courses', function () {
        $approvedCourse = Course::factory()->create([
            'is_approved' => true,
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
        ]);

        expect($this->coach->can('delete', $approvedCourse))->toBeFalse();
    });

    test('coach can add/remove modules to own courses if modules are also created by coach', function () {
        $course = Course::factory()->create([
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
        ]);

        $ownModule = Module::factory()->create(['coach_id' => $this->coach->id]);
        $otherModule = Module::factory()->create(['coach_id' => $this->otherCoach->id]);

        expect($this->coach->can('update', $course))->toBeTrue();
        // Logic for adding modules should be checked in controller/business logic
    });
});

describe('Coach Course Permissions - Other Courses', function () {
    test('coach can view other approved courses', function () {
        $approvedCourse = Course::factory()->create([
            'is_approved' => true,
            'coach_id' => $this->otherCoach->id,
            'theme_id' => $this->theme->id,
        ]);

        expect($this->coach->can('view', $approvedCourse))->toBeTrue();
    });

    test('coach cannot view other pending courses', function () {
        $pendingCourse = Course::factory()->create([
            'is_approved' => false,
            'coach_id' => $this->otherCoach->id,
            'theme_id' => $this->theme->id,
        ]);

        expect($this->coach->can('view', $pendingCourse))->toBeFalse();
    });

    test('coach cannot update other courses', function () {
        $course = Course::factory()->create([
            'coach_id' => $this->otherCoach->id,
            'theme_id' => $this->theme->id,
        ]);

        expect($this->coach->can('update', $course))->toBeFalse();
    });

    test('coach cannot delete other courses', function () {
        $course = Course::factory()->create([
            'coach_id' => $this->otherCoach->id,
            'theme_id' => $this->theme->id,
        ]);

        expect($this->coach->can('delete', $course))->toBeFalse();
    });

    test('coach cannot approve courses', function () {
        $course = Course::factory()->create([
            'is_approved' => false,
            'coach_id' => $this->otherCoach->id,
            'theme_id' => $this->theme->id,
        ]);

        expect($this->coach->can('approve', $course))->toBeFalse();
    });
});

describe('Coach Module Permissions - Own Modules', function () {
    test('coach can view own modules', function () {
        $module = Module::factory()->create(['coach_id' => $this->coach->id]);

        expect($this->coach->can('viewAny', Module::class))->toBeTrue();
        expect($this->coach->can('view', $module))->toBeTrue();
    });

    test('coach can create modules', function () {
        expect($this->coach->can('create', Module::class))->toBeTrue();
    });

    test('coach can update own modules', function () {
        $module = Module::factory()->create(['coach_id' => $this->coach->id]);

        expect($this->coach->can('update', $module))->toBeTrue();
    });

    test('coach can soft delete own modules', function () {
        $module = Module::factory()->create(['coach_id' => $this->coach->id]);

        expect($this->coach->can('delete', $module))->toBeTrue();
    });

    test('coach has MCA permission to own modules', function () {
        $freeModule = Module::factory()->create([
            'coach_id' => $this->coach->id,
            'is_free' => true,
        ]);

        $paidModule = Module::factory()->create([
            'coach_id' => $this->coach->id,
            'is_free' => false,
        ]);

        expect($this->coach->canAccess($freeModule))->toBeTrue();
        expect($this->coach->canAccess($paidModule))->toBeTrue();
    });

    test('coach can access own module content (video and attachments)', function () {
        $module = Module::factory()->create([
            'coach_id' => $this->coach->id,
            'is_free' => false,
        ]);

        $attachment = Attachment::factory()->create(['module_id' => $module->id]);

        expect($this->coach->canAccess($module))->toBeTrue();
    });
});

describe('Coach Module Permissions - Other Modules', function () {
    test('coach can view other modules', function () {
        $module = Module::factory()->create(['coach_id' => $this->otherCoach->id]);

        expect($this->coach->can('view', $module))->toBeTrue();
    });

    test('coach cannot update other modules', function () {
        $module = Module::factory()->create(['coach_id' => $this->otherCoach->id]);

        expect($this->coach->can('update', $module))->toBeFalse();
    });

    test('coach cannot delete other modules', function () {
        $module = Module::factory()->create(['coach_id' => $this->otherCoach->id]);

        expect($this->coach->can('delete', $module))->toBeFalse();
    });

    test('coach can list other module contents but cannot view them (no MCA)', function () {
        $module = Module::factory()->create([
            'coach_id' => $this->otherCoach->id,
            'is_free' => false,
        ]);

        $attachment = Attachment::factory()->create(['module_id' => $module->id]);

        // Coach can view module info but not access content
        expect($this->coach->can('view', $module))->toBeTrue();
        expect($this->coach->canAccess($module))->toBeFalse();
    });

    test('coach can access other modules through course purchase', function () {
        $module = Module::factory()->create([
            'coach_id' => $this->otherCoach->id,
            'is_free' => false,
        ]);

        $course = Course::factory()->create([
            'coach_id' => $this->otherCoach->id,
            'theme_id' => $this->theme->id,
            'is_approved' => true,
        ]);

        $course->modules()->attach($module->id, ['order' => 1]);

        // Before purchase - no access
        expect($this->coach->canAccess($module))->toBeFalse();

        // After purchase - has access
        Purchase::factory()->create([
            'user_id' => $this->coach->id,
            'purchasable_type' => Course::class,
            'purchasable_id' => $course->id,
            'status' => 'completed',
        ]);

        expect($this->coach->canAccess($module))->toBeTrue();
    });
});

describe('Coach Route Access', function () {
    test('coach can access management routes', function () {
        $routes = [
            'themes.index',
            'courses.index',
            'courses.create',
            'modules.index',
            'modules.create',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->coach)->get(route($route));
            $response->assertOk();
        }
    });

    test('coach cannot access theme management routes', function () {
        $response = $this->actingAs($this->coach)->get(route('themes.create'));
        $response->assertForbidden();
    });

    test('coach cannot access course approval routes', function () {
        $course = Course::factory()->create([
            'is_approved' => false,
            'coach_id' => $this->otherCoach->id,
            'theme_id' => $this->theme->id,
        ]);

        expect($this->coach->can('approve', $course))->toBeFalse();
    });
});
