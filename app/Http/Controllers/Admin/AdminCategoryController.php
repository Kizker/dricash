<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminCategoryController extends Controller
{
    public function index(): Response
    {
        $categories = Category::where('is_default', true)
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
            'counts' => [
                'income' => $categories->where('type', 'income')->count(),
                'expense' => $categories->where('type', 'expense')->count(),
                'obligation' => $categories->where('type', 'obligation')->count(),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:income,expense,obligation'],
            'icon' => ['required', 'string', 'max:50'],
            'color' => ['required', 'string', 'max:20'],
        ]);

        Category::create([
            'user_id' => null,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'icon' => $validated['icon'],
            'color' => $validated['color'],
            'is_default' => true,
        ]);

        return back()->with('success', "Kategori master '{$validated['name']}' berhasil ditambahkan.");
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:income,expense,obligation'],
            'icon' => ['required', 'string', 'max:50'],
            'color' => ['required', 'string', 'max:20'],
        ]);

        $category->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'icon' => $validated['icon'],
            'color' => $validated['color'],
        ]);

        return back()->with('success', "Kategori master '{$category->name}' berhasil diperbarui.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        $categoryName = $category->name;
        $category->delete();

        return back()->with('success', "Kategori master '{$categoryName}' berhasil dihapus.");
    }
}
