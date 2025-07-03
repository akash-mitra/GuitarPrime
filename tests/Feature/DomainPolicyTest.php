<?php

use App\Models\Course;
use App\Models\Module;
use App\Models\Theme;
use App\Models\User;

test('only admin can create theme', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $coach = User::factory()->create(['role' => 'coach']);
    $student = User::factory()->create(['role' => 'student']);

    expect($admin->can('create', Theme::class))->toBeTrue();
    expect($coach->can('create', Theme::class))->toBeFalse();
    expect($student->can('create', Theme::class))->toBeFalse();
});

test('admin can delete themes but coach cannot', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $coach = User::factory()->create(['role' => 'coach']);
    $theme = Theme::factory()->create();

    expect($admin->can('delete', $theme))->toBeTrue();
    expect($coach->can('delete', $theme))->toBeFalse();
});

test('coach can update own course but not others', function () {
    $coach1 = User::factory()->create(['role' => 'coach']);
    $coach2 = User::factory()->create(['role' => 'coach']);
    $theme = Theme::factory()->create();
    $course = Course::factory()->create(['coach_id' => $coach1->id, 'theme_id' => $theme->id]);

    expect($coach1->can('update', $course))->toBeTrue();
    expect($coach2->can('update', $course))->toBeFalse();
});

test('only admin can approve courses', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $coach = User::factory()->create(['role' => 'coach']);
    $theme = Theme::factory()->create();
    $course = Course::factory()->create(['coach_id' => $coach->id, 'theme_id' => $theme->id]);

    expect($admin->can('approve', $course))->toBeTrue();
    expect($coach->can('approve', $course))->toBeFalse();
});

test('all roles can view modules', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $coach = User::factory()->create(['role' => 'coach']);
    $student = User::factory()->create(['role' => 'student']);
    $module = Module::factory()->create();

    expect($admin->can('view', $module))->toBeTrue();
    expect($coach->can('view', $module))->toBeTrue();
    expect($student->can('view', $module))->toBeTrue();
});
