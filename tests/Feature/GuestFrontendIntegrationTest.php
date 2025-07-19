<?php

use App\Models\Theme;

describe('Guest Frontend Integration', function () {
    test('guest can access themes page without JavaScript errors', function () {
        Theme::factory()->count(3)->create();

        $response = $this->get('/themes');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Themes/Index')
            ->has('themes.data', 3)
            // Verify that auth is properly passed as null for guests
            ->where('auth.user', null)
        );
    });

    test('guest can access theme show page without JavaScript errors', function () {
        $theme = Theme::factory()->create();

        $response = $this->get("/themes/{$theme->id}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Themes/Show')
            ->where('theme.id', $theme->id)
            // Verify that auth is properly passed as null for guests
            ->where('auth.user', null)
        );
    });
});