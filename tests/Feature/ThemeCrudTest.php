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

test('student cannot access themes index', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->get(route('themes.index'));

    $response->assertStatus(403);
});

test('coach can create theme', function () {
    $coach = User::factory()->create(['role' => 'coach']);

    $response = $this->actingAs($coach)->post(route('themes.store'), [
        'name' => 'Guitar Basics',
        'description' => 'Learn the fundamentals of guitar playing'
    ]);

    $response->assertRedirect(route('themes.index'));
    $this->assertDatabaseHas('themes', [
        'name' => 'Guitar Basics',
        'description' => 'Learn the fundamentals of guitar playing'
    ]);
});

test('student cannot create theme', function () {
    $student = User::factory()->create(['role' => 'student']);

    $response = $this->actingAs($student)->post(route('themes.store'), [
        'name' => 'Guitar Basics',
        'description' => 'Learn the fundamentals'
    ]);

    $response->assertStatus(403);
    $this->assertDatabaseMissing('themes', ['name' => 'Guitar Basics']);
});

test('coach can edit theme', function () {
    $coach = User::factory()->create(['role' => 'coach']);
    $theme = Theme::factory()->create(['name' => 'Original Name']);

    $response = $this->actingAs($coach)->put(route('themes.update', $theme), [
        'name' => 'Updated Name',
        'description' => 'Updated description'
    ]);

    $response->assertRedirect(route('themes.index'));
    $this->assertDatabaseHas('themes', [
        'id' => $theme->id,
        'name' => 'Updated Name',
        'description' => 'Updated description'
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
    $coach = User::factory()->create(['role' => 'coach']);

    $response = $this->actingAs($coach)->post(route('themes.store'), [
        'name' => '', // Required field empty
        'description' => str_repeat('a', 1001) // Too long
    ]);

    $response->assertSessionHasErrors(['name', 'description']);
});

test('theme name must be unique', function () {
    $coach = User::factory()->create(['role' => 'coach']);
    Theme::factory()->create(['name' => 'Existing Theme']);

    $response = $this->actingAs($coach)->post(route('themes.store'), [
        'name' => 'Existing Theme',
        'description' => 'Some description'
    ]);

    $response->assertSessionHasErrors(['name']);
});
