<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\ComplaintCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BrandController extends Controller
{
    public function index()
    {
        // Get brands with category relationship
        $brands = Brand::with('category')->orderBy('id', 'asc')->paginate(15);
        $categories = ComplaintCategory::all()->pluck('name', 'id');
        
        return view('admin.brands.index', compact('brands', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:complaint_categories,id',
            'name' => 'required|string|max:255|unique:brands,name,NULL,id,category_id,' . $request->category_id,
        ]);

        Brand::create($validated);

        return back()->with('success', 'Brand created successfully.');
    }

    public function update(Request $request, $id)
    {
        try {
            $brand = Brand::findOrFail($id);
            
            $validated = $request->validate([
                'category_id' => 'required|exists:complaint_categories,id',
                'name' => 'required|string|max:255|unique:brands,name,' . $id . ',id,category_id,' . $request->category_id,
            ]);

            $brand->update($validated);
            
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['success' => true, 'message' => 'Brand updated successfully']);
            }
            return back()->with('success', 'Brand updated successfully.');
        } catch (\Exception $e) {
            Log::error('Brand update error: ' . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
            }
            return back()->with('error', 'Error updating brand: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $brand = Brand::findOrFail($id);
            $brand->delete();
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => true]);
            }
            return back()->with('success', 'Brand deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Brand delete error: ' . $e->getMessage());
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Error deleting brand: ' . $e->getMessage());
        }
    }

    public function getByCategory($categoryId)
    {
        $brands = Brand::where('category_id', $categoryId)->orderBy('name')->get(['id', 'name']);
        return response()->json($brands);
    }
}
