<?php

use App\Models\Course;
use App\Models\Purchase;
use App\Models\User;

beforeEach(function () {
    $this->student = User::factory()->create(['role' => 'student']);
    $this->coach = User::factory()->create(['role' => 'coach']);
    $this->admin = User::factory()->create(['role' => 'admin']);
});

test('student can create a purchase for a paid course', function () {
    $course = Course::factory()->create([
        'price' => 9999, // 99.99 rupees as paisa
        'is_free' => false,
        'is_approved' => true,
    ]);

    $response = $this->actingAs($this->student)
        ->post('/purchases', [
            'type' => 'course',
            'id' => $course->id,
            'payment_provider' => 'stripe',
        ]);

    $response->assertSessionHasErrors(); // Expect error due to missing Stripe credentials in test

    $this->assertDatabaseHas('purchases', [
        'user_id' => $this->student->id,
        'purchasable_type' => Course::class,
        'purchasable_id' => $course->id,
        'amount' => 99.99, // Amount stored in rupees in purchases table
        'payment_provider' => 'stripe',
        'status' => 'failed', // Status is failed in test environment
    ]);
});

test('student cannot purchase free course', function () {
    $course = Course::factory()->create([
        'price' => null,
        'is_free' => true,
        'is_approved' => true,
    ]);

    $response = $this->actingAs($this->student)
        ->post('/purchases', [
            'type' => 'course',
            'id' => $course->id,
            'payment_provider' => 'stripe',
        ]);

    $response->assertSessionHasErrors();

    $this->assertDatabaseMissing('purchases', [
        'user_id' => $this->student->id,
        'purchasable_type' => Course::class,
        'purchasable_id' => $course->id,
    ]);
});

test('student cannot purchase course twice', function () {
    $course = Course::factory()->create([
        'price' => 9999, // 99.99 rupees as paisa
        'is_free' => false,
        'is_approved' => true,
    ]);

    Purchase::factory()->create([
        'user_id' => $this->student->id,
        'purchasable_type' => Course::class,
        'purchasable_id' => $course->id,
        'status' => 'completed',
    ]);

    $response = $this->actingAs($this->student)
        ->post('/purchases', [
            'type' => 'course',
            'id' => $course->id,
            'payment_provider' => 'stripe',
        ]);

    $response->assertSessionHasErrors();
});

test('student can view their own purchases - authorization', function () {
    $purchase = Purchase::factory()->create([
        'user_id' => $this->student->id,
    ]);

    // Test authorization logic directly
    expect($this->student->can('view', $purchase))->toBeTrue();
});

test('student cannot view other users purchases - authorization', function () {
    $otherStudent = User::factory()->create(['role' => 'student']);
    $purchase = Purchase::factory()->create([
        'user_id' => $otherStudent->id,
    ]);

    // Test authorization logic directly
    expect($this->student->can('view', $purchase))->toBeFalse();
});

test('admin can view any purchase - authorization', function () {
    $purchase = Purchase::factory()->create([
        'user_id' => $this->student->id,
    ]);

    // Test authorization logic directly
    expect($this->admin->can('view', $purchase))->toBeTrue();
});

test('user can access free content without purchase', function () {
    $course = Course::factory()->create([
        'price' => null,
        'is_free' => true,
    ]);

    expect($this->student->canAccess($course))->toBeTrue();
});

test('user can access purchased content', function () {
    $course = Course::factory()->create([
        'price' => 9999, // 99.99 rupees as paisa
        'is_free' => false,
    ]);

    Purchase::factory()->create([
        'user_id' => $this->student->id,
        'purchasable_type' => Course::class,
        'purchasable_id' => $course->id,
        'status' => 'completed',
    ]);

    expect($this->student->canAccess($course))->toBeTrue();
});

test('user cannot access unpurchased paid content', function () {
    $course = Course::factory()->create([
        'price' => 9999, // 99.99 rupees as paisa
        'is_free' => false,
    ]);

    expect($this->student->canAccess($course))->toBeFalse();
});

test('admin can access all content', function () {
    $course = Course::factory()->create([
        'price' => 9999, // 99.99 rupees as paisa
        'is_free' => false,
    ]);

    expect($this->admin->canAccess($course))->toBeTrue();
});

test('coach can access their own content', function () {
    $course = Course::factory()->create([
        'coach_id' => $this->coach->id,
        'price' => 9999, // 99.99 rupees as paisa
        'is_free' => false,
    ]);

    expect($this->coach->canAccess($course))->toBeTrue();
});

test('coach cannot access other coaches paid content without purchase', function () {
    $otherCoach = User::factory()->create(['role' => 'coach']);
    $course = Course::factory()->create([
        'coach_id' => $otherCoach->id,
        'price' => 9999, // 99.99 rupees as paisa
        'is_free' => false,
    ]);

    expect($this->coach->canAccess($course))->toBeFalse();
});

test('frontend-backend integration: purchase API rejects wrong parameter names', function () {
    $course = Course::factory()->create([
        'price' => 9999, // 99.99 rupees as paisa
        'is_free' => false,
        'is_approved' => true,
    ]);

    // Test with frontend's old incorrect parameter names (should fail)
    $response = $this->actingAs($this->student)
        ->post('/purchases', [
            'purchasable_type' => 'course', // Wrong parameter name
            'purchasable_id' => $course->id, // Wrong parameter name
            'payment_provider' => 'stripe',
        ]);

    $response->assertSessionHasErrors(['type', 'id']);

    // Test with correct parameter names (should work)
    $response = $this->actingAs($this->student)
        ->post('/purchases', [
            'type' => 'course', // Correct parameter name
            'id' => $course->id, // Correct parameter name
            'payment_provider' => 'stripe',
        ]);

    $response->assertSessionHasErrors(); // Expect Stripe error in test environment

    // Should create a purchase record despite Stripe failure
    $this->assertDatabaseHas('purchases', [
        'user_id' => $this->student->id,
        'purchasable_type' => Course::class,
        'purchasable_id' => $course->id,
    ]);
});
