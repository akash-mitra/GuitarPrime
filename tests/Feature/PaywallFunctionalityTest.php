<?php

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

// Module Access Control Tests
describe('Module Access Control', function () {
    test('admin can access all modules (free and paid)', function () {
        $freeModule = Module::factory()->create(['is_free' => true, 'price' => null]);
        $paidModule = Module::factory()->create(['is_free' => false, 'price' => 29.99]);

        expect($this->admin->canAccess($freeModule))->toBeTrue();
        expect($this->admin->canAccess($paidModule))->toBeTrue();
    });

    test('coach can access free modules from any coach', function () {
        $freeModule = Module::factory()->create(['is_free' => true, 'price' => null]);
        
        expect($this->coach->canAccess($freeModule))->toBeTrue();
        expect($this->otherCoach->canAccess($freeModule))->toBeTrue();
        expect($this->student->canAccess($freeModule))->toBeTrue();
    });

    test('coach can access paid modules in their own courses', function () {
        $paidModule = Module::factory()->create(['is_free' => false, 'price' => 29.99]);
        $course = Course::factory()->create([
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
            'is_approved' => true,
        ]);
        $course->modules()->attach($paidModule->id, ['order' => 1]);

        expect($this->coach->canAccess($paidModule))->toBeTrue();
    });

    test('coach cannot access paid modules from other coaches without purchase', function () {
        $paidModule = Module::factory()->create(['is_free' => false, 'price' => 29.99]);
        $course = Course::factory()->create([
            'coach_id' => $this->otherCoach->id,
            'theme_id' => $this->theme->id,
            'is_approved' => true,
        ]);
        $course->modules()->attach($paidModule->id, ['order' => 1]);

        expect($this->coach->canAccess($paidModule))->toBeFalse();
    });

    test('coach can access paid modules from other coaches after purchase', function () {
        $paidModule = Module::factory()->create(['is_free' => false, 'price' => 29.99]);
        $course = Course::factory()->create([
            'coach_id' => $this->otherCoach->id,
            'theme_id' => $this->theme->id,
            'is_approved' => true,
        ]);
        $course->modules()->attach($paidModule->id, ['order' => 1]);

        // Create a completed purchase
        Purchase::factory()->create([
            'user_id' => $this->coach->id,
            'purchasable_type' => Module::class,
            'purchasable_id' => $paidModule->id,
            'status' => 'completed',
        ]);

        expect($this->coach->canAccess($paidModule))->toBeTrue();
    });

    test('student cannot access paid modules without purchase', function () {
        $paidModule = Module::factory()->create(['is_free' => false, 'price' => 29.99]);

        expect($this->student->canAccess($paidModule))->toBeFalse();
    });

    test('student can access paid modules after purchase', function () {
        $paidModule = Module::factory()->create(['is_free' => false, 'price' => 29.99]);

        // Create a completed purchase
        Purchase::factory()->create([
            'user_id' => $this->student->id,
            'purchasable_type' => Module::class,
            'purchasable_id' => $paidModule->id,
            'status' => 'completed',
        ]);

        expect($this->student->canAccess($paidModule))->toBeTrue();
    });
});

// Course Access Control Tests
describe('Course Access Control', function () {
    test('admin can access all courses (free and paid)', function () {
        $freeCourse = Course::factory()->create([
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
            'is_free' => true,
            'price' => null,
            'is_approved' => true,
        ]);
        $paidCourse = Course::factory()->create([
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
            'is_free' => false,
            'price' => 99.99,
            'is_approved' => true,
        ]);

        expect($this->admin->canAccess($freeCourse))->toBeTrue();
        expect($this->admin->canAccess($paidCourse))->toBeTrue();
    });

    test('coach can access their own courses', function () {
        $paidCourse = Course::factory()->create([
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
            'is_free' => false,
            'price' => 99.99,
            'is_approved' => true,
        ]);

        expect($this->coach->canAccess($paidCourse))->toBeTrue();
    });

    test('coach cannot access other coaches paid courses without purchase', function () {
        $paidCourse = Course::factory()->create([
            'coach_id' => $this->otherCoach->id,
            'theme_id' => $this->theme->id,
            'is_free' => false,
            'price' => 99.99,
            'is_approved' => true,
        ]);

        expect($this->coach->canAccess($paidCourse))->toBeFalse();
    });

    test('coach can access other coaches paid courses after purchase', function () {
        $paidCourse = Course::factory()->create([
            'coach_id' => $this->otherCoach->id,
            'theme_id' => $this->theme->id,
            'is_free' => false,
            'price' => 99.99,
            'is_approved' => true,
        ]);

        // Create a completed purchase
        Purchase::factory()->create([
            'user_id' => $this->coach->id,
            'purchasable_type' => Course::class,
            'purchasable_id' => $paidCourse->id,
            'status' => 'completed',
        ]);

        expect($this->coach->canAccess($paidCourse))->toBeTrue();
    });

    test('student can access free courses', function () {
        $freeCourse = Course::factory()->create([
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
            'is_free' => true,
            'price' => null,
            'is_approved' => true,
        ]);

        expect($this->student->canAccess($freeCourse))->toBeTrue();
    });

    test('student cannot access paid courses without purchase', function () {
        $paidCourse = Course::factory()->create([
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
            'is_free' => false,
            'price' => 99.99,
            'is_approved' => true,
        ]);

        expect($this->student->canAccess($paidCourse))->toBeFalse();
    });

    test('student can access paid courses after purchase', function () {
        $paidCourse = Course::factory()->create([
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
            'is_free' => false,
            'price' => 99.99,
            'is_approved' => true,
        ]);

        // Create a completed purchase
        Purchase::factory()->create([
            'user_id' => $this->student->id,
            'purchasable_type' => Course::class,
            'purchasable_id' => $paidCourse->id,
            'status' => 'completed',
        ]);

        expect($this->student->canAccess($paidCourse))->toBeTrue();
    });
});

// Frontend Integration Tests
describe('Frontend Integration', function () {
    test('module show page passes correct access data for admin', function () {
        $paidModule = Module::factory()->create(['is_free' => false, 'price' => 29.99]);

        $response = $this->actingAs($this->admin)
            ->get("/modules/{$paidModule->id}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('modules/Show')
            ->where('canAccess', true)
            ->where('pricing.is_free', false)
            ->where('pricing.price', '29.99')
            ->where('pricing.formatted_price', '$29.99')
        );
    });

    test('module show page passes correct access data for student without purchase', function () {
        $paidModule = Module::factory()->create(['is_free' => false, 'price' => 29.99]);

        $response = $this->actingAs($this->student)
            ->get("/modules/{$paidModule->id}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('modules/Show')
            ->where('canAccess', false)
            ->where('pricing.is_free', false)
            ->where('pricing.price', '29.99')
        );
    });

    test('module show page passes correct access data for student with purchase', function () {
        $paidModule = Module::factory()->create(['is_free' => false, 'price' => 29.99]);

        // Create a completed purchase
        Purchase::factory()->create([
            'user_id' => $this->student->id,
            'purchasable_type' => Module::class,
            'purchasable_id' => $paidModule->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($this->student)
            ->get("/modules/{$paidModule->id}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('modules/Show')
            ->where('canAccess', true)
            ->where('pricing.is_free', false)
            ->where('pricing.price', '29.99')
        );
    });

    test('course show page passes correct access data and module access mapping', function () {
        $paidCourse = Course::factory()->create([
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
            'is_free' => false,
            'price' => 99.99,
            'is_approved' => true,
        ]);

        $freeModule = Module::factory()->create(['is_free' => true, 'price' => null]);
        $paidModule = Module::factory()->create(['is_free' => false, 'price' => 29.99]);

        $paidCourse->modules()->attach([
            $freeModule->id => ['order' => 1],
            $paidModule->id => ['order' => 2],
        ]);

        $response = $this->actingAs($this->student)
            ->get("/courses/{$paidCourse->id}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Courses/Show')
            ->where('canAccess', false) // Student doesn't have course access
            ->where('pricing.is_free', false)
            ->where('pricing.price', '99.99')
            ->has('moduleAccess')
            ->where('moduleAccess.' . $freeModule->id, true) // Free module accessible
            ->where('moduleAccess.' . $paidModule->id, false) // Paid module not accessible
        );
    });

    test('coach can access own course and modules within', function () {
        $paidCourse = Course::factory()->create([
            'coach_id' => $this->coach->id,
            'theme_id' => $this->theme->id,
            'is_free' => false,
            'price' => 99.99,
            'is_approved' => true,
        ]);

        $paidModule = Module::factory()->create(['is_free' => false, 'price' => 29.99]);
        $paidCourse->modules()->attach($paidModule->id, ['order' => 1]);

        $response = $this->actingAs($this->coach)
            ->get("/courses/{$paidCourse->id}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Courses/Show')
            ->where('canAccess', true) // Coach owns the course
            ->where('moduleAccess.' . $paidModule->id, true) // Coach can access module in their course
        );
    });
});