<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Module;
use App\Models\Theme;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Course::class);

        $user = auth()->user();
        $search = $request->get('search');

        $courses = Course::with(['theme', 'coach'])
            ->when($search, function ($query, $search) {
                return $query->where('title', 'like', "%{$search}%");
            })
            ->when($user->role === 'student', function ($query) {
                // Students can only see approved courses
                return $query->approved();
            })
            ->when($user->role === 'coach', function ($query) use ($user) {
                // Coaches can see their own courses (approved and pending)
                return $query->where('coach_id', $user->id);
            })
            // Admins can see all courses (no additional filtering)
            ->latest()
            ->paginate(10)
            ->appends($request->query());

        return Inertia::render('Courses/Index', [
            'courses' => $courses,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create()
    {
        $this->authorize('create', Course::class);

        $themes = Theme::all(['id', 'name']);
        $modules = null;

        // Only provide modules data for admins
        if (auth()->user()->role === 'admin') {
            $modules = Module::all(['id', 'title', 'description', 'difficulty', 'video_url']);
        }

        return Inertia::render('Courses/Create', [
            'themes' => $themes,
            'modules' => $modules,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Course::class);

        $validated = $request->validate([
            'theme_id' => 'required|exists:themes,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'module_ids' => 'nullable|array',
            'module_ids.*' => 'exists:modules,id',
            'price' => 'nullable|numeric|min:0|max:999999',
            'is_free' => 'boolean',
        ]);

        $validated['coach_id'] = auth()->id();
        $validated['is_approved'] = false; // Default to unapproved

        // Convert price from rupees to paisa if provided
        if (isset($validated['price']) && $validated['price'] !== null) {
            $validated['price'] = (int) round($validated['price'] * 100);
        }

        // Ensure is_free is set correctly
        $validated['is_free'] = $validated['is_free'] ?? false;

        $course = Course::create($validated);

        // Only admins can assign modules
        if (auth()->user()->role === 'admin' && ! empty($validated['module_ids'])) {
            // Attach modules with order based on array index
            $modulesWithOrder = [];
            foreach ($validated['module_ids'] as $index => $moduleId) {
                $modulesWithOrder[$moduleId] = ['order' => $index + 1];
            }
            $course->modules()->attach($modulesWithOrder);
        }

        return redirect()->route('courses.index')
            ->with('success', 'Course created successfully. Awaiting admin approval.');
    }

    public function show(Course $course)
    {
        $this->authorize('view', $course);

        $course->load(['theme', 'coach', 'modules' => function ($query) {
            $query->withPivot('order')->orderBy('course_module_map.order');
        }]);

        $user = auth()->user();

        return Inertia::render('Courses/Show', [
            'course' => $course,
            'canAccess' => $user->canAccess($course),
            'pricing' => [
                'price' => $course->price,
                'is_free' => $course->is_free,
                'formatted_price' => $course->formatted_price,
            ],
            'moduleAccess' => $course->modules->mapWithKeys(function ($module) use ($user) {
                return [$module->id => $user->canAccess($module)];
            }),
        ]);
    }

    public function edit(Course $course)
    {
        $this->authorize('update', $course);

        $themes = Theme::all(['id', 'name']);
        $modules = null;

        // Load course modules and provide all modules data for admins
        $course->load(['modules']);
        if (auth()->user()->role === 'admin') {
            $modules = Module::all(['id', 'title', 'description', 'difficulty', 'video_url']);
        }

        return Inertia::render('Courses/Edit', [
            'course' => $course,
            'themes' => $themes,
            'modules' => $modules,
        ]);
    }

    public function update(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $validated = $request->validate([
            'theme_id' => 'required|exists:themes,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'module_ids' => 'nullable|array',
            'module_ids.*' => 'exists:modules,id',
            'price' => 'nullable|numeric|min:0|max:999999',
            'is_free' => 'boolean',
        ]);

        // Convert price from rupees to paisa if provided
        if (isset($validated['price']) && $validated['price'] !== null) {
            $validated['price'] = (int) round($validated['price'] * 100);
        }

        // Ensure is_free is set correctly
        $validated['is_free'] = $validated['is_free'] ?? false;

        $course->update($validated);

        // Only admins can manage modules
        if (auth()->user()->role === 'admin') {
            if (isset($validated['module_ids'])) {
                // Sync modules with order based on array index
                $modulesWithOrder = [];
                foreach ($validated['module_ids'] as $index => $moduleId) {
                    $modulesWithOrder[$moduleId] = ['order' => $index + 1];
                }
                $course->modules()->sync($modulesWithOrder);
            }
        }

        return redirect()->route('courses.index')
            ->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        $this->authorize('delete', $course);

        $course->delete();

        return redirect()->route('courses.index')
            ->with('success', 'Course deleted successfully.');
    }

    public function approve(Course $course)
    {
        $this->authorize('approve', $course);

        $course->update(['is_approved' => true]);

        return redirect()->back()
            ->with('success', 'Course approved successfully.');
    }

    public function approvalQueue()
    {
        $this->authorize('approve', new Course);

        $courses = Course::with(['theme', 'coach'])
            ->pending()
            ->latest()
            ->paginate(10);

        return Inertia::render('Courses/ApprovalQueue', [
            'courses' => $courses,
        ]);
    }
}
