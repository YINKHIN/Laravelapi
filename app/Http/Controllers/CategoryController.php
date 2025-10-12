<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Category::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Use the filtered query instead of returning all categories
        $category = $query->get();

        return response()->json([
            'success' => true,
            'data' => $category,
            'total' => Category::count()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10248',
            'status' => 'required|in:active,inactive'
        ]);

        // handle image
        $imageUrl = null;
        if ($request->hasFile('image')) {
            $imageUrl = $request->file('image')->store('categories', 'r2');
        }

        Category::create([
            'name' => $request->name,
            'description' => $request->description,
            'image' => $imageUrl,
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Insert successfully!'
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = Category::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $category
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $category = Category::findOrFail($id);
        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10248',
            'status' => 'required|in:active,inactive'
        ]);

        // handle image
        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('r2')->delete($category->image);
            }
            $category->image = $request->file('image')->store('categories', 'r2');
        } else if ($request->has('delete_image')) {
            if ($category->image) {
                Storage::disk('r2')->delete($category->image);
                $category->image = null;
            }
        }

        $category->update([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Update successfully!'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);
        $category->delete($id);
        return response()->json([
            'success' => true,
            'message' => 'Delete successfully!',
            'data' => $category
        ]);
    }
}
