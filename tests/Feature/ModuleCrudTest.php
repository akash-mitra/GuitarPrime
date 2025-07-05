<?php

use App\Models\Course;
use App\Models\Module;
use App\Models\Theme;
use App\Models\User;

test('coach can view modules index', function () {
    $coach = User::factory()->create(['role' => 'coach']);
    Module::factory(3)->create();

    $response = $this->actingAs($coach)->get(route('modules.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('modules/Index')
        ->has('modules.data', 3)
    );
});

test('student cannot access modules index (admin/coach only)', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->get(route('modules.index'));

    // Students cannot access modules index directly
    $response->assertStatus(403);
});

test('coach can create module', function () {
    $coach = User::factory()->create(['role' => 'coach']);

    $response = $this->actingAs($coach)->post(route('modules.store'), [
        'title' => 'Guitar Scales',
        'description' => 'Learn basic guitar scales',
        'difficulty' => 'medium',
        'video_url' => 'https://vimeo.com/123456789',
    ]);

    $response->assertRedirect(route('modules.index'));
    $this->assertDatabaseHas('modules', [
        'title' => 'Guitar Scales',
        'difficulty' => 'medium',
    ]);
});

test('student cannot create module', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->post(route('modules.store'), [
        'title' => 'Guitar Scales',
        'description' => 'Learn basic guitar scales',
        'difficulty' => 'medium',
    ]);

    $response->assertStatus(403);
});

test('coach can edit module', function () {
    $coach = User::factory()->create(['role' => 'coach']);
    $module = Module::factory()->create([
        'title' => 'Original Title',
        'coach_id' => $coach->id,
    ]);

    $response = $this->actingAs($coach)->put(route('modules.update', $module), [
        'title' => 'Updated Title',
        'description' => 'Updated description',
        'difficulty' => 'hard',
    ]);

    $response->assertRedirect(route('modules.index'));
    $this->assertDatabaseHas('modules', [
        'id' => $module->id,
        'title' => 'Updated Title',
        'difficulty' => 'hard',
    ]);
});

test('admin can delete module', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $module = Module::factory()->create();

    $response = $this->actingAs($admin)->delete(route('modules.destroy', $module));

    $response->assertRedirect(route('modules.index'));
    $this->assertDatabaseMissing('modules', ['id' => $module->id]);
});

test('coach can delete their own module', function () {
    $coach = User::factory()->create(['role' => 'coach']);
    $module = Module::factory()->create(['coach_id' => $coach->id]);

    $response = $this->actingAs($coach)->delete(route('modules.destroy', $module));

    $response->assertRedirect(route('modules.index'));
    $this->assertDatabaseMissing('modules', ['id' => $module->id]);
});

test('coach cannot delete other coaches module', function () {
    $coach = User::factory()->create(['role' => 'coach']);
    $otherCoach = User::factory()->create(['role' => 'coach']);
    $module = Module::factory()->create(['coach_id' => $otherCoach->id]);

    $response = $this->actingAs($coach)->delete(route('modules.destroy', $module));

    $response->assertStatus(403);
    $this->assertDatabaseHas('modules', ['id' => $module->id]);
});

test('module validation works correctly', function () {
    $coach = User::factory()->create(['role' => 'coach']);

    $response = $this->actingAs($coach)->post(route('modules.store'), [
        'title' => '', // Required
        'description' => '', // Required
        'difficulty' => 'invalid', // Invalid value
        'video_url' => 'not-a-url', // Invalid URL
    ]);

    $response->assertSessionHasErrors(['title', 'description', 'difficulty', 'video_url']);
});

test('vimeo url validation works correctly', function () {
    $coach = User::factory()->create(['role' => 'coach']);

    // Valid Vimeo URL should pass
    $response = $this->actingAs($coach)->post(route('modules.store'), [
        'title' => 'Test Module',
        'description' => 'Test description',
        'difficulty' => 'easy',
        'video_url' => 'https://vimeo.com/123456789',
    ]);

    $response->assertRedirect(route('modules.index'));

    // Invalid Vimeo URL should fail
    $response = $this->actingAs($coach)->post(route('modules.store'), [
        'title' => 'Test Module 2',
        'description' => 'Test description',
        'difficulty' => 'easy',
        'video_url' => 'https://youtube.com/watch?v=123',
    ]);

    $response->assertSessionHasErrors(['video_url']);
});

test('coach can reorder modules in course', function () {
    $coach = User::factory()->create(['role' => 'coach']);
    $theme = Theme::factory()->create();
    $course = Course::factory()->create([
        'theme_id' => $theme->id,
        'coach_id' => $coach->id,
    ]);

    $module1 = Module::factory()->create(['coach_id' => $coach->id]);
    $module2 = Module::factory()->create(['coach_id' => $coach->id]);
    $module3 = Module::factory()->create(['coach_id' => $coach->id]);

    // Attach modules to course with initial order
    $course->modules()->attach($module1->id, ['order' => 1]);
    $course->modules()->attach($module2->id, ['order' => 2]);
    $course->modules()->attach($module3->id, ['order' => 3]);

    // Reorder modules
    $response = $this->actingAs($coach)->post(route('modules.reorder'), [
        'course_id' => $course->id,
        'modules' => [
            ['id' => $module3->id, 'order' => 1],
            ['id' => $module1->id, 'order' => 2],
            ['id' => $module2->id, 'order' => 3],
        ],
    ]);

    $response->assertRedirect();

    // Check new order in database
    $this->assertDatabaseHas('course_module_map', [
        'course_id' => $course->id,
        'module_id' => $module3->id,
        'order' => 1,
    ]);

    $this->assertDatabaseHas('course_module_map', [
        'course_id' => $course->id,
        'module_id' => $module1->id,
        'order' => 2,
    ]);

    $this->assertDatabaseHas('course_module_map', [
        'course_id' => $course->id,
        'module_id' => $module2->id,
        'order' => 3,
    ]);
});

test('student cannot reorder modules', function () {
    $student = User::factory()->create(['role' => 'student']);
    $theme = Theme::factory()->create();
    $course = Course::factory()->create(['theme_id' => $theme->id]);
    $module = Module::factory()->create();

    $response = $this->actingAs($student)->post(route('modules.reorder'), [
        'course_id' => $course->id,
        'modules' => [
            ['id' => $module->id, 'order' => 1],
        ],
    ]);

    $response->assertStatus(403);
});

test('reorder validation works correctly', function () {
    $coach = User::factory()->create(['role' => 'coach']);
    $theme = Theme::factory()->create();
    $course = Course::factory()->create([
        'theme_id' => $theme->id,
        'coach_id' => $coach->id,
    ]);

    $response = $this->actingAs($coach)->post(route('modules.reorder'), [
        'course_id' => '', // Required
        'modules' => [], // Required array
    ]);

    $response->assertSessionHasErrors(['course_id', 'modules']);
});
