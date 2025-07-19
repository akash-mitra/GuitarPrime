<?php

use App\Models\Attachment;
use App\Models\Course;
use App\Models\Module;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('private');
});

describe('Module-Attachment Integration', function () {
    test('module edit form loads existing attachments', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);

        // Create attachments for the module
        $attachment1 = Attachment::factory()->create(['module_id' => $module->id, 'name' => 'Document 1']);
        $attachment2 = Attachment::factory()->create(['module_id' => $module->id, 'name' => 'Document 2']);

        $response = $this->actingAs($coach)->get(route('modules.edit', $module->id));

        $response->assertSuccessful();

        // Check that attachments are passed to the frontend
        $response->assertInertia(fn ($assert) => $assert
            ->component('modules/Edit')
            ->has('module.attachments', 2)
            ->where('module.attachments.0.name', 'Document 1')
            ->where('module.attachments.1.name', 'Document 2')
        );
    });

    test('module show page displays attachments', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);

        $attachment1 = Attachment::factory()->create(['module_id' => $module->id, 'name' => 'Document 1']);
        $attachment2 = Attachment::factory()->create(['module_id' => $module->id, 'name' => 'Document 2']);

        $response = $this->actingAs($coach)->get(route('modules.show', $module->id));

        $response->assertSuccessful();

        $response->assertInertia(fn ($assert) => $assert
            ->component('modules/Show')
            ->has('module.attachments', 2)
            ->where('module.attachments.0.name', 'Document 1')
            ->where('module.attachments.1.name', 'Document 2')
        );
    });

    test('module creation stores module_id and redirects with success', function () {
        $coach = User::factory()->create(['role' => 'coach']);

        $response = $this->actingAs($coach)->post(route('modules.store'), [
            'title' => 'New Module',
            'description' => 'Test module description',
            'difficulty' => 'easy',
            'video_url' => 'https://vimeo.com/123456789',
        ]);

        $response->assertRedirect(route('modules.index'));
        $response->assertSessionHas('success', 'Module created successfully.');

        $module = Module::where('title', 'New Module')->first();
        $response->assertSessionHas('moduleId', $module->id);
    });

    test('module deletion cascades to attachments', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);

        $attachment1 = Attachment::factory()->create(['module_id' => $module->id]);
        $attachment2 = Attachment::factory()->create(['module_id' => $module->id]);

        // Create fake files in storage
        Storage::disk('private')->put($attachment1->path, 'content 1');
        Storage::disk('private')->put($attachment2->path, 'content 2');

        $response = $this->actingAs($coach)->delete(route('modules.destroy', $module->id));

        $response->assertRedirect(route('modules.index'));

        // Module should be hard deleted (no soft deletes on Module model)
        $this->assertDatabaseMissing('modules', ['id' => $module->id]);

        // Attachments should be cascade deleted due to foreign key constraint
        $this->assertDatabaseMissing('attachments', ['id' => $attachment1->id]);
        $this->assertDatabaseMissing('attachments', ['id' => $attachment2->id]);

        // Files should still exist in storage (only database records are deleted)
        Storage::disk('private')->assertExists($attachment1->path);
        Storage::disk('private')->assertExists($attachment2->path);
    });

    test('attachment belongs to correct module', function () {
        $module = Module::factory()->create();
        $attachment = Attachment::factory()->create(['module_id' => $module->id]);

        expect($attachment->module->id)->toBe($module->id);
        expect($attachment->module->title)->toBe($module->title);
    });

    test('module has many attachments relationship', function () {
        $module = Module::factory()->create();
        $attachment1 = Attachment::factory()->create(['module_id' => $module->id]);
        $attachment2 = Attachment::factory()->create(['module_id' => $module->id]);

        $module->load('attachments');

        expect($module->attachments)->toHaveCount(2);
        expect($module->attachments->pluck('id'))->toContain($attachment1->id);
        expect($module->attachments->pluck('id'))->toContain($attachment2->id);
    });

    test('module attachment count is accurate', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);

        // Initially no attachments
        expect($module->attachments()->count())->toBe(0);

        // Add attachments
        Attachment::factory()->create(['module_id' => $module->id]);
        Attachment::factory()->create(['module_id' => $module->id]);

        $module->refresh();
        expect($module->attachments()->count())->toBe(2);

        // Remove one attachment
        $module->attachments()->first()->delete();

        $module->refresh();
        expect($module->attachments()->count())->toBe(1);
    });
});

describe('Course-Module-Attachment Integration', function () {
    test('course show page displays module attachments correctly', function () {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_free' => true, 'is_approved' => true]);
        $module = Module::factory()->create(['is_free' => true]);
        $attachment = Attachment::factory()->create(['module_id' => $module->id, 'name' => 'Course Attachment']);

        // Associate module with course
        $course->modules()->attach($module->id, ['order' => 1]);

        $response = $this->actingAs($student)->get(route('courses.modules.show', [$course->id, $module->id]));

        $response->assertSuccessful();

        $response->assertInertia(fn ($assert) => $assert
            ->component('Courses/modules/Show')
            ->has('module.attachments', 1)
            ->where('module.attachments.0.name', 'Course Attachment')
        );
    });

    test('attachment access control works in course context', function () {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_free' => false, 'is_approved' => true]);
        $module = Module::factory()->create(['is_free' => false]);
        $attachment = Attachment::factory()->create(['module_id' => $module->id]);

        // Associate module with course
        $course->modules()->attach($module->id, ['order' => 1]);

        // Create fake file
        Storage::disk('private')->put($attachment->path, 'restricted content');

        // Student cannot access attachment without purchase
        $response = $this->actingAs($student)->get(route('attachments.download', $attachment->id));
        $response->assertForbidden();

        // After purchasing course, student can access attachment
        Purchase::factory()->create([
            'user_id' => $student->id,
            'purchasable_type' => Course::class,
            'purchasable_id' => $course->id,
            'status' => 'completed',
            'amount' => 1000,
        ]);

        $response = $this->actingAs($student)->get(route('attachments.download', $attachment->id));
        $response->assertSuccessful();
    });

    test('module in multiple courses allows attachment access via any purchased course', function () {
        $student = User::factory()->create(['role' => 'student']);
        $course1 = Course::factory()->create(['is_free' => false, 'is_approved' => true]);
        $course2 = Course::factory()->create(['is_free' => false, 'is_approved' => true]);
        $module = Module::factory()->create(['is_free' => false]);
        $attachment = Attachment::factory()->create(['module_id' => $module->id]);

        // Associate module with both courses
        $course1->modules()->attach($module->id, ['order' => 1]);
        $course2->modules()->attach($module->id, ['order' => 1]);

        // Create fake file
        Storage::disk('private')->put($attachment->path, 'shared content');

        // Purchase only course1
        Purchase::factory()->create([
            'user_id' => $student->id,
            'purchasable_type' => Course::class,
            'purchasable_id' => $course1->id,
            'status' => 'completed',
            'amount' => 1000,
        ]);

        // Student can access attachment through purchased course
        $response = $this->actingAs($student)->get(route('attachments.download', $attachment->id));
        $response->assertSuccessful();

        // Student can also access module through both course contexts
        $response1 = $this->actingAs($student)->get(route('courses.modules.show', [$course1->id, $module->id]));
        $response1->assertSuccessful();

        $response2 = $this->actingAs($student)->get(route('courses.modules.show', [$course2->id, $module->id]));
        $response2->assertSuccessful();
    });

    test('free module attachments are accessible in paid courses', function () {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_free' => false, 'is_approved' => true]);
        $module = Module::factory()->create(['is_free' => true]); // Free module
        $attachment = Attachment::factory()->create(['module_id' => $module->id]);

        // Associate module with paid course
        $course->modules()->attach($module->id, ['order' => 1]);

        // Create fake file
        Storage::disk('private')->put($attachment->path, 'free content');

        // Student can access free module attachment without purchasing course
        $response = $this->actingAs($student)->get(route('attachments.download', $attachment->id));
        $response->assertSuccessful();
    });
});

describe('Attachment Frontend Integration', function () {
    test('attachment upload component receives correct props in edit form', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);

        $attachment1 = Attachment::factory()->create([
            'module_id' => $module->id,
            'name' => 'Document 1',
            'size' => 1024,
            'mime_type' => 'application/pdf',
        ]);

        $attachment2 = Attachment::factory()->create([
            'module_id' => $module->id,
            'name' => 'Document 2',
            'size' => 2048,
            'mime_type' => 'image/jpeg',
        ]);

        $response = $this->actingAs($coach)->get(route('modules.edit', $module->id));

        $response->assertSuccessful();

        $response->assertInertia(fn ($assert) => $assert
            ->component('modules/Edit')
            ->where('module.id', $module->id)
            ->has('module.attachments', 2)
            ->where('module.attachments.0.name', 'Document 1')
            ->where('module.attachments.0.size', 1024)
            ->where('module.attachments.0.mime_type', 'application/pdf')
            ->where('module.attachments.1.name', 'Document 2')
            ->where('module.attachments.1.size', 2048)
            ->where('module.attachments.1.mime_type', 'image/jpeg')
        );
    });

    test('module create form does not include attachment data', function () {
        $coach = User::factory()->create(['role' => 'coach']);

        $response = $this->actingAs($coach)->get(route('modules.create'));

        $response->assertSuccessful();

        $response->assertInertia(fn ($assert) => $assert
            ->component('modules/Create')
            ->missing('module.attachments')
        );
    });

    test('attachment download route returns correct filename', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $module = Module::factory()->create();

        $attachment = Attachment::factory()->create([
            'module_id' => $module->id,
            'name' => 'My Important Document',
            'filename' => 'uuid-12345.pdf',
        ]);

        // Create fake file
        Storage::disk('private')->put($attachment->path, 'document content');

        $response = $this->actingAs($admin)->get(route('attachments.download', $attachment->id));

        $response->assertSuccessful();
        $response->assertDownload('My Important Document.pdf');
    });

    test('attachment list shows correct file information', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['is_free' => true, 'coach_id' => $coach->id]);

        $attachment = Attachment::factory()->create([
            'module_id' => $module->id,
            'name' => 'Guitar Lesson PDF',
            'size' => 2048576, // 2MB
            'mime_type' => 'application/pdf',
        ]);

        $response = $this->actingAs($coach)->get(route('modules.show', $module->id));

        $response->assertSuccessful();

        $response->assertInertia(fn ($assert) => $assert
            ->component('modules/Show')
            ->has('module.attachments', 1)
            ->where('module.attachments.0.name', 'Guitar Lesson PDF')
            ->where('module.attachments.0.size', 2048576)
            ->where('module.attachments.0.mime_type', 'application/pdf')
        );
    });
});
