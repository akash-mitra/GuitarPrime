<?php

use App\Models\Attachment;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('private');
});

describe('Attachment CRUD Operations', function () {
    test('coach can upload attachment to own module', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);

        $file = UploadedFile::fake()->create('test.pdf', 1, 'application/pdf');

        $response = $this->actingAs($coach)->post(route('attachments.store'), [
            'name' => 'Test Document',
            'file' => $file,
            'module_id' => $module->id,
        ]);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'message',
            'attachment' => ['id', 'name', 'filename', 'size', 'mime_type'],
        ]);

        $attachment = Attachment::latest()->first();
        expect($attachment->module_id)->toBe($module->id);
        expect($attachment->name)->toBe('Test Document');
        expect($attachment->mime_type)->toBe('application/pdf');

        Storage::disk('private')->assertExists('attachments/'.Attachment::latest()->first()->filename);
    });

    test('admin can upload attachment to any module', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);

        $file = UploadedFile::fake()->create('admin-test.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($admin)->post(route('attachments.store'), [
            'name' => 'Admin Document',
            'file' => $file,
            'module_id' => $module->id,
        ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('attachments', [
            'module_id' => $module->id,
            'name' => 'Admin Document',
        ]);
    });

    test('coach cannot upload attachment to another coaches module', function () {
        $coach1 = User::factory()->create(['role' => 'coach']);
        $coach2 = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach2->id]);

        $file = UploadedFile::fake()->create('test.pdf', 1024);

        $response = $this->actingAs($coach1)->post(route('attachments.store'), [
            'name' => 'Test Document',
            'file' => $file,
            'module_id' => $module->id,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('attachments', [
            'module_id' => $module->id,
            'name' => 'Test Document',
        ]);
    });

    test('student cannot upload attachments', function () {
        $student = User::factory()->create(['role' => 'student']);
        $module = Module::factory()->create();

        $file = UploadedFile::fake()->create('test.pdf', 1024);

        $response = $this->actingAs($student)->post(route('attachments.store'), [
            'name' => 'Test Document',
            'file' => $file,
            'module_id' => $module->id,
        ]);

        $response->assertForbidden();
    });

    test('coach can update attachment name for own module', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        $attachment = Attachment::factory()->create(['module_id' => $module->id]);

        $response = $this->actingAs($coach)->put(route('attachments.update', $attachment->id), [
            'name' => 'Updated Name',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('attachments', [
            'id' => $attachment->id,
            'name' => 'Updated Name',
        ]);
    });

    test('coach cannot update attachment name for another coaches module', function () {
        $coach1 = User::factory()->create(['role' => 'coach']);
        $coach2 = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach2->id]);
        $attachment = Attachment::factory()->create(['module_id' => $module->id]);

        $response = $this->actingAs($coach1)->put(route('attachments.update', $attachment->id), [
            'name' => 'Updated Name',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('attachments', [
            'id' => $attachment->id,
            'name' => 'Updated Name',
        ]);
    });

    test('coach can delete attachment from own module', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        $attachment = Attachment::factory()->create(['module_id' => $module->id]);

        // Create a fake file in storage
        Storage::disk('private')->put($attachment->path, 'fake file content');

        $response = $this->actingAs($coach)->delete(route('attachments.destroy', $attachment->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('attachments', ['id' => $attachment->id]);
        Storage::disk('private')->assertMissing($attachment->path);
    });

    test('coach cannot delete attachment from another coaches module', function () {
        $coach1 = User::factory()->create(['role' => 'coach']);
        $coach2 = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach2->id]);
        $attachment = Attachment::factory()->create(['module_id' => $module->id]);

        $response = $this->actingAs($coach1)->delete(route('attachments.destroy', $attachment->id));

        $response->assertForbidden();
        $this->assertDatabaseHas('attachments', ['id' => $attachment->id]);
    });

    test('admin can delete any attachment', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        $attachment = Attachment::factory()->create(['module_id' => $module->id]);

        // Create a fake file in storage
        Storage::disk('private')->put($attachment->path, 'fake file content');

        $response = $this->actingAs($admin)->delete(route('attachments.destroy', $attachment->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('attachments', ['id' => $attachment->id]);
        Storage::disk('private')->assertMissing($attachment->path);
    });
});

describe('Attachment Upload Validation', function () {
    test('attachment upload requires valid file', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);

        $response = $this->actingAs($coach)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('attachments.store'), [
                'name' => 'Test Document',
                'module_id' => $module->id,
                // Missing file
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['file']);
    });

    test('attachment upload requires name', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);

        $file = UploadedFile::fake()->create('test.pdf', 1024);

        $response = $this->actingAs($coach)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('attachments.store'), [
                'file' => $file,
                'module_id' => $module->id,
                // Missing name
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name']);
    });

    test('attachment upload requires valid module', function () {
        $coach = User::factory()->create(['role' => 'coach']);

        $file = UploadedFile::fake()->create('test.pdf', 1024);

        $response = $this->actingAs($coach)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('attachments.store'), [
                'name' => 'Test Document',
                'file' => $file,
                'module_id' => 'invalid-module-id',
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['module_id']);
    });

    test('attachment upload enforces file size limit', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);

        // Create a file larger than 10MB (10240KB)
        $file = UploadedFile::fake()->create('large.pdf', 15000, 'application/pdf');

        $response = $this->actingAs($coach)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('attachments.store'), [
                'name' => 'Large Document',
                'file' => $file,
                'module_id' => $module->id,
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['file']);
    });

    test('attachment name update requires valid name', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        $attachment = Attachment::factory()->create(['module_id' => $module->id]);

        $response = $this->actingAs($coach)
            ->withHeaders(['Accept' => 'application/json'])
            ->put(route('attachments.update', $attachment->id), [
                'name' => '', // Empty name
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name']);
    });

    test('attachment name cannot exceed 255 characters', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        $attachment = Attachment::factory()->create(['module_id' => $module->id]);

        $response = $this->actingAs($coach)
            ->withHeaders(['Accept' => 'application/json'])
            ->put(route('attachments.update', $attachment->id), [
                'name' => str_repeat('a', 256), // 256 characters
            ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name']);
    });
});

describe('Attachment File Storage', function () {
    test('uploaded files are stored with unique filenames', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);

        $file1 = UploadedFile::fake()->create('test.pdf', 1024, 'application/pdf');
        $file2 = UploadedFile::fake()->create('test.pdf', 1024, 'application/pdf');

        $this->actingAs($coach)->post(route('attachments.store'), [
            'name' => 'Document 1',
            'file' => $file1,
            'module_id' => $module->id,
        ]);

        $this->actingAs($coach)->post(route('attachments.store'), [
            'name' => 'Document 2',
            'file' => $file2,
            'module_id' => $module->id,
        ]);

        $attachments = Attachment::where('module_id', $module->id)->get();

        expect($attachments)->toHaveCount(2);
        expect($attachments->first()->filename)->not->toBe($attachments->last()->filename);
    });

    test('file deletion removes file from storage', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        $attachment = Attachment::factory()->create(['module_id' => $module->id]);

        // Create a fake file in storage
        Storage::disk('private')->put($attachment->path, 'fake file content');
        Storage::disk('private')->assertExists($attachment->path);

        $this->actingAs($coach)->delete(route('attachments.destroy', $attachment->id));

        Storage::disk('private')->assertMissing($attachment->path);
    });

    test('file deletion handles missing files gracefully', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        $attachment = Attachment::factory()->create(['module_id' => $module->id]);

        // Don't create the file in storage
        Storage::disk('private')->assertMissing($attachment->path);

        $response = $this->actingAs($coach)->delete(route('attachments.destroy', $attachment->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('attachments', ['id' => $attachment->id]);
    });
});
