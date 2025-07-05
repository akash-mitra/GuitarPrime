<?php

use App\Models\Theme;
use App\Models\User;

test('coach can view themes index', function () {
    $coach = User::factory()->create(['role' => 'coach']);
    Theme::factory(3)->create();

    $response = $this->actingAs($coach)->get(route('themes.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Themes/Index')
        ->has('themes.data', 3)
    );
});

test('student can access themes index for browsing', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->get(route('themes.index'));

    // Students can now browse themes
    $response->assertStatus(200);
});

test('admin can create theme', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post(route('themes.store'), [
        'name' => 'Guitar Basics',
        'description' => 'Learn the fundamentals of guitar playing',
    ]);

    $response->assertRedirect(route('themes.index'));
    $this->assertDatabaseHas('themes', [
        'name' => 'Guitar Basics',
        'description' => 'Learn the fundamentals of guitar playing',
    ]);
});

test('coach cannot create theme', function () {
    $coach = User::factory()->create(['role' => 'coach']);

    $response = $this->actingAs($coach)->post(route('themes.store'), [
        'name' => 'Guitar Basics',
        'description' => 'Learn the fundamentals',
    ]);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('themes', ['name' => 'Guitar Basics']);
});

test('student cannot create theme', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->post(route('themes.store'), [
        'name' => 'Guitar Basics',
        'description' => 'Learn the fundamentals',
    ]);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('themes', ['name' => 'Guitar Basics']);
});

test('admin can edit theme', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $theme = Theme::factory()->create(['name' => 'Original Name']);

    $response = $this->actingAs($admin)->put(route('themes.update', $theme), [
        'name' => 'Updated Name',
        'description' => 'Updated description',
    ]);

    $response->assertRedirect(route('themes.index'));
    $this->assertDatabaseHas('themes', [
        'id' => $theme->id,
        'name' => 'Updated Name',
        'description' => 'Updated description',
    ]);
});

test('coach cannot edit theme', function () {
    $coach = User::factory()->create(['role' => 'coach']);
    $theme = Theme::factory()->create(['name' => 'Original Name']);

    $response = $this->actingAs($coach)->put(route('themes.update', $theme), [
        'name' => 'Updated Name',
        'description' => 'Updated description',
    ]);

    $response->assertStatus(403);
    $this->assertDatabaseHas('themes', [
        'id' => $theme->id,
        'name' => 'Original Name',
    ]);
});

test('admin can delete theme', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $theme = Theme::factory()->create();

    $response = $this->actingAs($admin)->delete(route('themes.destroy', $theme));

    $response->assertRedirect(route('themes.index'));
    $this->assertDatabaseMissing('themes', ['id' => $theme->id]);
});

test('coach cannot delete theme', function () {
    $coach = User::factory()->create(['role' => 'coach']);
    $theme = Theme::factory()->create();

    $response = $this->actingAs($coach)->delete(route('themes.destroy', $theme));

    $response->assertStatus(403);
    $this->assertDatabaseHas('themes', ['id' => $theme->id]);
});

test('theme validation works correctly', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $response = $this->actingAs($admin)->post(route('themes.store'), [
        'name' => '', // Required field empty
        'description' => str_repeat('a', 1001), // Too long
    ]);

    $response->assertSessionHasErrors(['name', 'description']);
});

test('theme name must be unique', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Theme::factory()->create(['name' => 'Existing Theme']);

    $response = $this->actingAs($admin)->post(route('themes.store'), [
        'name' => 'Existing Theme',
        'description' => 'Some description',
    ]);

    $response->assertSessionHasErrors(['name']);
});

test('coach can view theme show page', function () {
    $coach = User::factory()->create(['role' => 'coach']);
    $theme = Theme::factory()->create([
        'name' => 'Test Theme',
        'description' => 'Test description',
    ]);

    $response = $this->actingAs($coach)->get(route('themes.show', $theme));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Themes/Show')
        ->has('theme')
        ->where('theme.id', $theme->id)
        ->where('theme.name', 'Test Theme')
        ->where('theme.description', 'Test description')
    );
});

test('student can view theme show page', function () {
    $student = User::factory()->create(['role' => 'student']);
    $theme = Theme::factory()->create();

    $response = $this->actingAs($student)->get(route('themes.show', $theme));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Themes/Show')
        ->has('theme')
        ->where('theme.id', $theme->id)
    );
});

test('theme show page displays approved courses only', function () {
    $coach = User::factory()->create(['role' => 'coach']);
    $theme = Theme::factory()->create();

    // Create approved course
    $approvedCourse = \App\Models\Course::factory()->create([
        'theme_id' => $theme->id,
        'coach_id' => $coach->id,
        'is_approved' => true,
        'title' => 'Approved Course',
    ]);

    // Create unapproved course
    \App\Models\Course::factory()->create([
        'theme_id' => $theme->id,
        'coach_id' => $coach->id,
        'is_approved' => false,
        'title' => 'Unapproved Course',
    ]);

    $response = $this->actingAs($coach)->get(route('themes.show', $theme));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Themes/Show')
        ->has('courses.data', 1) // Only approved course
        ->where('courses.data.0.title', 'Approved Course')
        ->where('courses.data.0.id', $approvedCourse->id)
    );
});

test('theme show page loads courses with coach information', function () {
    $coach = User::factory()->create(['role' => 'coach', 'name' => 'John Coach']);
    $theme = Theme::factory()->create();

    \App\Models\Course::factory()->create([
        'theme_id' => $theme->id,
        'coach_id' => $coach->id,
        'is_approved' => true,
        'title' => 'Test Course',
    ]);

    $response = $this->actingAs($coach)->get(route('themes.show', $theme));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Themes/Show')
        ->has('courses.data', 1)
        ->where('courses.data.0.coach.name', 'John Coach')
        ->where('courses.data.0.coach.id', $coach->id)
    );
});
