<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {}

    public function index(): View
    {
        $categories = Category::withCount('listings')->orderBy('name')->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        $this->auditLogService->log(
            action: 'admin_category_created',
            targetType: Category::class,
            targetId: $category->id,
            details: ['name' => $category->name]
        );

        return back()->with('success', "Category \"{$category->name}\" created.");
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category->id)],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $category->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        $this->auditLogService->log(
            action: 'admin_category_updated',
            targetType: Category::class,
            targetId: $category->id,
            details: ['name' => $category->name]
        );

        return back()->with('success', "Category \"{$category->name}\" updated.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->listings()->exists()) {
            return back()->with('error', 'Cannot delete a category that is currently used by food listings.');
        }

        $name = $category->name;
        $id = $category->id;
        $category->delete();

        $this->auditLogService->log(
            action: 'admin_category_deleted',
            targetType: Category::class,
            targetId: $id,
            details: ['name' => $name]
        );

        return back()->with('success', "Category \"{$name}\" deleted.");
    }
}
