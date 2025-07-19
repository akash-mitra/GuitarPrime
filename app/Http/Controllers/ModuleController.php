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
            ->with('coach')
            ->when($search, function ($query, $search) {
                return $query->where('title', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->appends($request->query());

        // Add authorization information to each module
        $modules->getCollection()->transform(function ($module) {
            $module->can_edit = auth()->user()->can('update', $module);
            $module->can_delete = auth()->user()->can('delete', $module);

            return $module;
        });

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
            'price' => 'nullable|numeric|min:0|max:999999',
            'is_free' => 'boolean',
        ]);

        // Convert price from rupees to paisa if provided
        if (isset($validated['price']) && $validated['price'] !== null) {
            $validated['price'] = (int) round($validated['price'] * 100);
        }

        // Ensure is_free is set correctly
        $validated['is_free'] = $validated['is_free'] ?? false;

        $module = Module::create(array_merge($validated, [
            'coach_id' => auth()->id(),
        ]));

        // Return JSON response if request expects JSON
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Module created successfully.',
                'moduleId' => $module->id,
            ]);
        }

        return redirect()->route('modules.index')
            ->with('success', 'Module created successfully.')
            ->with('moduleId', $module->id);
    }

    public function show(Module $module)
    {
        $this->authorize('view', $module);

        $module->load(['attachments', 'courses']);
        $user = auth()->user();

        return Inertia::render('modules/Show', [
            'module' => $module,
            'canAccess' => $user ? $user->canAccess($module) : false,
        ]);
    }

    public function showInCourse(Course $course, Module $module)
    {
        $this->authorize('view', $course);
        $this->authorize('view', $module);

        // Verify that the module belongs to this course
        if (! $course->modules()->where('modules.id', $module->id)->exists()) {
            abort(404, 'Module not found in this course');
        }

        $module->load(['attachments']);
        $user = auth()->user();

        // Get course modules ordered by their pivot order
        $courseModules = $course->modules()
            ->orderBy('course_module_map.order')
            ->select('modules.id', 'modules.title', 'course_module_map.order')
            ->get();

        // Find current module position and get previous/next modules
        $currentModuleIndex = $courseModules->search(function ($item) use ($module) {
            return $item->id === $module->id;
        });

        $previousModule = null;
        $nextModule = null;

        if ($currentModuleIndex !== false) {
            if ($currentModuleIndex > 0) {
                $prev = $courseModules[$currentModuleIndex - 1];
                $previousModule = [
                    'id' => $prev->id,
                    'title' => $prev->title,
                ];
            }

            if ($currentModuleIndex < $courseModules->count() - 1) {
                $next = $courseModules[$currentModuleIndex + 1];
                $nextModule = [
                    'id' => $next->id,
                    'title' => $next->title,
                ];
            }
        }

        return Inertia::render('Courses/modules/Show', [
            'course' => $course->load(['theme', 'coach']),
            'module' => $module,
            'canAccessCourse' => $user ? $user->canAccess($course) : false,
            'canAccessModule' => $user ? $user->canAccess($module) : false,
            'coursePricing' => [
                'price' => $course->price,
                'is_free' => $course->is_free,
                'formatted_price' => $course->formatted_price,
            ],
            'previousModule' => $previousModule,
            'nextModule' => $nextModule,
        ]);
    }

    public function edit(Module $module)
    {
        $this->authorize('update', $module);

        $module->load('attachments');

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
            'price' => 'nullable|numeric|min:0|max:999999',
            'is_free' => 'boolean',
        ]);

        // Convert price from rupees to paisa if provided
        if (isset($validated['price']) && $validated['price'] !== null) {
            $validated['price'] = (int) round($validated['price'] * 100);
        }

        // Ensure is_free is set correctly
        $validated['is_free'] = $validated['is_free'] ?? false;

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
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'modules' => 'required|array',
            'modules.*.id' => 'required|exists:modules,id',
            'modules.*.order' => 'required|integer|min:1',
        ]);

        $course = Course::findOrFail($validated['course_id']);
        $this->authorize('update', $course);

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
