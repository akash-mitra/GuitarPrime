<?php

use App\Models\Course;
use App\Models\Module;
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

test('student can access courses index for browsing', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->get(route('courses.index'));

    // Students can now browse courses
    $response->assertStatus(200);
});

test('coach can create course with default unapproved status', function () {
    $coach = User::factory()->create(['role' => 'coach']);
    $theme = Theme::factory()->create();

    $response = $this->actingAs($coach)->post(route('courses.store'), [
        'theme_id' => $theme->id,
        'title' => 'Guitar Fundamentals',
        'description' => 'Learn the basics of guitar playing',
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
        'is_approved' => false,
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
        'is_approved' => false,
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
        'title' => 'Original Title',
    ]);

    $response = $this->actingAs($coach)->put(route('courses.update', $course), [
        'theme_id' => $theme->id,
        'title' => 'Updated Title',
        'description' => 'Updated description',
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
        'theme_id' => $theme->id,
    ]);

    $response = $this->actingAs($coach2)->put(route('courses.update', $course), [
        'theme_id' => $theme->id,
        'title' => 'Hacked Title',
        'description' => 'Hacked description',
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
        'is_approved' => false,
    ]);

    // Create approved course (should not appear in queue)
    Course::factory()->create([
        'coach_id' => $coach->id,
        'theme_id' => $theme->id,
        'is_approved' => true,
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
        'theme_id' => $theme->id,
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
        'description' => '', // Required
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
        'title' => 'Unapproved Course',
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
        'title' => 'Approved Course',
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
        'title' => 'Other Coach Course',
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
        'title' => 'Admin Delete Approved',
    ]);

    $response = $this->actingAs($admin)->delete(route('courses.destroy', $approvedCourse));
    $response->assertRedirect(route('courses.index'));
    $this->assertDatabaseMissing('courses', ['id' => $approvedCourse->id]);

    // Test deleting unapproved course
    $unapprovedCourse = Course::factory()->create([
        'coach_id' => $coach->id,
        'theme_id' => $theme->id,
        'is_approved' => false,
        'title' => 'Admin Delete Unapproved',
    ]);

    $response = $this->actingAs($admin)->delete(route('courses.destroy', $unapprovedCourse));
    $response->assertRedirect(route('courses.index'));
    $this->assertDatabaseMissing('courses', ['id' => $unapprovedCourse->id]);
});

// Module Management Tests

test('admin can create course with modules', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $theme = Theme::factory()->create();
    $module1 = Module::factory()->create(['title' => 'Module 1']);
    $module2 = Module::factory()->create(['title' => 'Module 2']);
    $module3 = Module::factory()->create(['title' => 'Module 3']);

    $response = $this->actingAs($admin)->post(route('courses.store'), [
        'theme_id' => $theme->id,
        'title' => 'Course with Modules',
        'description' => 'A course that includes modules',
        'module_ids' => [$module1->id, $module3->id, $module2->id], // Order matters
    ]);

    $response->assertRedirect(route('courses.index'));

    $course = Course::where('title', 'Course with Modules')->first();
    expect($course)->not->toBeNull();
    expect($course->coach_id)->toBe($admin->id);

    // Check modules are attached with correct order
    $this->assertDatabaseHas('course_module_map', [
        'course_id' => $course->id,
        'module_id' => $module1->id,
        'order' => 1,
    ]);

    $this->assertDatabaseHas('course_module_map', [
        'course_id' => $course->id,
        'module_id' => $module3->id,
        'order' => 2,
    ]);

    $this->assertDatabaseHas('course_module_map', [
        'course_id' => $course->id,
        'module_id' => $module2->id,
        'order' => 3,
    ]);

    // Verify the course has 3 modules
    expect($course->modules()->count())->toBe(3);
});

test('admin can create course without modules', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $theme = Theme::factory()->create();

    $response = $this->actingAs($admin)->post(route('courses.store'), [
        'theme_id' => $theme->id,
        'title' => 'Course without Modules',
        'description' => 'A course with no modules',
        'module_ids' => [],
    ]);

    $response->assertRedirect(route('courses.index'));

    $course = Course::where('title', 'Course without Modules')->first();
    expect($course)->not->toBeNull();
    expect($course->modules()->count())->toBe(0);
});

test('coach cannot create course with modules', function () {
    $coach = User::factory()->create(['role' => 'coach']);
    $theme = Theme::factory()->create();
    $module = Module::factory()->create();

    $response = $this->actingAs($coach)->post(route('courses.store'), [
        'theme_id' => $theme->id,
        'title' => 'Coach Course with Modules',
        'description' => 'A coach trying to add modules',
        'module_ids' => [$module->id],
    ]);

    $response->assertRedirect(route('courses.index'));

    $course = Course::where('title', 'Coach Course with Modules')->first();
    expect($course)->not->toBeNull();

    // Coach's module_ids should be ignored
    expect($course->modules()->count())->toBe(0);
});

test('admin can update course modules', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $theme = Theme::factory()->create();
    $module1 = Module::factory()->create(['title' => 'Module 1']);
    $module2 = Module::factory()->create(['title' => 'Module 2']);
    $module3 = Module::factory()->create(['title' => 'Module 3']);
    $module4 = Module::factory()->create(['title' => 'Module 4']);

    // Create course with initial modules
    $course = Course::factory()->create([
        'theme_id' => $theme->id,
        'coach_id' => $admin->id,
        'title' => 'Test Course',
    ]);

    // Attach initial modules
    $course->modules()->attach($module1->id, ['order' => 1]);
    $course->modules()->attach($module2->id, ['order' => 2]);

    // Update course with different modules
    $response = $this->actingAs($admin)->put(route('courses.update', $course), [
        'theme_id' => $theme->id,
        'title' => 'Updated Course',
        'description' => 'Updated description',
        'module_ids' => [$module3->id, $module4->id, $module1->id], // New order and modules
    ]);

    $response->assertRedirect(route('courses.index'));

    // Check old modules are removed
    $this->assertDatabaseMissing('course_module_map', [
        'course_id' => $course->id,
        'module_id' => $module2->id,
    ]);

    // Check new modules are added with correct order
    $this->assertDatabaseHas('course_module_map', [
        'course_id' => $course->id,
        'module_id' => $module3->id,
        'order' => 1,
    ]);

    $this->assertDatabaseHas('course_module_map', [
        'course_id' => $course->id,
        'module_id' => $module4->id,
        'order' => 2,
    ]);

    $this->assertDatabaseHas('course_module_map', [
        'course_id' => $course->id,
        'module_id' => $module1->id,
        'order' => 3,
    ]);

    expect($course->fresh()->modules()->count())->toBe(3);
});

test('admin can remove all modules from course', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $theme = Theme::factory()->create();
    $module1 = Module::factory()->create();
    $module2 = Module::factory()->create();

    // Create course with modules
    $course = Course::factory()->create([
        'theme_id' => $theme->id,
        'coach_id' => $admin->id,
    ]);

    $course->modules()->attach($module1->id, ['order' => 1]);
    $course->modules()->attach($module2->id, ['order' => 2]);

    // Remove all modules
    $response = $this->actingAs($admin)->put(route('courses.update', $course), [
        'theme_id' => $theme->id,
        'title' => $course->title,
        'description' => $course->description,
        'module_ids' => [],
    ]);

    $response->assertRedirect(route('courses.index'));

    // Check all modules are removed
    $this->assertDatabaseMissing('course_module_map', [
        'course_id' => $course->id,
        'module_id' => $module1->id,
    ]);

    $this->assertDatabaseMissing('course_module_map', [
        'course_id' => $course->id,
        'module_id' => $module2->id,
    ]);

    expect($course->fresh()->modules()->count())->toBe(0);
});

test('coach cannot update course modules', function () {
    $coach = User::factory()->create(['role' => 'coach']);
    $theme = Theme::factory()->create();
    $module1 = Module::factory()->create();
    $module2 = Module::factory()->create();

    // Create course with initial modules (would be done by admin)
    $course = Course::factory()->create([
        'theme_id' => $theme->id,
        'coach_id' => $coach->id,
    ]);

    $course->modules()->attach($module1->id, ['order' => 1]);

    // Coach tries to update modules
    $response = $this->actingAs($coach)->put(route('courses.update', $course), [
        'theme_id' => $theme->id,
        'title' => 'Updated by Coach',
        'description' => 'Updated description',
        'module_ids' => [$module2->id], // Should be ignored
    ]);

    $response->assertRedirect(route('courses.index'));

    // Original module should still be there
    $this->assertDatabaseHas('course_module_map', [
        'course_id' => $course->id,
        'module_id' => $module1->id,
        'order' => 1,
    ]);

    // New module should not be added
    $this->assertDatabaseMissing('course_module_map', [
        'course_id' => $course->id,
        'module_id' => $module2->id,
    ]);

    expect($course->fresh()->modules()->count())->toBe(1);
});

test('module_ids validation works correctly', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $theme = Theme::factory()->create();

    // Test with invalid module IDs
    $response = $this->actingAs($admin)->post(route('courses.store'), [
        'theme_id' => $theme->id,
        'title' => 'Test Course',
        'description' => 'Test description',
        'module_ids' => ['invalid-id', 'another-invalid-id'],
    ]);

    $response->assertSessionHasErrors(['module_ids.0', 'module_ids.1']);
});

test('module_ids field accepts null and empty array', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $theme = Theme::factory()->create();

    // Test with null
    $response = $this->actingAs($admin)->post(route('courses.store'), [
        'theme_id' => $theme->id,
        'title' => 'Course with null modules',
        'description' => 'Test description',
        // module_ids is not provided (null)
    ]);

    $response->assertRedirect(route('courses.index'));

    $course = Course::where('title', 'Course with null modules')->first();
    expect($course->modules()->count())->toBe(0);
});

test('admin can create course with single module', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $theme = Theme::factory()->create();
    $module = Module::factory()->create();

    $response = $this->actingAs($admin)->post(route('courses.store'), [
        'theme_id' => $theme->id,
        'title' => 'Single Module Course',
        'description' => 'Course with one module',
        'module_ids' => [$module->id],
    ]);

    $response->assertRedirect(route('courses.index'));

    $course = Course::where('title', 'Single Module Course')->first();
    expect($course->modules()->count())->toBe(1);

    $this->assertDatabaseHas('course_module_map', [
        'course_id' => $course->id,
        'module_id' => $module->id,
        'order' => 1,
    ]);
});

test('module order is preserved during course update', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $theme = Theme::factory()->create();
    $module1 = Module::factory()->create(['title' => 'First']);
    $module2 = Module::factory()->create(['title' => 'Second']);
    $module3 = Module::factory()->create(['title' => 'Third']);

    $course = Course::factory()->create([
        'theme_id' => $theme->id,
        'coach_id' => $admin->id,
    ]);

    // Initial order: module1, module2, module3
    $course->modules()->attach($module1->id, ['order' => 1]);
    $course->modules()->attach($module2->id, ['order' => 2]);
    $course->modules()->attach($module3->id, ['order' => 3]);

    // Update with new order: module3, module1, module2
    $response = $this->actingAs($admin)->put(route('courses.update', $course), [
        'theme_id' => $theme->id,
        'title' => $course->title,
        'description' => $course->description,
        'module_ids' => [$module3->id, $module1->id, $module2->id],
    ]);

    $response->assertRedirect(route('courses.index'));

    // Check new order
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
