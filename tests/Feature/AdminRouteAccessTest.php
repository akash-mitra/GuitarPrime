<?php

use App\Models\Course;
use App\Models\Module;
use App\Models\Theme;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->coach = User::factory()->create(['role' => 'coach']);
    $this->student = User::factory()->create(['role' => 'student']);
});

// Theme Route Access Tests
test('admin can access themes/create route', function () {
    $response = $this->actingAs($this->admin)
        ->get('/themes/create');

    $response->assertOk();
});

test('admin can access themes edit route', function () {
    $theme = Theme::factory()->create();

    $response = $this->actingAs($this->admin)
        ->get("/themes/{$theme->id}/edit");

    $response->assertOk();
});

test('coach can access themes/create route', function () {
    $response = $this->actingAs($this->coach)
        ->get('/themes/create');

    $response->assertOk();
});

test('student cannot access themes/create route', function () {
    $response = $this->actingAs($this->student)
        ->get('/themes/create');

    $response->assertForbidden(); // Middleware returns 403 for access denied
});

// Course Route Access Tests
test('admin can access courses/create route', function () {
    $response = $this->actingAs($this->admin)
        ->get('/courses/create');

    $response->assertOk();
});

test('admin can access courses edit route', function () {
    $course = Course::factory()->create();

    $response = $this->actingAs($this->admin)
        ->get("/courses/{$course->id}/edit");

    $response->assertOk();
});

test('admin can access courses approval queue', function () {
    $response = $this->actingAs($this->admin)
        ->get('/courses/approval-queue');

    $response->assertOk();
});

test('coach can access courses/create route', function () {
    $response = $this->actingAs($this->coach)
        ->get('/courses/create');

    $response->assertOk();
});

test('coach cannot access courses approval queue', function () {
    $response = $this->actingAs($this->coach)
        ->get('/courses/approval-queue');

    $response->assertForbidden(); // Admin only route returns 403
});

test('student cannot access courses/create route', function () {
    $response = $this->actingAs($this->student)
        ->get('/courses/create');

    $response->assertForbidden(); // Middleware returns 403 for access denied
});

// Module Route Access Tests
test('admin can access modules/create route', function () {
    $response = $this->actingAs($this->admin)
        ->get('/modules/create');

    $response->assertOk();
});

test('admin can access modules edit route', function () {
    $module = Module::factory()->create();

    $response = $this->actingAs($this->admin)
        ->get("/modules/{$module->id}/edit");

    $response->assertOk();
});

test('coach can access modules/create route', function () {
    $response = $this->actingAs($this->coach)
        ->get('/modules/create');

    $response->assertOk();
});

test('student cannot access modules/create route', function () {
    $response = $this->actingAs($this->student)
        ->get('/modules/create');

    $response->assertForbidden(); // Middleware returns 403 for access denied
});

// Route ordering verification tests
test('create routes are not captured by parameterized routes', function () {
    // Verify that /themes/create doesn't match /themes/{theme}
    $response = $this->actingAs($this->admin)
        ->get('/themes/create');

    $response->assertOk();
    // If this was incorrectly matching /themes/{theme}, it would try to find a theme with id "create"
});

test('themes show route still works for actual theme IDs', function () {
    $theme = Theme::factory()->create();

    $response = $this->actingAs($this->student)
        ->get("/themes/{$theme->id}");

    $response->assertOk();
});

test('courses show route still works for actual course IDs', function () {
    $course = Course::factory()->create(['is_approved' => true]);

    $response = $this->actingAs($this->student)
        ->get("/courses/{$course->id}");

    $response->assertOk();
});

test('modules show route requires admin/coach access', function () {
    $module = Module::factory()->create();

    // Student cannot access direct module route
    $response = $this->actingAs($this->student)
        ->get("/modules/{$module->id}");
    $response->assertStatus(403);

    // Admin can access direct module route
    $response = $this->actingAs($this->admin)
        ->get("/modules/{$module->id}");
    $response->assertOk();
});
