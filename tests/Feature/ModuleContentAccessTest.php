<?php

use App\Models\Attachment;
use App\Models\Course;
use App\Models\Module;
use App\Models\Purchase;
use App\Models\Theme;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->coach = User::factory()->create(['role' => 'coach']);
    $this->otherCoach = User::factory()->create(['role' => 'coach']);
    $this->student = User::factory()->create(['role' => 'student']);
    $this->theme = Theme::factory()->create();
});

describe('Module Content Access (MCA) - Core Rules', function () {
    test('admin has MCA to all modules', function () {
        $freeModule = Module::factory()->create(['is_free' => true]);
        $paidModule = Module::factory()->create(['is_free' => false]);
        $ownModule = Module::factory()->create(['coach_id' => $this->admin->id, 'is_free' => false]);

        expect($this->admin->canAccess($freeModule))->toBeTrue();
        expect($this->admin->canAccess($paidModule))->toBeTrue();
        expect($this->admin->canAccess($ownModule))->toBeTrue();
    });

    test('coach has MCA to own modules', function () {
        $ownFreeModule = Module::factory()->create([
            'coach_id' => $this->coach->id,
            'is_free' => true,
        ]);

        $ownPaidModule = Module::factory()->create([
            'coach_id' => $this->coach->id,
            'is_free' => false,
        ]);

        expect($this->coach->canAccess($ownFreeModule))->toBeTrue();
        expect($this->coach->canAccess($ownPaidModule))->toBeTrue();
    });

    test('student has MCA to modules in subscribed courses', function () {
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

        Purchase::factory()->create([
            'user_id' => $this->student->id,
            'purchasable_type' => Course::class,
            'purchasable_id' => $course->id,
            'status' => 'completed',
        ]);

        expect($this->student->canAccess($moduleInCourse))->toBeTrue();
    });

    test('users have MCA to free modules regardless of subscription', function () {
        $freeModule = Module::factory()->create(['is_free' => true]);

        expect($this->admin->canAccess($freeModule))->toBeTrue();
        expect($this->coach->canAccess($freeModule))->toBeTrue();
        expect($this->otherCoach->canAccess($freeModule))->toBeTrue();
        expect($this->student->canAccess($freeModule))->toBeTrue();
    });
});

describe('Module Content Access - Video Access', function () {
    test('admin can access video in any module', function () {
        $moduleWithVideo = Module::factory()->create([
            'coach_id' => $this->coach->id,
            'is_free' => false,
            'video_url' => 'https://vimeo.com/123456789',
        ]);

        expect($this->admin->canAccess($moduleWithVideo))->toBeTrue();
    });

    test('coach can access video in own modules', function () {
        $ownModuleWithVideo = Module::factory()->create([
            'coach_id' => $this->coach->id,
            'is_free' => false,
            'video_url' => 'https://vimeo.com/123456789',
        ]);

        expect($this->coach->canAccess($ownModuleWithVideo))->toBeTrue();
    });

    test('coach cannot access video in other modules without subscription', function () {
        $otherModuleWithVideo = Module::factory()->create([
            'coach_id' => $this->otherCoach->id,
            'is_free' => false,
            'video_url' => 'https://vimeo.com/123456789',
        ]);

        expect($this->coach->canAccess($otherModuleWithVideo))->toBeFalse();
    });

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

    test('student cannot access video in non-subscribed modules', function () {
        $moduleWithVideo = Module::factory()->create([
            'coach_id' => $this->coach->id,
            'is_free' => false,
            'video_url' => 'https://vimeo.com/123456789',
        ]);

        expect($this->student->canAccess($moduleWithVideo))->toBeFalse();
    });
});

describe('Module Content Access - Attachment Access', function () {
    test('admin can access attachments in any module', function () {
        $moduleWithAttachment = Module::factory()->create([
            'coach_id' => $this->coach->id,
            'is_free' => false,
        ]);

        $attachment = Attachment::factory()->create(['module_id' => $moduleWithAttachment->id]);

        expect($this->admin->canAccess($moduleWithAttachment))->toBeTrue();
    });

    test('coach can access attachments in own modules', function () {
        $ownModuleWithAttachment = Module::factory()->create([
            'coach_id' => $this->coach->id,
            'is_free' => false,
        ]);

        $attachment = Attachment::factory()->create(['module_id' => $ownModuleWithAttachment->id]);

        expect($this->coach->canAccess($ownModuleWithAttachment))->toBeTrue();
    });

    test('coach cannot access attachments in other modules without subscription', function () {
        $otherModuleWithAttachment = Module::factory()->create([
            'coach_id' => $this->otherCoach->id,
            'is_free' => false,
        ]);

        $attachment = Attachment::factory()->create(['module_id' => $otherModuleWithAttachment->id]);

        expect($this->coach->canAccess($otherModuleWithAttachment))->toBeFalse();
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

    test('student cannot access attachments in non-subscribed modules', function () {
        $moduleWithAttachment = Module::factory()->create([
            'coach_id' => $this->coach->id,
            'is_free' => false,
        ]);

        $attachment = Attachment::factory()->create(['module_id' => $moduleWithAttachment->id]);

        expect($this->student->canAccess($moduleWithAttachment))->toBeFalse();
    });
});

describe('Module Content Access - Complex Scenarios', function () {
    test('module in multiple courses - access through any subscription', function () {
        $course1 = Course::factory()->create([
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
            'is_approved' => true,
        ]);

        $course2 = Course::factory()->create([
            'coach_id' => $this->otherCoach->id,
            'theme_id' => $this->theme->id,
            'is_approved' => true,
        ]);

        $sharedModule = Module::factory()->create([
            'coach_id' => $this->coach->id,
            'is_free' => false,
        ]);

        $course1->modules()->attach($sharedModule->id, ['order' => 1]);
        $course2->modules()->attach($sharedModule->id, ['order' => 1]);

        // Subscribe to course2 only
        Purchase::factory()->create([
            'user_id' => $this->student->id,
            'purchasable_type' => Course::class,
            'purchasable_id' => $course2->id,
            'status' => 'completed',
        ]);

        // Student should have access through course2 subscription
        expect($this->student->canAccess($sharedModule))->toBeTrue();
    });

    test('coach accessing other coach modules through subscription', function () {
        $course = Course::factory()->create([
            'coach_id' => $this->otherCoach->id,
            'theme_id' => $this->theme->id,
            'is_approved' => true,
        ]);

        $moduleByOtherCoach = Module::factory()->create([
            'coach_id' => $this->otherCoach->id,
            'is_free' => false,
        ]);

        $course->modules()->attach($moduleByOtherCoach->id, ['order' => 1]);

        // Before subscription
        expect($this->coach->canAccess($moduleByOtherCoach))->toBeFalse();

        // After subscription
        Purchase::factory()->create([
            'user_id' => $this->coach->id,
            'purchasable_type' => Course::class,
            'purchasable_id' => $course->id,
            'status' => 'completed',
        ]);

        expect($this->coach->canAccess($moduleByOtherCoach))->toBeTrue();
    });

    test('MCA permission overrides regular view permission', function () {
        $paidModule = Module::factory()->create([
            'coach_id' => $this->coach->id,
            'is_free' => false,
        ]);

        // Student can view module details but cannot access content
        expect($this->student->can('view', $paidModule))->toBeTrue();
        expect($this->student->canAccess($paidModule))->toBeFalse();
    });

    test('subscription status must be completed for MCA', function () {
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

        // Pending purchase - no access
        Purchase::factory()->create([
            'user_id' => $this->student->id,
            'purchasable_type' => Course::class,
            'purchasable_id' => $course->id,
            'status' => 'pending',
        ]);

        expect($this->student->canAccess($moduleInCourse))->toBeFalse();

        // Failed purchase - no access
        Purchase::factory()->create([
            'user_id' => $this->student->id,
            'purchasable_type' => Course::class,
            'purchasable_id' => $course->id,
            'status' => 'failed',
        ]);

        expect($this->student->canAccess($moduleInCourse))->toBeFalse();
    });
});

describe('Module Content Access - UI Integration', function () {
    test('MCA permission passed to frontend for module show page', function () {
        $module = Module::factory()->create([
            'coach_id' => $this->coach->id,
            'is_free' => false,
        ]);

        $response = $this->actingAs($this->admin)->get(route('modules.show', $module));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('modules/Show')
            ->where('canAccess', true)
        );
    });

    test('MCA permission passed to frontend for course module show page', function () {
        $course = Course::factory()->create([
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
            'is_approved' => true,
        ]);

        $module = Module::factory()->create([
            'coach_id' => $this->coach->id,
            'is_free' => false,
        ]);

        $course->modules()->attach($module->id, ['order' => 1]);

        $response = $this->actingAs($this->coach)->get(route('courses.modules.show', [$course, $module]));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Courses/modules/Show')
            ->where('canAccessModule', true)
        );
    });
});
