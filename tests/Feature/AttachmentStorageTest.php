<?php

use App\Models\Attachment;
use App\Models\Module;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('private');
    Storage::fake('public');
});

describe('Attachment Storage Management', function () {
    test('files are stored in private disk', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        
        $file = UploadedFile::fake()->create('test.pdf', 1024, 'application/pdf');
        
        $response = $this->actingAs($coach)->post(route('attachments.store'), [
            'name' => 'Test Document',
            'file' => $file,
            'module_id' => $module->id,
        ]);
        
        $response->assertSuccessful();
        
        $attachment = Attachment::latest()->first();
        
        // File should be in private disk
        Storage::disk('private')->assertExists($attachment->path);
        
        // File should NOT be in public disk
        Storage::disk('public')->assertMissing($attachment->path);
    });

    test('file paths are organized in attachments directory', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        
        $file = UploadedFile::fake()->create('test.pdf', 1024, 'application/pdf');
        
        $response = $this->actingAs($coach)->post(route('attachments.store'), [
            'name' => 'Test Document',
            'file' => $file,
            'module_id' => $module->id,
        ]);
        
        $response->assertSuccessful();
        
        $attachment = Attachment::latest()->first();
        
        expect($attachment->path)->toStartWith('attachments/');
        expect($attachment->disk)->toBe('private');
        
        // Verify the file actually exists at the expected path
        Storage::disk('private')->assertExists($attachment->path);
    });

    test('unique filenames prevent collisions', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        
        // Upload two files with the same original name but different content
        $file1 = UploadedFile::fake()->createWithContent('document.pdf', 'Content of first file');
        $file2 = UploadedFile::fake()->createWithContent('document.pdf', 'Content of second file');
        
        $response1 = $this->actingAs($coach)->post(route('attachments.store'), [
            'name' => 'Document 1',
            'file' => $file1,
            'module_id' => $module->id,
        ]);
        
        $response2 = $this->actingAs($coach)->post(route('attachments.store'), [
            'name' => 'Document 2',
            'file' => $file2,
            'module_id' => $module->id,
        ]);
        
        $response1->assertSuccessful();
        $response2->assertSuccessful();
        
        $attachments = Attachment::latest()->limit(2)->get();
        
        // Files should have different filenames
        expect($attachments->first()->filename)->not->toBe($attachments->last()->filename);
        
        // Both files should exist in storage
        Storage::disk('private')->assertExists($attachments->first()->path);
        Storage::disk('private')->assertExists($attachments->last()->path);
        
        // Files should be different in storage
        $content1 = Storage::disk('private')->get($attachments->first()->path);
        $content2 = Storage::disk('private')->get($attachments->last()->path);
        expect($content1)->not->toBe($content2);
    });

    test('file extension is preserved in generated filename', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        
        // Test a few key extensions
        $testCases = [
            ['ext' => 'pdf', 'mime' => 'application/pdf'],
            ['ext' => 'jpg', 'mime' => 'image/jpeg'],
            ['ext' => 'mp3', 'mime' => 'audio/mpeg'],
        ];
        
        foreach ($testCases as $testCase) {
            $ext = $testCase['ext'];
            $file = UploadedFile::fake()->create("test.$ext", 1024, $testCase['mime']);
            
            $response = $this->actingAs($coach)->post(route('attachments.store'), [
                'name' => "Test File $ext",
                'file' => $file,
                'module_id' => $module->id,
            ]);
            
            $response->assertSuccessful();
            
            $attachment = Attachment::where('name', "Test File $ext")->latest()->first();
            
            expect($attachment->filename)->toEndWith(".$ext");
            expect($attachment->path)->toEndWith(".$ext");
        }
    });
});

describe('Attachment Cleanup', function () {
    test('file is deleted from storage when attachment is deleted', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        
        $file = UploadedFile::fake()->create('test.pdf', 1024, 'application/pdf');
        
        $response = $this->actingAs($coach)->post(route('attachments.store'), [
            'name' => 'Test Document',
            'file' => $file,
            'module_id' => $module->id,
        ]);
        
        $response->assertSuccessful();
        
        $attachment = Attachment::latest()->first();
        
        // Verify file exists
        Storage::disk('private')->assertExists($attachment->path);
        
        // Delete attachment
        $deleteResponse = $this->actingAs($coach)->delete(route('attachments.destroy', $attachment->id));
        $deleteResponse->assertRedirect();
        
        // Verify file is deleted
        Storage::disk('private')->assertMissing($attachment->path);
        
        // Verify database record is deleted
        $this->assertDatabaseMissing('attachments', ['id' => $attachment->id]);
    });

    test('deletion handles missing files gracefully', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        $attachment = Attachment::factory()->create(['module_id' => $module->id]);
        
        // Verify file doesn't exist (wasn't created in factory)
        Storage::disk('private')->assertMissing($attachment->path);
        
        // Delete attachment should succeed even if file is missing
        $response = $this->actingAs($coach)->delete(route('attachments.destroy', $attachment->id));
        
        $response->assertRedirect();
        
        // Verify database record is deleted
        $this->assertDatabaseMissing('attachments', ['id' => $attachment->id]);
    });

    test('multiple attachments can be deleted independently', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        
        $file1 = UploadedFile::fake()->create('test1.pdf', 1024, 'application/pdf');
        $file2 = UploadedFile::fake()->create('test2.pdf', 1024, 'application/pdf');
        
        // Upload two files
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
        
        // Both files should exist
        Storage::disk('private')->assertExists($attachments->first()->path);
        Storage::disk('private')->assertExists($attachments->last()->path);
        
        // Delete first attachment
        $response = $this->actingAs($coach)->delete(route('attachments.destroy', $attachments->first()->id));
        $response->assertRedirect();
        
        // First file should be deleted, second should remain
        Storage::disk('private')->assertMissing($attachments->first()->path);
        Storage::disk('private')->assertExists($attachments->last()->path);
        
        // First record should be deleted, second should remain
        $this->assertDatabaseMissing('attachments', ['id' => $attachments->first()->id]);
        $this->assertDatabaseHas('attachments', ['id' => $attachments->last()->id]);
    });

    test('attachment update does not affect file storage', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        
        $file = UploadedFile::fake()->create('test.pdf', 1024, 'application/pdf');
        
        $response = $this->actingAs($coach)->post(route('attachments.store'), [
            'name' => 'Original Name',
            'file' => $file,
            'module_id' => $module->id,
        ]);
        
        $response->assertSuccessful();
        
        $attachment = Attachment::latest()->first();
        $originalPath = $attachment->path;
        
        // Verify file exists
        Storage::disk('private')->assertExists($originalPath);
        
        // Update attachment name
        $updateResponse = $this->actingAs($coach)->put(route('attachments.update', $attachment->id), [
            'name' => 'Updated Name',
        ]);
        
        $updateResponse->assertRedirect();
        
        $attachment->refresh();
        
        // File should still exist at the same path
        Storage::disk('private')->assertExists($originalPath);
        expect($attachment->path)->toBe($originalPath);
        
        // Only the name should be updated
        expect($attachment->name)->toBe('Updated Name');
    });
});

describe('Storage Configuration', function () {
    test('private disk configuration is used correctly', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        
        $file = UploadedFile::fake()->create('test.pdf', 1024, 'application/pdf');
        
        $response = $this->actingAs($coach)->post(route('attachments.store'), [
            'name' => 'Test Document',
            'file' => $file,
            'module_id' => $module->id,
        ]);
        
        $response->assertSuccessful();
        
        $attachment = Attachment::latest()->first();
        
        // Verify disk configuration
        expect($attachment->disk)->toBe('private');
        
        // Verify file is stored in the correct disk
        Storage::disk('private')->assertExists($attachment->path);
        
        // Verify file is NOT accessible through public disk
        Storage::disk('public')->assertMissing($attachment->path);
    });

    test('file storage path structure is consistent', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        
        $files = [
            UploadedFile::fake()->create('test1.pdf', 1024, 'application/pdf'),
            UploadedFile::fake()->create('test2.jpg', 1024, 'image/jpeg'),
            UploadedFile::fake()->create('test3.mp3', 1024, 'audio/mpeg'),
        ];
        
        foreach ($files as $file) {
            $response = $this->actingAs($coach)->post(route('attachments.store'), [
                'name' => 'Test File',
                'file' => $file,
                'module_id' => $module->id,
            ]);
            
            $response->assertSuccessful();
            
            $attachment = Attachment::latest()->first();
            
            // All files should be stored in attachments/ directory
            expect($attachment->path)->toStartWith('attachments/');
            
            // Path should contain the filename
            expect($attachment->path)->toContain($attachment->filename);
            
            // Full path should be: attachments/{filename}
            expect($attachment->path)->toBe('attachments/' . $attachment->filename);
        }
    });
});

describe('File Access Security', function () {
    test('files are not directly accessible via URL', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        
        $file = UploadedFile::fake()->create('test.pdf', 1024, 'application/pdf');
        
        $response = $this->actingAs($coach)->post(route('attachments.store'), [
            'name' => 'Test Document',
            'file' => $file,
            'module_id' => $module->id,
        ]);
        
        $response->assertSuccessful();
        
        $attachment = Attachment::latest()->first();
        
        // File should exist in private storage
        Storage::disk('private')->assertExists($attachment->path);
        
        // Attempting to access file directly should fail (either 404 or 403)
        $directAccess = $this->get('/storage/' . $attachment->path);
        expect($directAccess->status())->toBeIn([403, 404]);
        
        // File should only be accessible through the download route
        $authorizedAccess = $this->actingAs($coach)->get(route('attachments.download', $attachment->id));
        $authorizedAccess->assertSuccessful();
    });

    test('file content is preserved during upload and download', function () {
        $coach = User::factory()->create(['role' => 'coach']);
        $module = Module::factory()->create(['coach_id' => $coach->id]);
        
        $originalContent = 'This is a test file content with special characters: àáâãäåæçèéêë';
        $file = UploadedFile::fake()->createWithContent('test.txt', $originalContent);
        
        $response = $this->actingAs($coach)->post(route('attachments.store'), [
            'name' => 'Test Document',
            'file' => $file,
            'module_id' => $module->id,
        ]);
        
        $response->assertSuccessful();
        
        $attachment = Attachment::latest()->first();
        
        // Verify content is preserved in storage
        $storedContent = Storage::disk('private')->get($attachment->path);
        expect($storedContent)->toBe($originalContent);
        
        // Verify download response works correctly
        $downloadResponse = $this->actingAs($coach)->get(route('attachments.download', $attachment->id));
        $downloadResponse->assertSuccessful();
        $downloadResponse->assertDownload('Test Document.txt');
    });
});