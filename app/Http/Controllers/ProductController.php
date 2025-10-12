<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('pro_name', 'like', "%{$search}%")
                    ->orWhere('pro_code', 'like', "%{$search}%");
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        $products = $query->get();
        
        return response()->json([
            'success' => true,
            'data' => $products,
            'list'=> $products,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'pro_name' => 'required|string',
            'qty' => 'nullable|string',
            'upis' => 'required|numeric',
            'sup' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10248',
            'status' => 'required|in:active,inactive'
        ]);
        
        // handle image
        $imageUrl = null;
        if ($request->hasFile('image')) {
            $imageUrl = $request->file('image')->store('product', 'r2');
        }
        
        $product = Product::create([
            'category_id' => $request->category_id,
            'pro_name' => $request->pro_name,
            'qty' => $request->qty,
            'upis' => $request->upis,
            'sup' => $request->sup,
            'image' => $imageUrl,
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Insert successfully!',
            'data' => $product->load('category')
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $product->load(['category'])
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'pro_name' => 'required|string',
            'qty' => 'nullable|string',
            'upis' => 'required|numeric',
            'sup' => 'required|numeric',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,Webp|max:10248',
            'status' => 'required|in:active,inactive'
        ]);
        
        // handle image
        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('r2')->delete($product->image);
            }
            $product->image = $request->file('image')->store('product', 'r2');
        } else if ($request->has('delete_image')) {
            if ($product->image) {
                Storage::disk('r2')->delete($product->image);
                $product->image = null;
            }
        }
        
        $product->update([
            'category_id' => $request->category_id,
            'pro_name' => $request->pro_name,
            'qty' => $request->qty,
            'upis' => $request->upis,
            'sup' => $request->sup,
            'status' => $request->status
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Update successfully!',
            'data' => $product->fresh()->load('category')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        
        // Delete image if exists
        if ($product->image) {
            Storage::disk('r2')->delete($product->image);
        }
        
        $product->delete();
        return response()->json([
            'success' => true,
            'message' => 'Delete successfully!',
            'data' => $product
        ]);
    }
}