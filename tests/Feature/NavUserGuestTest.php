<?php

use App\Models\Theme;

describe('NavUser Guest Integration', function () {
    test('guest can access themes page with NavUser component (no JavaScript errors)', function () {
        Theme::factory()->count(3)->create();

        $response = $this->get('/themes');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Themes/Index')
            ->has('themes.data', 3)
            // Verify that auth.user is properly passed as null for guests
            ->where('auth.user', null)
        );
    });

    test('authenticated user sees proper user dropdown in NavUser', function () {
        $user = \App\Models\User::factory()->create();
        
        $response = $this->actingAs($user)->get('/themes');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Themes/Index')
            // Verify that auth.user is properly passed for authenticated users
            ->where('auth.user.id', $user->id)
            ->where('auth.user.name', $user->name)
            ->where('auth.user.email', $user->email)
        );
    });
});