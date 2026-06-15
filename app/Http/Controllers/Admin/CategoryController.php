<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $paginated  = Category::whereNull('parent_id')->withCount('children')->with('children')->paginate(20);
        $categories = flattenCategories(Category::whereNull('parent_id')->with('children')->get()->all());
        $mothers    = Category::whereNull('parent_id')->orderBy('title')->get(['id', 'title']);
        $stats = [
            'total'    => Category::count(),
            'mothers'  => Category::whereNull('parent_id')->count(),
            'subs'     => Category::whereNotNull('parent_id')->count(),
            'active'   => Category::where('is_active', true)->count(),
            'inactive' => Category::where('is_active', false)->count(),
        ];
        return view('admin.categories2.index', compact('paginated', 'categories', 'mothers', 'stats'));
    }

    public function create()
    {
        return $this->index();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'     => 'required|string|max:255',
            'slug'      => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
        ]);

        $slug = generateUniqueSlug(Category::class, $validated['slug'] ?? $validated['title']);

        Category::create([
            'title'     => $validated['title'],
            'slug'      => $slug,
            'parent_id' => $validated['parent_id'] ?? null,
            'is_active' => true,
        ]);
        return redirect()->route('admin.categories.index')
                         ->with('success', 'Category created successfully');
    }

    public function show($id)
    {
        $category   = Category::withCount('children')->with('children')->findOrFail($id);
        $paginated  = Category::whereNull('parent_id')->withCount('children')->with('children')->paginate(20);
        $categories = flattenCategories(Category::whereNull('parent_id')->with('children')->get()->all());
        return view('admin.categories2.show', compact('category', 'categories', 'paginated'));
    }

    public function edit($id)
    {
        $category   = Category::findOrFail($id);
        $paginated  = Category::whereNull('parent_id')->withCount('children')->with('children')->paginate(20);
        $categories = flattenCategories(Category::whereNull('parent_id')->with('children')->get()->all());
        return view('admin.categories2.edit', compact('category', 'categories', 'paginated'));
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $validated = $request->validate([
            'title'     => 'required|string|max:255',
            'slug'      => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'parent_id' => ['nullable', Rule::exists('categories', 'id')->whereNot('id', $category->id)],
        ]);

        // If slug is changed, regenerate unique slug
        $slug = generateUniqueSlug(
            Category::class,
            $validated['slug'] ?? $validated['title'],
            $category->id // exclude current record from uniqueness check
        );

        $is_active = $validated['is_active'] ? true : false;
        $category->update([
            'title' => $validated['title'],
            'slug'  => $slug,
            'is_active'  => $is_active,
            'parent_id' => $validated['parent_id'],
        ]);

        return redirect()->route('admin.categories.index')
                         ->with('success', 'Category updated successfully');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return redirect()->route('admin.categories.index')
                         ->with('success', 'Category deleted successfully');
    }
}
