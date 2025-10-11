<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json([
            "list"=> Brand::all(),
            "total" => Brand::count()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'code' => 'required|string|unique:brands',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:active,inactive'
        ]);
        
        // handle image
        $imageUrl = null;
        if($request->hasFile("image")){
            $imageUrl = $request->file("image")->store("brands","public");
        }
        
        Brand::create([
            "name" => $request->name,
            "code" => $request->code,
            "image" => $imageUrl,
            "status" => $request->status
        ]);

        return response()->json([
            "message" =>  "Insert successfully!"
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $brand = Brand::findOrFail($id);
        return response()->json([
            "data" =>  $brand
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $brand = Brand::findOrFail($id);
        $request->validate([
            'name' => 'required|string',
            'code' => 'required|string|unique:brands,code,'.$id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:active,inactive'
        ]);
        
        // handle image
        if($request->hasFile("image")){
            if($brand->image){
                Storage::disk("public")->delete($brand->image);
            }
            $brand->image = $request->file("image")->store("brands","public");
        } else if ($request->has('delete_image')) {
            if($brand->image){
                Storage::disk("public")->delete($brand->image);
                $brand->image = null;
            }
        }
        
        $brand->update([
            "name" => $request->name,
            "code" => $request->code,
            "status" => $request->status
        ]);
        
        return response()->json([
            "message" =>  "Update successfully!"
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $brand = Brand::findOrFail($id);
        
        // Check if brand has products
        if($brand->products()->count() > 0){
            return response()->json([
                "message" => "Cannot delete brand with existing products",
                "error" => true
            ], 422);
        }
        
        if($brand->image){
            Storage::disk("public")->delete($brand->image);
        }
        
        $brand->delete();
        
        return response()->json([
            "message" =>  "Delete successfully!"
        ]);
    }
}