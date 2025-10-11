<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->get();

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function store(CategoryRequest $request)
    {
        Gate::authorize('manage-inventory');

        $validated = $request->validated();

        Category::create($validated);

        return redirect()->route('categories.index');
    }

    public function update(CategoryRequest $request, Category $category)
    {
        Gate::authorize('manage-inventory');

        $validated = $request->validated();

        $category->update($validated);

        return redirect()->route('categories.index');
    }

    public function destroy(Category $category)
    {
        Gate::authorize('manage-inventory');

        if ($category->items()->exists()) {
            return redirect()
                ->route('categories.index')
                ->withErrors([
                    'category' => __('Unable to delete a category that still has items assigned.'),
                ]);
        }

        $category->delete();

        return redirect()->route('categories.index');
    }
}
