<?php

namespace App\Http\Controllers;

use App\Models\Theme;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ThemeController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Theme::class);

        $search = $request->get('search');

        $themes = Theme::withCount('courses')
            ->when($search, function ($query, $search) {
                return $query->where('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->appends($request->query());

        return Inertia::render('Themes/Index', [
            'themes' => $themes,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create()
    {
        $this->authorize('create', Theme::class);

        return Inertia::render('Themes/Create');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Theme::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:themes,name',
            'description' => 'nullable|string|max:1000',
        ]);

        Theme::create($validated);

        return redirect()->route('themes.index')
            ->with('success', 'Theme created successfully.');
    }

    public function show(Request $request, Theme $theme)
    {
        $this->authorize('view', $theme);

        $search = $request->get('search');

        $courses = $theme->courses()
            ->where('is_approved', true)
            ->with('coach')
            ->when($search, function ($query, $search) {
                return $query->where('title', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->appends($request->query());

        return Inertia::render('Themes/Show', [
            'theme' => $theme,
            'courses' => $courses,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function edit(Theme $theme)
    {
        $this->authorize('update', $theme);

        return Inertia::render('Themes/Edit', [
            'theme' => $theme,
        ]);
    }

    public function update(Request $request, Theme $theme)
    {
        $this->authorize('update', $theme);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:themes,name,'.$theme->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $theme->update($validated);

        return redirect()->route('themes.index')
            ->with('success', 'Theme updated successfully.');
    }

    public function destroy(Theme $theme)
    {
        $this->authorize('delete', $theme);

        $theme->delete();

        return redirect()->route('themes.index')
            ->with('success', 'Theme deleted successfully.');
    }
}
