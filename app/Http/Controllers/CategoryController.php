<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Only admin can view all categories
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', __('messages.unauthorized'));
        }
        
        $categories = Category::all();
        return view('categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Only admin can create categories
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', __('messages.unauthorized'));
        }
        
        return view('categories.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Only admin can store categories
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', __('messages.unauthorized'));
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
        ]);
        
        Category::create($validated);
        
        return redirect()->route('categories.index')
            ->with('success', __('messages.category_created'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        // Both admin and worker can view category details
        return view('categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        // Only admin can edit categories
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', __('messages.unauthorized'));
        }
        
        return view('categories.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        // Only admin can update categories
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', __('messages.unauthorized'));
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
        ]);
        
        $category->update($validated);
        
        return redirect()->route('categories.index')
            ->with('success', __('messages.category_updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        // Only admin can delete categories
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', __('messages.unauthorized'));
        }
        
        // Check if category has products
        if ($category->products()->count() > 0) {
            return redirect()->route('categories.index')
                ->with('error', __('messages.category_has_products'));
        }
        
        $category->delete();
        
        return redirect()->route('categories.index')
            ->with('success', __('messages.category_deleted'));
    }
} 