<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Theme;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CourseController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', Course::class);

        $user = auth()->user();

        $courses = Course::with(['theme', 'coach'])
            ->when($user->role === 'coach', function ($query) use ($user) {
                return $query->where('coach_id', $user->id);
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('Courses/Index', [
            'courses' => $courses
        ]);
    }

    public function create()
    {
        $this->authorize('create', Course::class);

        $themes = Theme::all(['id', 'name']);

        return Inertia::render('Courses/Create', [
            'themes' => $themes
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Course::class);

        $validated = $request->validate([
            'theme_id' => 'required|exists:themes,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000'
        ]);

        $validated['coach_id'] = auth()->id();
        $validated['is_approved'] = false; // Default to unapproved

        Course::create($validated);

        return redirect()->route('courses.index')
            ->with('success', 'Course created successfully. Awaiting admin approval.');
    }

    public function show(Course $course)
    {
        $this->authorize('view', $course);

        $course->load(['theme', 'coach', 'modules' => function ($query) {
            $query->withPivot('order')->orderBy('course_module_map.order');
        }]);

        return Inertia::render('Courses/Show', [
            'course' => $course
        ]);
    }

    public function edit(Course $course)
    {
        $this->authorize('update', $course);

        $themes = Theme::all(['id', 'name']);

        return Inertia::render('Courses/Edit', [
            'course' => $course,
            'themes' => $themes
        ]);
    }

    public function update(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $validated = $request->validate([
            'theme_id' => 'required|exists:themes,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000'
        ]);

        $course->update($validated);

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
        $this->authorize('approve', new Course());

        $courses = Course::with(['theme', 'coach'])
            ->pending()
            ->latest()
            ->paginate(10);

        return Inertia::render('Courses/ApprovalQueue', [
            'courses' => $courses
        ]);
    }
}
