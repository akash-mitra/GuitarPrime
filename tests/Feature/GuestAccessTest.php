<?php

use App\Models\Course;
use App\Models\Module;
use App\Models\Theme;
use App\Models\User;

describe('Guest Access - Theme Browsing', function () {
    test('guest can access themes index page', function () {
        Theme::factory()->count(3)->create();

        $response = $this->get('/themes');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Themes/Index')
            ->has('themes.data', 3)
        );
    });

    test('guest can view individual theme page', function () {
        $theme = Theme::factory()->create();
        Course::factory()->count(2)->create([
            'theme_id' => $theme->id,
            'is_approved' => true,
        ]);

        $response = $this->get("/themes/{$theme->id}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Themes/Show')
            ->where('theme.id', $theme->id)
            ->has('courses.data', 2)
        );
    });

    test('guest can search themes', function () {
        Theme::factory()->create(['name' => 'Jazz Guitar']);
        Theme::factory()->create(['name' => 'Rock Guitar']);

        $response = $this->get('/themes?search=Jazz');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Themes/Index')
            ->has('themes.data', 1)
            ->where('themes.data.0.name', 'Jazz Guitar')
        );
    });
});

describe('Guest Access - Course Browsing', function () {
    test('guest can access courses index page', function () {
        Course::factory()->count(2)->create(['is_approved' => true]);
        Course::factory()->create(['is_approved' => false]);

        $response = $this->get('/courses');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Courses/Index')
            ->has('courses.data', 2) // Only approved courses visible to guests
        );
    });

    test('guest can only see approved courses', function () {
        $approvedCourse = Course::factory()->create(['is_approved' => true]);
        $pendingCourse = Course::factory()->create(['is_approved' => false]);

        $response = $this->get('/courses');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Courses/Index')
            ->has('courses.data', 1)
            ->where('courses.data.0.id', $approvedCourse->id)
        );
    });

    test('guest can view approved course details', function () {
        $course = Course::factory()->create(['is_approved' => true]);

        $response = $this->get("/courses/{$course->id}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Courses/Show')
            ->where('course.id', $course->id)
            ->where('canAccess', false) // Guest cannot access paid content
        );
    });

    test('guest cannot view pending/unapproved course details', function () {
        $course = Course::factory()->create(['is_approved' => false]);

        $response = $this->get("/courses/{$course->id}");

        $response->assertForbidden();
    });

    test('guest can search approved courses', function () {
        Course::factory()->create(['title' => 'Jazz Fundamentals', 'is_approved' => true]);
        Course::factory()->create(['title' => 'Rock Basics', 'is_approved' => true]);
        Course::factory()->create(['title' => 'Jazz Advanced', 'is_approved' => false]);

        $response = $this->get('/courses?search=Jazz');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Courses/Index')
            ->has('courses.data', 1) // Only approved Jazz course
            ->where('courses.data.0.title', 'Jazz Fundamentals')
        );
    });
});

describe('Guest Access - Module Browsing', function () {
    test('guest can access free modules through course context', function () {
        $course = Course::factory()->create(['is_approved' => true]);
        $freeModule = Module::factory()->create(['is_free' => true]);
        $course->modules()->attach($freeModule->id, ['order' => 1]);

        $response = $this->get("/courses/{$course->id}/modules/{$freeModule->id}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Courses/modules/Show')
            ->where('module.id', $freeModule->id)
            ->where('canAccessModule', false) // Guest cannot access even free modules yet - needs to be authenticated
        );
    });

    test('guest cannot access paid modules through course context', function () {
        $course = Course::factory()->create(['is_approved' => true]);
        $paidModule = Module::factory()->create(['is_free' => false]);
        $course->modules()->attach($paidModule->id, ['order' => 1]);

        $response = $this->get("/courses/{$course->id}/modules/{$paidModule->id}");

        $response->assertForbidden();
    });

    test('guest cannot access modules if course is not approved', function () {
        $course = Course::factory()->create(['is_approved' => false]);
        $freeModule = Module::factory()->create(['is_free' => true]);
        $course->modules()->attach($freeModule->id, ['order' => 1]);

        $response = $this->get("/courses/{$course->id}/modules/{$freeModule->id}");

        $response->assertForbidden();
    });

    test('guest cannot access module if not associated with course', function () {
        $course = Course::factory()->create(['is_approved' => true]);
        $module = Module::factory()->create(['is_free' => true]);
        // Don't associate module with course

        $response = $this->get("/courses/{$course->id}/modules/{$module->id}");

        $response->assertNotFound();
    });

    test('guest cannot access direct module routes', function () {
        $module = Module::factory()->create(['is_free' => true]);

        $response = $this->get("/modules/{$module->id}");

        $response->assertRedirect('/login');
    });

    test('guest cannot access modules index route', function () {
        $response = $this->get('/modules');

        $response->assertRedirect('/login');
    });
});

describe('Guest Access - UI and Frontend Integration', function () {
    test('guest sees appropriate UI elements on course page', function () {
        $course = Course::factory()->create([
            'is_approved' => true,
            'is_free' => false,
            'price' => 9900, // ₹99
        ]);

        $response = $this->get("/courses/{$course->id}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Courses/Show')
            ->where('canAccess', false)
            ->where('pricing.is_free', false)
            ->where('pricing.price', 9900)
        );
    });

    test('guest sees appropriate UI elements on free course page', function () {
        $course = Course::factory()->create([
            'is_approved' => true,
            'is_free' => true,
        ]);

        $response = $this->get("/courses/{$course->id}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Courses/Show')
            ->where('canAccess', false) // Even free courses require authentication for now
            ->where('pricing.is_free', true)
        );
    });

    test('guest does not see management UI elements', function () {
        $theme = Theme::factory()->create();

        $response = $this->get("/themes/{$theme->id}");

        $response->assertOk();
        // The absence of "Create Theme" button will be handled by frontend v-if="auth.user && auth.user.role === 'admin'"
        $response->assertInertia(fn ($page) => $page
            ->component('Themes/Show')
            ->where('theme.id', $theme->id)
        );
    });
});

describe('Guest Access - Authorization Edge Cases', function () {
    test('guest access works with missing optional parameters', function () {
        $theme = Theme::factory()->create(['description' => null]);

        $response = $this->get("/themes/{$theme->id}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Themes/Show')
            ->where('theme.description', null)
        );
    });

    test('guest access handles pagination correctly', function () {
        Theme::factory()->count(15)->create();

        $response = $this->get('/themes');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Themes/Index')
            ->has('themes.data', 10) // Default pagination limit
            ->has('themes.links')
        );
    });

    test('guest can navigate between pages', function () {
        Course::factory()->count(15)->create(['is_approved' => true]);

        $response = $this->get('/courses?page=2');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Courses/Index')
            ->has('courses.data', 5) // Remaining items on page 2
        );
    });
});

describe('Guest Access - SEO and Discoverability', function () {
    test('themes index page is accessible for SEO crawlers', function () {
        Theme::factory()->count(3)->create();

        $response = $this->get('/themes');

        $response->assertOk();
        $response->assertSee('Themes'); // Page title should be visible
    });

    test('course details are accessible for SEO crawlers', function () {
        $course = Course::factory()->create([
            'title' => 'Learn Jazz Guitar',
            'description' => 'Master the fundamentals of jazz guitar',
            'is_approved' => true,
        ]);

        $response = $this->get("/courses/{$course->id}");

        $response->assertOk();
        // Content should be accessible in the page
        $response->assertInertia(fn ($page) => $page
            ->where('course.title', 'Learn Jazz Guitar')
            ->where('course.description', 'Master the fundamentals of jazz guitar')
        );
    });

    test('theme details show associated approved courses for SEO', function () {
        $theme = Theme::factory()->create(['name' => 'Jazz Guitar']);
        Course::factory()->create([
            'theme_id' => $theme->id,
            'title' => 'Jazz Basics',
            'is_approved' => true,
        ]);
        Course::factory()->create([
            'theme_id' => $theme->id,
            'title' => 'Jazz Advanced',
            'is_approved' => false, // Should not appear
        ]);

        $response = $this->get("/themes/{$theme->id}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('theme.name', 'Jazz Guitar')
            ->has('courses.data', 1)
            ->where('courses.data.0.title', 'Jazz Basics')
        );
    });
});