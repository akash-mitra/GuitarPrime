<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use App\Models\Module;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Get the appropriate disk for storing attachments based on FILESYSTEM_DISK.
     * Maps local -> private (secure local storage), s3 -> s3 (secure cloud storage).
     */
    private function getAttachmentDisk(): string
    {
        return config('filesystems.default') === 'local' ? 'private' : 's3';
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'file' => 'required|file|max:10240', // 10MB max
            'module_id' => 'required|exists:modules,id',
        ]);

        $module = Module::findOrFail($validated['module_id']);
        $this->authorize('update', $module);

        $file = $request->file('file');
        $filename = Str::uuid().'.'.$file->getClientOriginalExtension();
        $disk = $this->getAttachmentDisk();
        $path = $file->storeAs('attachments', $filename, $disk);

        $attachment = Attachment::create([
            'module_id' => $module->id,
            'name' => $validated['name'],
            'filename' => $filename,
            'disk' => $disk,
            'path' => $path,
            'type' => 'file',
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ]);

        return response()->json([
            'message' => 'Attachment uploaded successfully.',
            'attachment' => $attachment->only(['id', 'name', 'filename', 'size', 'mime_type']),
        ]);
    }

    public function update(Request $request, Attachment $attachment)
    {
        $this->authorize('update', $attachment->module);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $attachment->update($validated);

        return back()->with('success', 'Attachment updated successfully.');
    }

    public function destroy(Attachment $attachment)
    {
        $this->authorize('update', $attachment->module);

        // Delete the file from storage
        if (Storage::disk($attachment->disk)->exists($attachment->path)) {
            Storage::disk($attachment->disk)->delete($attachment->path);
        }

        $attachment->delete();

        return back()->with('success', 'Attachment deleted successfully.');
    }

    public function download(Attachment $attachment)
    {
        $module = $attachment->module;
        $user = auth()->user();

        // Check if user can access this module
        if (! $user->canAccess($module)) {
            abort(403, 'You do not have access to this attachment.');
        }

        if (! Storage::disk($attachment->disk)->exists($attachment->path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk($attachment->disk)->download(
            $attachment->path,
            $attachment->name.'.'.pathinfo($attachment->filename, PATHINFO_EXTENSION)
        );
    }
}
