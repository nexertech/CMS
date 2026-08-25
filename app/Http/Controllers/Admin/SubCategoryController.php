<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ComplaintCategory;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class SubCategoryController extends Controller
{
    /**
     * Display a listing of the sub categories.
     */
    public function index(Request $request)
    {
        if (!Schema::hasTable('sub_categories')) {
            $subCategories = new LengthAwarePaginator([], 0, 15);
            $categories = collect();
            return view('admin.sub_category.index', compact('subCategories', 'categories'))
                ->with('error', 'Run migrations to create sub_categories table.');
        }

        $query = SubCategory::with('category');

        // Search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('app_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('category', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Category filter
        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }

        // Status filter
        if ($request->has('status') && $request->status !== null && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $subCategories = $query->orderBy('id', 'asc')->paginate(15)->withQueryString();
        $categories = ComplaintCategory::where('status', 1)->orderBy('name', 'asc')->get();

        if ($request->ajax() && $request->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'sub_categories' => $subCategories
            ]);
        }

        return view('admin.sub_category.index', compact('subCategories', 'categories'));
    }

    /**
     * Store a newly created sub category in storage.
     */
    public function store(Request $request)
    {
        if (!Schema::hasTable('sub_categories')) {
            return back()->with('error', 'Run migrations to create sub_categories table.');
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:complaint_categories,id',
            'name' => 'required|string|max:100',
            'app_name' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'status' => 'required|in:0,1',
        ]);

        SubCategory::create($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Sub Category created successfully']);
        }

        return back()->with('success', 'Sub Category created successfully');
    }

    /**
     * Update the specified sub category in storage.
     */
    public function update(Request $request, $id)
    {
        if (!Schema::hasTable('sub_categories')) {
            return back()->with('error', 'Run migrations to create sub_categories table.');
        }

        try {
            $subCategory = SubCategory::findOrFail($id);

            $validated = $request->validate([
                'category_id' => 'required|exists:complaint_categories,id',
                'name' => 'required|string|max:100',
                'app_name' => 'nullable|string|max:100',
                'description' => 'nullable|string',
                'status' => 'required|in:0,1',
            ]);

            $subCategory->update($validated);

            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['success' => true, 'message' => 'Sub Category updated successfully']);
            }

            return back()->with('success', 'Sub Category updated successfully');
        } catch (\Exception $e) {
            Log::error('Sub Category update error: ' . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
            }
            return back()->with('error', 'Error updating Sub Category: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified sub category from storage.
     */
    public function destroy($id)
    {
        if (!Schema::hasTable('sub_categories')) {
            return back()->with('error', 'Run migrations to create sub_categories table.');
        }

        try {
            $subCategory = SubCategory::findOrFail($id);
            $subCategory->delete();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => true, 'message' => 'Sub Category deleted successfully']);
            }

            return back()->with('success', 'Sub Category removed from list');
        } catch (\Exception $e) {
            Log::error('Sub Category delete error: ' . $e->getMessage());
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Error deleting Sub Category: ' . $e->getMessage());
        }
    }

    /**
     * Get sub categories by category (AJAX endpoint for dropdowns).
     */
    public function getByCategory(Request $request)
    {
        if (!Schema::hasTable('sub_categories')) {
            return response()->json(['sub_categories' => []]);
        }

        $categoryId = $request->input('category_id') ?? $request->input('category');

        if (!$categoryId) {
            return response()->json(['sub_categories' => []]);
        }

        // If category argument is name instead of ID
        if (!is_numeric($categoryId)) {
            $categoryObj = ComplaintCategory::where('name', $categoryId)->first();
            $categoryId = $categoryObj ? $categoryObj->id : null;
        }

        if (!$categoryId) {
            return response()->json(['sub_categories' => []]);
        }

        $subCategories = SubCategory::where('category_id', $categoryId)
            ->where('status', 1)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'app_name']);

        return response()->json(['sub_categories' => $subCategories]);
    }
}
