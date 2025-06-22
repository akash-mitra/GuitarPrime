<?php

use App\Models\Course;
use App\Models\Theme;
use App\Models\User;

test('coach can view courses index', function () {
    $coach = User::factory()->create(['role' => 'coach']);
    $theme = Theme::factory()->create();
    Course::factory()->create(['coach_id' => $coach->id, 'theme_id' => $theme->id]);

    $response = $this->actingAs($coach)->get(route('courses.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Courses/Index')
        ->has('courses.data', 1)
    );
});

test('student cannot access courses index', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->get(route('courses.index'));

    $response->assertStatus(403);
});

test('coach can create course with default unapproved status', function () {
    $coach = User::factory()->create(['role' => 'coach']);
    $theme = Theme::factory()->create();

    $response = $this->actingAs($coach)->post(route('courses.store'), [
        'theme_id' => $theme->id,
        'title' => 'Guitar Fundamentals',
        'description' => 'Learn the basics of guitar playing'
    ]);

    $response->assertRedirect(route('courses.index'));

    $course = Course::where('title', 'Guitar Fundamentals')->first();
    expect($course)->not->toBeNull();
    expect($course->is_approved)->toBeFalse();
    expect($course->coach_id)->toBe($coach->id);
});

test('coach cannot approve their own course', function () {
    $coach = User::factory()->create(['role' => 'coach']);
    $theme = Theme::factory()->create();
    $course = Course::factory()->create([
        'coach_id' => $coach->id,
        'theme_id' => $theme->id,
        'is_approved' => false
    ]);

    $response = $this->actingAs($coach)->post(route('courses.approve', $course));

    $response->assertStatus(403);
    expect($course->fresh()->is_approved)->toBeFalse();
});

test('admin can approve course', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $coach = User::factory()->create(['role' => 'coach']);
    $theme = Theme::factory()->create();
    $course = Course::factory()->create([
        'coach_id' => $coach->id,
        'theme_id' => $theme->id,
        'is_approved' => false
    ]);

    $response = $this->actingAs($admin)->post(route('courses.approve', $course));

    $response->assertRedirect();
    expect($course->fresh()->is_approved)->toBeTrue();
});

test('coach can edit own course', function () {
    $coach = User::factory()->create(['role' => 'coach']);
    $theme = Theme::factory()->create();
    $course = Course::factory()->create([
        'coach_id' => $coach->id,
        'theme_id' => $theme->id,
        'title' => 'Original Title'
    ]);

    $response = $this->actingAs($coach)->put(route('courses.update', $course), [
        'theme_id' => $theme->id,
        'title' => 'Updated Title',
        'description' => 'Updated description'
    ]);

    $response->assertRedirect(route('courses.index'));
    expect($course->fresh()->title)->toBe('Updated Title');
});

test('coach cannot edit another coaches course', function () {
    $coach1 = User::factory()->create(['role' => 'coach']);
    $coach2 = User::factory()->create(['role' => 'coach']);
    $theme = Theme::factory()->create();
    $course = Course::factory()->create([
        'coach_id' => $coach1->id,
        'theme_id' => $theme->id
    ]);

    $response = $this->actingAs($coach2)->put(route('courses.update', $course), [
        'theme_id' => $theme->id,
        'title' => 'Hacked Title',
        'description' => 'Hacked description'
    ]);

    $response->assertStatus(403);
});

test('admin can view approval queue', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $coach = User::factory()->create(['role' => 'coach']);
    $theme = Theme::factory()->create();

    // Create pending course
    Course::factory()->create([
        'coach_id' => $coach->id,
        'theme_id' => $theme->id,
        'is_approved' => false
    ]);

    // Create approved course (should not appear in queue)
    Course::factory()->create([
        'coach_id' => $coach->id,
        'theme_id' => $theme->id,
        'is_approved' => true
    ]);

    $response = $this->actingAs($admin)->get(route('courses.approval-queue'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Courses/ApprovalQueue')
        ->has('courses.data', 1) // Only pending course
    );
});

test('coach cannot access approval queue', function () {
    $coach = User::factory()->create(['role' => 'coach']);

    $response = $this->actingAs($coach)->get(route('courses.approval-queue'));

    $response->assertStatus(403);
});

test('admin can delete course', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $coach = User::factory()->create(['role' => 'coach']);
    $theme = Theme::factory()->create();
    $course = Course::factory()->create([
        'coach_id' => $coach->id,
        'theme_id' => $theme->id
    ]);

    $response = $this->actingAs($admin)->delete(route('courses.destroy', $course));

    $response->assertRedirect(route('courses.index'));
    $this->assertDatabaseMissing('courses', ['id' => $course->id]);
});

test('course validation works correctly', function () {
    $coach = User::factory()->create(['role' => 'coach']);

    $response = $this->actingAs($coach)->post(route('courses.store'), [
        'theme_id' => '', // Required
        'title' => '', // Required
        'description' => '' // Required
    ]);

    $response->assertSessionHasErrors(['theme_id', 'title', 'description']);
});

test('coach can delete own unapproved course', function () {
    $coach = User::factory()->create(['role' => 'coach']);
    $theme = Theme::factory()->create();
    $course = Course::factory()->create([
        'coach_id' => $coach->id,
        'theme_id' => $theme->id,
        'is_approved' => false,
        'title' => 'Unapproved Course'
    ]);

    $response = $this->actingAs($coach)->delete(route('courses.destroy', $course));

    $response->assertRedirect(route('courses.index'));
    $this->assertDatabaseMissing('courses', ['id' => $course->id]);
});

test('coach cannot delete own approved course', function () {
    $coach = User::factory()->create(['role' => 'coach']);
    $theme = Theme::factory()->create();
    $course = Course::factory()->create([
        'coach_id' => $coach->id,
        'theme_id' => $theme->id,
        'is_approved' => true,
        'title' => 'Approved Course'
    ]);

    $response = $this->actingAs($coach)->delete(route('courses.destroy', $course));

    $response->assertStatus(403);
    $this->assertDatabaseHas('courses', ['id' => $course->id]);
});

test('coach cannot delete another coaches unapproved course', function () {
    $coach1 = User::factory()->create(['role' => 'coach']);
    $coach2 = User::factory()->create(['role' => 'coach']);
    $theme = Theme::factory()->create();
    $course = Course::factory()->create([
        'coach_id' => $coach1->id,
        'theme_id' => $theme->id,
        'is_approved' => false,
        'title' => 'Other Coach Course'
    ]);

    $response = $this->actingAs($coach2)->delete(route('courses.destroy', $course));

    $response->assertStatus(403);
    $this->assertDatabaseHas('courses', ['id' => $course->id]);
});

test('admin can delete any course regardless of approval status', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $coach = User::factory()->create(['role' => 'coach']);
    $theme = Theme::factory()->create();
    
    // Test deleting approved course
    $approvedCourse = Course::factory()->create([
        'coach_id' => $coach->id,
        'theme_id' => $theme->id,
        'is_approved' => true,
        'title' => 'Admin Delete Approved'
    ]);

    $response = $this->actingAs($admin)->delete(route('courses.destroy', $approvedCourse));
    $response->assertRedirect(route('courses.index'));
    $this->assertDatabaseMissing('courses', ['id' => $approvedCourse->id]);

    // Test deleting unapproved course
    $unapprovedCourse = Course::factory()->create([
        'coach_id' => $coach->id,
        'theme_id' => $theme->id,
        'is_approved' => false,
        'title' => 'Admin Delete Unapproved'
    ]);

    $response = $this->actingAs($admin)->delete(route('courses.destroy', $unapprovedCourse));
    $response->assertRedirect(route('courses.index'));
    $this->assertDatabaseMissing('courses', ['id' => $unapprovedCourse->id]);
});
