<?php

use App\Models\Attachment;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('private');
});

describe('Attachment Upload Validation', function () {
    test('supports various document file types', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        
        $fileTypes = [
            ['name' => 'document.pdf', 'mime' => 'application/pdf'],
            ['name' => 'document.doc', 'mime' => 'application/msword'],
            ['name' => 'document.docx', 'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            ['name' => 'document.txt', 'mime' => 'text/plain'],
        ];
        
        foreach ($fileTypes as $fileType) {
            $file = UploadedFile::fake()->create($fileType['name'], 1024, $fileType['mime']);
            
            $response = $this->actingAs($coach)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('attachments.store'), [
                'name' => 'Test ' . $fileType['name'],
                'file' => $file,
                'module_id' => $module->id,
            ]);
            
            $response->assertSuccessful();
            $this->assertDatabaseHas('attachments', [
                'module_id' => $module->id,
                'name' => 'Test ' . $fileType['name'],
                'mime_type' => $fileType['mime'],
            ]);
        }
    });

    test('supports various image file types', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        
        $imageTypes = [
            ['name' => 'image.jpg', 'mime' => 'image/jpeg'],
            ['name' => 'image.jpeg', 'mime' => 'image/jpeg'],
            ['name' => 'image.png', 'mime' => 'image/png'],
            ['name' => 'image.gif', 'mime' => 'image/gif'],
        ];
        
        foreach ($imageTypes as $imageType) {
            $file = UploadedFile::fake()->image($imageType['name'], 100, 100);
            
            $response = $this->actingAs($coach)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('attachments.store'), [
                'name' => 'Test ' . $imageType['name'],
                'file' => $file,
                'module_id' => $module->id,
            ]);
            
            $response->assertSuccessful();
            $this->assertDatabaseHas('attachments', [
                'module_id' => $module->id,
                'name' => 'Test ' . $imageType['name'],
            ]);
        }
    });

    test('supports audio file types', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        
        $audioTypes = [
            ['name' => 'audio.mp3', 'mime' => 'audio/mpeg'],
            ['name' => 'audio.wav', 'mime' => 'audio/wav'],
            ['name' => 'song.midi', 'mime' => 'audio/midi'],
            ['name' => 'song.mid', 'mime' => 'audio/midi'],
        ];
        
        foreach ($audioTypes as $audioType) {
            $file = UploadedFile::fake()->create($audioType['name'], 1024, $audioType['mime']);
            
            $response = $this->actingAs($coach)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('attachments.store'), [
                'name' => 'Test ' . $audioType['name'],
                'file' => $file,
                'module_id' => $module->id,
            ]);
            
            $response->assertSuccessful();
            $this->assertDatabaseHas('attachments', [
                'module_id' => $module->id,
                'name' => 'Test ' . $audioType['name'],
                'mime_type' => $audioType['mime'],
            ]);
        }
    });

    test('file size validation works correctly', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        
        // Test file at exactly 10MB (should pass)
        $file = UploadedFile::fake()->create('exactly10mb.pdf', 10240, 'application/pdf');
        
        $response = $this->actingAs($coach)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('attachments.store'), [
            'name' => 'Exactly 10MB File',
            'file' => $file,
            'module_id' => $module->id,
        ]);
        
        $response->assertSuccessful();
        
        // Test file over 10MB (should fail)
        $largeFile = UploadedFile::fake()->create('over10mb.pdf', 10241, 'application/pdf');
        
        $response = $this->actingAs($coach)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('attachments.store'), [
            'name' => 'Over 10MB File',
            'file' => $largeFile,
            'module_id' => $module->id,
        ]);
        
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['file']);
    });

    test('empty file is accepted but creates zero-size attachment', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        
        $file = UploadedFile::fake()->create('empty.pdf', 0, 'application/pdf');
        
        $response = $this->actingAs($coach)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('attachments.store'), [
            'name' => 'Empty File',
            'file' => $file,
            'module_id' => $module->id,
        ]);
        
        $response->assertSuccessful();
        $attachment = Attachment::latest()->first();
        expect($attachment->size)->toBe(0);
        expect($attachment->name)->toBe('Empty File');
    });

    test('non-file input is rejected', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        
        $response = $this->actingAs($coach)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('attachments.store'), [
            'name' => 'Not a file',
            'file' => 'just a string',
            'module_id' => $module->id,
        ]);
        
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['file']);
    });

    test('name validation works correctly', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        $file = UploadedFile::fake()->create('test.pdf', 1024, 'application/pdf');
        
        // Test empty name
        $response = $this->actingAs($coach)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('attachments.store'), [
            'name' => '',
            'file' => $file,
            'module_id' => $module->id,
        ]);
        
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name']);
        
        // Test name that's too long
        $response = $this->actingAs($coach)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('attachments.store'), [
            'name' => str_repeat('a', 256),
            'file' => $file,
            'module_id' => $module->id,
        ]);
        
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name']);
        
        // Test valid name at max length
        $response = $this->actingAs($coach)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('attachments.store'), [
            'name' => str_repeat('a', 255),
            'file' => $file,
            'module_id' => $module->id,
        ]);
        
        $response->assertSuccessful();
    });

    test('module_id validation works correctly', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $file = UploadedFile::fake()->create('test.pdf', 1024, 'application/pdf');
        
        // Test missing module_id
        $response = $this->actingAs($coach)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('attachments.store'), [
            'name' => 'Test File',
            'file' => $file,
        ]);
        
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['module_id']);
        
        // Test non-existent module_id
        $response = $this->actingAs($coach)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('attachments.store'), [
            'name' => 'Test File',
            'file' => $file,
            'module_id' => 'non-existent-id',
        ]);
        
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['module_id']);
        
        // Test valid module_id
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        $response = $this->actingAs($coach)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('attachments.store'), [
            'name' => 'Test File',
            'file' => $file,
            'module_id' => $module->id,
        ]);
        
        $response->assertSuccessful();
    });
});

describe('Attachment Security', function () {
    test('file storage uses private disk', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        
        $file = UploadedFile::fake()->create('test.pdf', 1024, 'application/pdf');
        
        $response = $this->actingAs($coach)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('attachments.store'), [
            'name' => 'Test Document',
            'file' => $file,
            'module_id' => $module->id,
        ]);
        
        $response->assertSuccessful();
        
        $attachment = Attachment::latest()->first();
        expect($attachment->disk)->toBe('private');
        
        // Verify file is stored in private disk
        Storage::disk('private')->assertExists($attachment->path);
        Storage::disk('public')->assertMissing($attachment->path);
    });

    test('filename is unique and not predictable', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        
        $file1 = UploadedFile::fake()->create('samename.pdf', 1024, 'application/pdf');
        $file2 = UploadedFile::fake()->create('samename.pdf', 1024, 'application/pdf');
        
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
        
        $attachments = Attachment::latest()->limit(2)->get();
        
        // Filenames should be different
        expect($attachments->first()->filename)->not->toBe($attachments->last()->filename);
        
        // Filenames should not be the original filename
        expect($attachments->first()->filename)->not->toBe('samename.pdf');
        expect($attachments->last()->filename)->not->toBe('samename.pdf');
        
        // Filenames should look like UUIDs
        expect($attachments->first()->filename)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\.pdf$/');
        expect($attachments->last()->filename)->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\.pdf$/');
    });

    test('file paths are stored in attachments subdirectory', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        
        $file = UploadedFile::fake()->create('test.pdf', 1024, 'application/pdf');
        
        $response = $this->actingAs($coach)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('attachments.store'), [
            'name' => 'Test Document',
            'file' => $file,
            'module_id' => $module->id,
        ]);
        
        $response->assertSuccessful();
        
        $attachment = Attachment::latest()->first();
        expect($attachment->path)->toStartWith('attachments/');
        expect($attachment->path)->toEndWith('.pdf');
    });

    test('file metadata is correctly stored', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        
        $file = UploadedFile::fake()->create('test.pdf', 1024, 'application/pdf');
        
        $response = $this->actingAs($coach)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('attachments.store'), [
            'name' => 'Test Document',
            'file' => $file,
            'module_id' => $module->id,
        ]);
        
        $response->assertSuccessful();
        
        $attachment = Attachment::latest()->first();
        
        expect($attachment->name)->toBe('Test Document');
        expect($attachment->size)->toBeGreaterThan(0);
        expect($attachment->mime_type)->toBe('application/pdf');
        expect($attachment->type)->toBe('file');
        expect($attachment->disk)->toBe('private');
    });

    test('sensitive information is not exposed in responses', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        
        $file = UploadedFile::fake()->create('test.pdf', 1024, 'application/pdf');
        
        $response = $this->actingAs($coach)
            ->withHeaders(['Accept' => 'application/json'])
            ->post(route('attachments.store'), [
            'name' => 'Test Document',
            'file' => $file,
            'module_id' => $module->id,
        ]);
        
        $response->assertSuccessful();
        
        $responseData = $response->json();
        
        // Should not expose internal path or disk information
        expect($responseData['attachment'])->not->toHaveKey('path');
        expect($responseData['attachment'])->not->toHaveKey('disk');
        
        // Should expose safe information
        expect($responseData['attachment'])->toHaveKey('id');
        expect($responseData['attachment'])->toHaveKey('name');
        expect($responseData['attachment'])->toHaveKey('filename');
        expect($responseData['attachment'])->toHaveKey('size');
        expect($responseData['attachment'])->toHaveKey('mime_type');
    });
});