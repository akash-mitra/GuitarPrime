<?php

use App\Models\Course;
use App\Models\User;

beforeEach(function () {
    $this->student = User::factory()->create(['role' => 'student']);
    $this->coach = User::factory()->create(['role' => 'coach']);
    $this->admin = User::factory()->create(['role' => 'admin']);
});

test('student can access courses index page', function () {
    $response = $this->actingAs($this->student)
        ->get('/courses');

    $response->assertOk();
});

test('student can only see approved courses in index', function () {
    $approvedCourse = Course::factory()->create([
        'is_approved' => true,
        'title' => 'Approved Course',
    ]);

    $pendingCourse = Course::factory()->create([
        'is_approved' => false,
        'title' => 'Pending Course',
    ]);

    $response = $this->actingAs($this->student)
        ->get('/courses');

    $response->assertOk();

    $courses = $response->getOriginalContent()->getData()['page']['props']['courses']['data'];
    expect($courses)->toHaveCount(1);
    expect($courses[0]['title'])->toBe('Approved Course');
    expect($courses[0]['is_approved'])->toBe(true);
});

test('coach can see all approved courses plus their own unapproved courses', function () {
    // Create approved courses by different coaches
    $approvedCourseByOther = Course::factory()->create([
        'is_approved' => true,
        'title' => 'Approved by Other Coach',
    ]);

    $approvedCourseByCoach = Course::factory()->create([
        'coach_id' => $this->coach->id,
        'is_approved' => true,
        'title' => 'Approved by This Coach',
    ]);

    // Create unapproved courses
    $pendingCourseByCoach = Course::factory()->create([
        'coach_id' => $this->coach->id,
        'is_approved' => false,
        'title' => 'Pending by This Coach',
    ]);

    $pendingCourseByOther = Course::factory()->create([
        'is_approved' => false,
        'title' => 'Pending by Other Coach',
    ]);

    $response = $this->actingAs($this->coach)
        ->get('/courses');

    $response->assertOk();

    $courses = $response->getOriginalContent()->getData()['page']['props']['courses']['data'];
    expect($courses)->toHaveCount(3);

    $courseTitles = collect($courses)->pluck('title')->toArray();
    expect($courseTitles)->toContain('Approved by Other Coach');
    expect($courseTitles)->toContain('Approved by This Coach');
    expect($courseTitles)->toContain('Pending by This Coach');
    expect($courseTitles)->not->toContain('Pending by Other Coach');
});

test('admin can see all courses regardless of approval status', function () {
    $approvedCourse = Course::factory()->create([
        'is_approved' => true,
        'title' => 'Approved Course',
    ]);

    $pendingCourse = Course::factory()->create([
        'is_approved' => false,
        'title' => 'Pending Course',
    ]);

    $response = $this->actingAs($this->admin)
        ->get('/courses');

    $response->assertOk();

    $courses = $response->getOriginalContent()->getData()['page']['props']['courses']['data'];
    expect($courses)->toHaveCount(2);

    $courseTitles = collect($courses)->pluck('title')->toArray();
    expect($courseTitles)->toContain('Approved Course');
    expect($courseTitles)->toContain('Pending Course');
});

test('student can view approved course details', function () {
    $course = Course::factory()->create([
        'is_approved' => true,
    ]);

    $response = $this->actingAs($this->student)
        ->get("/courses/{$course->id}");

    $response->assertOk();
});

test('student cannot view pending course details', function () {
    $course = Course::factory()->create([
        'is_approved' => false,
    ]);

    $response = $this->actingAs($this->student)
        ->get("/courses/{$course->id}");

    $response->assertForbidden();
});

test('coach can view their own pending courses', function () {
    $course = Course::factory()->create([
        'coach_id' => $this->coach->id,
        'is_approved' => false,
    ]);

    $response = $this->actingAs($this->coach)
        ->get("/courses/{$course->id}");

    $response->assertOk();
});

test('coach cannot view other coaches pending courses', function () {
    $otherCoach = User::factory()->create(['role' => 'coach']);
    $course = Course::factory()->create([
        'coach_id' => $otherCoach->id,
        'is_approved' => false,
    ]);

    $response = $this->actingAs($this->coach)
        ->get("/courses/{$course->id}");

    $response->assertForbidden();
});

test('admin can view all courses', function () {
    $pendingCourse = Course::factory()->create([
        'is_approved' => false,
    ]);

    $response = $this->actingAs($this->admin)
        ->get("/courses/{$pendingCourse->id}");

    $response->assertOk();
});

test('student cannot access course management routes', function () {
    $response = $this->actingAs($this->student)
        ->get('/courses/create');

    // Middleware-protected routes return 403 when access is denied
    $response->assertForbidden();
});

test('student cannot access modules index (admin/coach only)', function () {
    $response = $this->actingAs($this->student)
        ->get('/modules');

    $response->assertForbidden();
});

test('student can access themes index', function () {
    $response = $this->actingAs($this->student)
        ->get('/themes');

    $response->assertOk();
});

test('student can view theme details', function () {
    $theme = \App\Models\Theme::factory()->create();

    $response = $this->actingAs($this->student)
        ->get("/themes/{$theme->id}");

    $response->assertOk();
});

test('student cannot access theme management routes', function () {
    $response = $this->actingAs($this->student)
        ->get('/themes/create');

    // Middleware-protected routes return 403 when access is denied
    $response->assertForbidden();
});
