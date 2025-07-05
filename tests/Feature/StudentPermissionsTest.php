<?php

use App\Models\Attachment;
use App\Models\Course;
use App\Models\Module;
use App\Models\Purchase;
use App\Models\Theme;
use App\Models\User;

beforeEach(function () {
    $this->student = User::factory()->create(['role' => 'student']);
    $this->otherStudent = User::factory()->create(['role' => 'student']);
    $this->coach = User::factory()->create(['role' => 'coach']);
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->theme = Theme::factory()->create();
});

describe('Student Theme Permissions', function () {
    test('student can view themes', function () {
        $themes = Theme::factory()->count(3)->create();

        expect($this->student->can('viewAny', Theme::class))->toBeTrue();

        foreach ($themes as $theme) {
            expect($this->student->can('view', $theme))->toBeTrue();
        }
    });

    test('student cannot create themes', function () {
        expect($this->student->can('create', Theme::class))->toBeFalse();
    });

    test('student cannot update themes', function () {
        $theme = Theme::factory()->create();
        expect($this->student->can('update', $theme))->toBeFalse();
    });

    test('student cannot delete themes', function () {
        $theme = Theme::factory()->create();
        expect($this->student->can('delete', $theme))->toBeFalse();
    });

    test('student cannot submit new courses to themes', function () {
        expect($this->student->can('create', Course::class))->toBeFalse();
    });
});

describe('Student Course Permissions', function () {
    test('student can view approved courses', function () {
        $approvedCourse = Course::factory()->create([
            'is_approved' => true,
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
        ]);

        expect($this->student->can('viewAny', Course::class))->toBeTrue();
        expect($this->student->can('view', $approvedCourse))->toBeTrue();
    });

    test('student cannot view pending courses', function () {
        $pendingCourse = Course::factory()->create([
            'is_approved' => false,
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
        ]);

        expect($this->student->can('view', $pendingCourse))->toBeFalse();
    });

    test('student cannot create courses', function () {
        expect($this->student->can('create', Course::class))->toBeFalse();
    });

    test('student cannot update courses', function () {
        $course = Course::factory()->create([
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
        ]);

        expect($this->student->can('update', $course))->toBeFalse();
    });

    test('student cannot delete courses', function () {
        $course = Course::factory()->create([
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
        ]);

        expect($this->student->can('delete', $course))->toBeFalse();
    });

    test('student cannot approve courses', function () {
        $course = Course::factory()->create([
            'is_approved' => false,
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
        ]);

        expect($this->student->can('approve', $course))->toBeFalse();
    });
});

describe('Student Module Permissions - Basic Access', function () {
    test('student can view module list', function () {
        $module = Module::factory()->create(['coach_id' => $this->coach->id]);

        expect($this->student->can('viewAny', Module::class))->toBeTrue();
        expect($this->student->can('view', $module))->toBeTrue();
    });

    test('student cannot create modules', function () {
        expect($this->student->can('create', Module::class))->toBeFalse();
    });

    test('student cannot update modules', function () {
        $module = Module::factory()->create(['coach_id' => $this->coach->id]);

        expect($this->student->can('update', $module))->toBeFalse();
    });

    test('student cannot delete modules', function () {
        $module = Module::factory()->create(['coach_id' => $this->coach->id]);

        expect($this->student->can('delete', $module))->toBeFalse();
    });
});

describe('Student Module Content Access - Subscription Based', function () {
    test('student can access free modules regardless of subscription', function () {
        $freeModule = Module::factory()->create([
            'coach_id' => $this->coach->id,
            'is_free' => true,
        ]);

        expect($this->student->canAccess($freeModule))->toBeTrue();
    });

    test('student cannot access paid modules without subscription', function () {
        $paidModule = Module::factory()->create([
            'coach_id' => $this->coach->id,
            'is_free' => false,
        ]);

        expect($this->student->canAccess($paidModule))->toBeFalse();
    });

    test('student can access modules in subscribed courses', function () {
        $course = Course::factory()->create([
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
            'is_approved' => true,
        ]);

        $moduleInCourse = Module::factory()->create([
            'coach_id' => $this->coach->id,
            'is_free' => false,
        ]);

        $course->modules()->attach($moduleInCourse->id, ['order' => 1]);

        // Before subscription - no access
        expect($this->student->canAccess($moduleInCourse))->toBeFalse();

        // After subscription - has access
        Purchase::factory()->create([
            'user_id' => $this->student->id,
            'purchasable_type' => Course::class,
            'purchasable_id' => $course->id,
            'status' => 'completed',
        ]);

        expect($this->student->canAccess($moduleInCourse))->toBeTrue();
    });

    test('student cannot access modules in non-subscribed courses', function () {
        $course = Course::factory()->create([
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
            'is_approved' => true,
        ]);

        $moduleInCourse = Module::factory()->create([
            'coach_id' => $this->coach->id,
            'is_free' => false,
        ]);

        $course->modules()->attach($moduleInCourse->id, ['order' => 1]);

        expect($this->student->canAccess($moduleInCourse))->toBeFalse();
    });

    test('student can access modules across multiple subscribed courses', function () {
        $course1 = Course::factory()->create([
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
            'is_approved' => true,
        ]);

        $course2 = Course::factory()->create([
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
            'is_approved' => true,
        ]);

        $sharedModule = Module::factory()->create([
            'coach_id' => $this->coach->id,
            'is_free' => false,
        ]);

        // Same module used in both courses
        $course1->modules()->attach($sharedModule->id, ['order' => 1]);
        $course2->modules()->attach($sharedModule->id, ['order' => 1]);

        // Subscribe to only one course
        Purchase::factory()->create([
            'user_id' => $this->student->id,
            'purchasable_type' => Course::class,
            'purchasable_id' => $course1->id,
            'status' => 'completed',
        ]);

        // Student should have access to module through course1 subscription
        expect($this->student->canAccess($sharedModule))->toBeTrue();
    });

    test('student loses access when subscription is not completed', function () {
        $course = Course::factory()->create([
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
            'is_approved' => true,
        ]);

        $moduleInCourse = Module::factory()->create([
            'coach_id' => $this->coach->id,
            'is_free' => false,
        ]);

        $course->modules()->attach($moduleInCourse->id, ['order' => 1]);

        // Purchase with pending status
        Purchase::factory()->create([
            'user_id' => $this->student->id,
            'purchasable_type' => Course::class,
            'purchasable_id' => $course->id,
            'status' => 'pending',
        ]);

        expect($this->student->canAccess($moduleInCourse))->toBeFalse();
    });
});

describe('Student Module Content Access - Video and Attachments', function () {
    test('student can access video in subscribed modules', function () {
        $course = Course::factory()->create([
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
            'is_approved' => true,
        ]);

        $moduleWithVideo = Module::factory()->create([
            'coach_id' => $this->coach->id,
            'is_free' => false,
            'video_url' => 'https://vimeo.com/123456789',
        ]);

        $course->modules()->attach($moduleWithVideo->id, ['order' => 1]);

        Purchase::factory()->create([
            'user_id' => $this->student->id,
            'purchasable_type' => Course::class,
            'purchasable_id' => $course->id,
            'status' => 'completed',
        ]);

        expect($this->student->canAccess($moduleWithVideo))->toBeTrue();
    });

    test('student can access attachments in subscribed modules', function () {
        $course = Course::factory()->create([
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
            'is_approved' => true,
        ]);

        $moduleWithAttachment = Module::factory()->create([
            'coach_id' => $this->coach->id,
            'is_free' => false,
        ]);

        $attachment = Attachment::factory()->create(['module_id' => $moduleWithAttachment->id]);

        $course->modules()->attach($moduleWithAttachment->id, ['order' => 1]);

        Purchase::factory()->create([
            'user_id' => $this->student->id,
            'purchasable_type' => Course::class,
            'purchasable_id' => $course->id,
            'status' => 'completed',
        ]);

        expect($this->student->canAccess($moduleWithAttachment))->toBeTrue();
    });

    test('student cannot access video in non-subscribed modules', function () {
        $moduleWithVideo = Module::factory()->create([
            'coach_id' => $this->coach->id,
            'is_free' => false,
            'video_url' => 'https://vimeo.com/123456789',
        ]);

        expect($this->student->canAccess($moduleWithVideo))->toBeFalse();
    });

    test('student cannot access attachments in non-subscribed modules', function () {
        $moduleWithAttachment = Module::factory()->create([
            'coach_id' => $this->coach->id,
            'is_free' => false,
        ]);

        $attachment = Attachment::factory()->create(['module_id' => $moduleWithAttachment->id]);

        expect($this->student->canAccess($moduleWithAttachment))->toBeFalse();
    });
});

describe('Student Route Access', function () {
    test('student can access viewing routes', function () {
        $routes = [
            'themes.index',
            'courses.index',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->student)->get(route($route));
            $response->assertOk();
        }
    });

    test('student cannot access management routes', function () {
        $routes = [
            'themes.create',
            'courses.create',
            'modules.index',
            'modules.create',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->student)->get(route($route));
            $response->assertForbidden();
        }
    });

    test('student cannot access direct module routes', function () {
        $module = Module::factory()->create(['coach_id' => $this->coach->id]);

        $response = $this->actingAs($this->student)->get(route('modules.show', $module));
        $response->assertForbidden();
    });
});
