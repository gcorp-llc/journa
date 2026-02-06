<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order', 'asc')
            ->get();

        return CategoryResource::collection($categories);
    }

    public function show($slug)
    {
        $category = Category::where('slug', $slug)
            ->with(['children', 'news' => function($query) {
                $query->published()->latest()->take(10);
            }])
            ->firstOrFail();

        return new CategoryResource($category);
    }
}
