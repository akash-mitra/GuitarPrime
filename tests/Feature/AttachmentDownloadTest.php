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

describe('Attachment Download Access Control', function () {
    test('admin can download any attachment', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        $attachment = Attachment::factory()->create(['module_id' => $module->id]);

        // Create a fake file in storage
        Storage::disk('private')->put($attachment->path, 'test file content');

        $response = $this->actingAs($admin)->get(route('attachments.download', $attachment->id));

        $response->assertSuccessful();
        $response->assertDownload();
    });

    test('coach can download attachment from own module', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        $attachment = Attachment::factory()->create(['module_id' => $module->id]);

        // Create a fake file in storage
        Storage::disk('private')->put($attachment->path, 'test file content');

        $response = $this->actingAs($coach)->get(route('attachments.download', $attachment->id));

        $response->assertSuccessful();
        $response->assertDownload();
    });

    test('coach can download attachment from free module by another coach', function () {
        $coach1 = User::factory()->create(['role' => 'coach']);
        $coach2 = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach2->id, 'is_free' => true]);
        $attachment = Attachment::factory()->create(['module_id' => $module->id]);

        // Create a fake file in storage
        Storage::disk('private')->put($attachment->path, 'test file content');

        $response = $this->actingAs($coach1)->get(route('attachments.download', $attachment->id));

        $response->assertSuccessful();
        $response->assertDownload();
    });

    test('coach cannot download attachment from paid module by another coach without purchase', function () {
        $coach1 = User::factory()->create(['role' => 'coach']);
        $coach2 = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach2->id, 'is_free' => false]);
        $attachment = Attachment::factory()->create(['module_id' => $module->id]);

        // Create a fake file in storage
        Storage::disk('private')->put($attachment->path, 'test file content');

        $response = $this->actingAs($coach1)->get(route('attachments.download', $attachment->id));

        $response->assertForbidden();
    });

    test('student can download attachment from free module', function () {
        $student = User::factory()->create(['role' => 'student']);
        $module = Module::factory()->create(['is_free' => true]);
        $attachment = Attachment::factory()->create(['module_id' => $module->id]);

        // Create a fake file in storage
        Storage::disk('private')->put($attachment->path, 'test file content');

        $response = $this->actingAs($student)->get(route('attachments.download', $attachment->id));

        $response->assertSuccessful();
        $response->assertDownload();
    });

    test('student cannot download attachment from paid module without purchase', function () {
        $student = User::factory()->create(['role' => 'student']);
        $module = Module::factory()->create(['is_free' => false]);
        $attachment = Attachment::factory()->create(['module_id' => $module->id]);

        // Create a fake file in storage
        Storage::disk('private')->put($attachment->path, 'test file content');

        $response = $this->actingAs($student)->get(route('attachments.download', $attachment->id));

        $response->assertForbidden();
    });

    test('student can download attachment from paid module after course purchase', function () {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_free' => false, 'is_approved' => true]);
        $module = Module::factory()->create(['is_free' => false]);
        $attachment = Attachment::factory()->create(['module_id' => $module->id]);

        // Associate module with course
        $course->modules()->attach($module->id, ['order' => 1]);

        // Create purchase for the course
        Purchase::factory()->create([
            'user_id' => $student->id,
            'purchasable_type' => Course::class,
            'purchasable_id' => $course->id,
            'status' => 'completed',
        ]);

        // Create a fake file in storage
        Storage::disk('private')->put($attachment->path, 'test file content');

        $response = $this->actingAs($student)->get(route('attachments.download', $attachment->id));

        $response->assertSuccessful();
        $response->assertDownload();
    });

    test('student can download attachment from paid module after module purchase', function () {
        $student = User::factory()->create(['role' => 'student']);
        $module = Module::factory()->create(['is_free' => false]);
        $attachment = Attachment::factory()->create(['module_id' => $module->id]);

        // Create purchase for the module
        Purchase::factory()->create([
            'user_id' => $student->id,
            'purchasable_type' => Module::class,
            'purchasable_id' => $module->id,
            'status' => 'completed',
        ]);

        // Create a fake file in storage
        Storage::disk('private')->put($attachment->path, 'test file content');

        $response = $this->actingAs($student)->get(route('attachments.download', $attachment->id));

        $response->assertSuccessful();
        $response->assertDownload();
    });

    test('unauthenticated user cannot download attachments', function () {
        $module = Module::factory()->create(['is_free' => true]);
        $attachment = Attachment::factory()->create(['module_id' => $module->id]);

        $response = $this->get(route('attachments.download', $attachment->id));

        $response->assertRedirect(route('login'));
    });
});

describe('Attachment Download File Handling', function () {
    test('download returns 404 for missing file', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $module = Module::factory()->create();
        $attachment = Attachment::factory()->create(['module_id' => $module->id]);

        // Don't create the file in storage
        Storage::disk('private')->assertMissing($attachment->path);

        $response = $this->actingAs($admin)->get(route('attachments.download', $attachment->id));

        $response->assertNotFound();
    });

    test('download returns file with correct name and extension', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $module = Module::factory()->create();
        $attachment = Attachment::factory()->create([
            'module_id' => $module->id,
            'name' => 'My Document',
            'filename' => 'uuid-filename.pdf',
        ]);

        // Create a fake file in storage
        Storage::disk('private')->put($attachment->path, 'test file content');

        $response = $this->actingAs($admin)->get(route('attachments.download', $attachment->id));

        $response->assertSuccessful();
        $response->assertDownload('My Document.pdf');
    });

    test('download preserves original file extension', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $module = Module::factory()->create();
        $attachment = Attachment::factory()->create([
            'module_id' => $module->id,
            'name' => 'Audio File',
            'filename' => 'uuid-filename.mp3',
        ]);

        // Create a fake file in storage
        Storage::disk('private')->put($attachment->path, 'test audio content');

        $response = $this->actingAs($admin)->get(route('attachments.download', $attachment->id));

        $response->assertSuccessful();
        $response->assertDownload('Audio File.mp3');
    });

    test('download handles files without extensions', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $module = Module::factory()->create();
        $attachment = Attachment::factory()->create([
            'module_id' => $module->id,
            'name' => 'Text File',
            'filename' => 'uuid-filename',
        ]);

        // Create a fake file in storage
        Storage::disk('private')->put($attachment->path, 'test content');

        $response = $this->actingAs($admin)->get(route('attachments.download', $attachment->id));

        $response->assertSuccessful();
        $response->assertDownload('Text File.');
    });
});

describe('Attachment Download Integration', function () {
    test('download works with complex course-module relationships', function () {
        $student = User::factory()->create(['role' => 'student']);
        $course1 = Course::factory()->create(['is_free' => false, 'is_approved' => true]);
        $course2 = Course::factory()->create(['is_free' => false, 'is_approved' => true]);
        $module = Module::factory()->create(['is_free' => false]);
        $attachment = Attachment::factory()->create(['module_id' => $module->id]);

        // Associate module with both courses
        $course1->modules()->attach($module->id, ['order' => 1]);
        $course2->modules()->attach($module->id, ['order' => 2]);

        // Purchase only one course
        Purchase::factory()->create([
            'user_id' => $student->id,
            'purchasable_type' => Course::class,
            'purchasable_id' => $course1->id,
            'status' => 'completed',
        ]);

        // Create a fake file in storage
        Storage::disk('private')->put($attachment->path, 'test file content');

        $response = $this->actingAs($student)->get(route('attachments.download', $attachment->id));

        $response->assertSuccessful();
        $response->assertDownload();
    });

    test('download access is denied for incomplete purchases', function () {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_free' => false, 'is_approved' => true]);
        $module = Module::factory()->create(['is_free' => false]);
        $attachment = Attachment::factory()->create(['module_id' => $module->id]);

        // Associate module with course
        $course->modules()->attach($module->id, ['order' => 1]);

        // Create incomplete purchase
        Purchase::factory()->create([
            'user_id' => $student->id,
            'purchasable_type' => Course::class,
            'purchasable_id' => $course->id,
            'status' => 'pending',
        ]);

        // Create a fake file in storage
        Storage::disk('private')->put($attachment->path, 'test file content');

        $response = $this->actingAs($student)->get(route('attachments.download', $attachment->id));

        $response->assertForbidden();
    });

    test('download access respects module free status over course paid status', function () {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_free' => false, 'is_approved' => true]);
        $module = Module::factory()->create(['is_free' => true]); // Free module in paid course
        $attachment = Attachment::factory()->create(['module_id' => $module->id]);

        // Associate module with course
        $course->modules()->attach($module->id, ['order' => 1]);

        // No purchase needed for free module

        // Create a fake file in storage
        Storage::disk('private')->put($attachment->path, 'test file content');

        $response = $this->actingAs($student)->get(route('attachments.download', $attachment->id));

        $response->assertSuccessful();
        $response->assertDownload();
    });
});
