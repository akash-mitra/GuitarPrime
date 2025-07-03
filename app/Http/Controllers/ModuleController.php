<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Module;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ModuleController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Module::class);

        $search = $request->get('search');

        $modules = Module::withCount('attachments')
            ->when($search, function ($query, $search) {
                return $query->where('title', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->appends($request->query());

        return Inertia::render('modules/Index', [
            'modules' => $modules,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create()
    {
        $this->authorize('create', Module::class);

        return Inertia::render('modules/Create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Module::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'difficulty' => 'required|in:easy,medium,hard',
            'video_url' => 'nullable|url|regex:/^https:\/\/(www\.)?vimeo\.com\/\d+(\?.*)?$/',
        ]);

        Module::create($validated);

        return redirect()->route('modules.index')
            ->with('success', 'Module created successfully.');
    }

    public function show(Module $module)
    {
        $this->authorize('view', $module);

        $module->load(['attachments', 'courses']);
        $user = auth()->user();

        return Inertia::render('modules/Show', [
            'module' => $module,
            'canAccess' => $user->canAccess($module),
        ]);
    }

    public function showInCourse(Course $course, Module $module)
    {
        $this->authorize('view', $course);

        // Verify that the module belongs to this course
        if (! $course->modules()->where('modules.id', $module->id)->exists()) {
            abort(404, 'Module not found in this course');
        }

        $module->load(['attachments']);
        $user = auth()->user();

        return Inertia::render('Courses/modules/Show', [
            'course' => $course->load(['theme', 'coach']),
            'module' => $module,
            'canAccessCourse' => $user->canAccess($course),
            'canAccessModule' => $user->canAccess($module),
            'coursePricing' => [
                'price' => $course->price,
                'is_free' => $course->is_free,
                'formatted_price' => $course->formatted_price,
            ],
        ]);
    }

    public function edit(Module $module)
    {
        $this->authorize('update', $module);

        return Inertia::render('modules/Edit', [
            'module' => $module,
        ]);
    }

    public function update(Request $request, Module $module)
    {
        $this->authorize('update', $module);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'difficulty' => 'required|in:easy,medium,hard',
            'video_url' => 'nullable|url|regex:/^https:\/\/(www\.)?vimeo\.com\/\d+(\?.*)?$/',
        ]);

        $module->update($validated);

        return redirect()->route('modules.index')
            ->with('success', 'Module updated successfully.');
    }

    public function destroy(Module $module)
    {
        $this->authorize('delete', $module);

        $module->delete();

        return redirect()->route('modules.index')
            ->with('success', 'Module deleted successfully.');
    }

    public function reorder(Request $request)
    {
        $this->authorize('update', new Module);

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'modules' => 'required|array',
            'modules.*.id' => 'required|exists:modules,id',
            'modules.*.order' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['modules'] as $moduleData) {
                DB::table('course_module_map')
                    ->where('course_id', $validated['course_id'])
                    ->where('module_id', $moduleData['id'])
                    ->update(['order' => $moduleData['order']]);
            }
        });

        return back()->with('success', 'Modules reordered successfully.');
    }
}
