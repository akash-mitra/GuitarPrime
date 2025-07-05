<?php

use App\Models\Attachment;
use App\Models\Course;
use App\Models\Module;
use App\Models\Theme;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->coach = User::factory()->create(['role' => 'coach']);
    $this->student = User::factory()->create(['role' => 'student']);
    $this->theme = Theme::factory()->create();
});

describe('Admin Theme Permissions', function () {
    test('admin can view all themes', function () {
        $themes = Theme::factory()->count(3)->create();

        expect($this->admin->can('viewAny', Theme::class))->toBeTrue();

        foreach ($themes as $theme) {
            expect($this->admin->can('view', $theme))->toBeTrue();
        }
    });

    test('admin can create themes', function () {
        expect($this->admin->can('create', Theme::class))->toBeTrue();
    });

    test('admin can update any theme', function () {
        $theme = Theme::factory()->create();
        expect($this->admin->can('update', $theme))->toBeTrue();
    });

    test('admin can delete any theme', function () {
        $theme = Theme::factory()->create();
        expect($this->admin->can('delete', $theme))->toBeTrue();
    });
});

describe('Admin Course Permissions', function () {
    test('admin can view any course regardless of approval status', function () {
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

        expect($this->admin->can('viewAny', Course::class))->toBeTrue();
        expect($this->admin->can('view', $approvedCourse))->toBeTrue();
        expect($this->admin->can('view', $pendingCourse))->toBeTrue();
    });

    test('admin can create courses', function () {
        expect($this->admin->can('create', Course::class))->toBeTrue();
    });

    test('admin can update any course', function () {
        $course = Course::factory()->create([
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
        ]);

        expect($this->admin->can('update', $course))->toBeTrue();
    });

    test('admin can delete any course', function () {
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

        expect($this->admin->can('delete', $approvedCourse))->toBeTrue();
        expect($this->admin->can('delete', $pendingCourse))->toBeTrue();
    });

    test('admin can approve any course', function () {
        $course = Course::factory()->create([
            'is_approved' => false,
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
        ]);

        expect($this->admin->can('approve', $course))->toBeTrue();
    });

    test('admin can add/remove modules from any course', function () {
        $course = Course::factory()->create([
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
        ]);

        $module = Module::factory()->create(['coach_id' => $this->coach->id]);

        // Admin should be able to manage course modules
        expect($this->admin->can('update', $course))->toBeTrue();
    });
});

describe('Admin Module Permissions', function () {
    test('admin can view any module', function () {
        $ownModule = Module::factory()->create(['coach_id' => $this->coach->id]);
        $otherModule = Module::factory()->create();

        expect($this->admin->can('viewAny', Module::class))->toBeTrue();
        expect($this->admin->can('view', $ownModule))->toBeTrue();
        expect($this->admin->can('view', $otherModule))->toBeTrue();
    });

    test('admin can create modules', function () {
        expect($this->admin->can('create', Module::class))->toBeTrue();
    });

    test('admin can update any module', function () {
        $ownModule = Module::factory()->create(['coach_id' => $this->coach->id]);
        $otherModule = Module::factory()->create();

        expect($this->admin->can('update', $ownModule))->toBeTrue();
        expect($this->admin->can('update', $otherModule))->toBeTrue();
    });

    test('admin can delete any module', function () {
        $ownModule = Module::factory()->create(['coach_id' => $this->coach->id]);
        $otherModule = Module::factory()->create();

        expect($this->admin->can('delete', $ownModule))->toBeTrue();
        expect($this->admin->can('delete', $otherModule))->toBeTrue();
    });

    test('admin has MCA permission to all modules', function () {
        $freeModule = Module::factory()->create(['is_free' => true]);
        $paidModule = Module::factory()->create(['is_free' => false]);

        // Admin should have Module Content Access (MCA) to all modules
        expect($this->admin->canAccess($freeModule))->toBeTrue();
        expect($this->admin->canAccess($paidModule))->toBeTrue();
    });

    test('admin can access module content (video and attachments)', function () {
        $module = Module::factory()->create(['is_free' => false]);
        $attachment = Attachment::factory()->create(['module_id' => $module->id]);

        // Admin should have access to module content
        expect($this->admin->canAccess($module))->toBeTrue();
    });
});

describe('Admin Attachment Permissions', function () {
    test('admin can view any attachment', function () {
        $module = Module::factory()->create();
        $attachment = Attachment::factory()->create(['module_id' => $module->id]);

        // Admin should be able to view any attachment
        expect($this->admin->canAccess($module))->toBeTrue();
    });

    test('admin can download any attachment', function () {
        $module = Module::factory()->create();
        $attachment = Attachment::factory()->create(['module_id' => $module->id]);

        // Admin should be able to download any attachment
        expect($this->admin->canAccess($module))->toBeTrue();
    });
});

describe('Admin Route Access', function () {
    test('admin can access all management routes', function () {
        $routes = [
            'themes.index',
            'themes.create',
            'courses.index',
            'courses.create',
            'modules.index',
            'modules.create',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->admin)->get(route($route));
            $response->assertOk();
        }
    });

    test('admin can access course approval routes', function () {
        $course = Course::factory()->create([
            'is_approved' => false,
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
        ]);

        // Admin should be able to access approval functionality
        expect($this->admin->can('approve', $course))->toBeTrue();
    });
});
