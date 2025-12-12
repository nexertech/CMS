<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ComplaintCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryController extends Controller
{
    public function index()
    {
        if (!Schema::hasTable('complaint_categories')) {
            $categories = new LengthAwarePaginator([], 0, 15);
            return view('admin.category.index', compact('categories'))
                ->with('error', 'Run migrations to create complaint_categories table.');
        }

        // Show all categories - ordered by ID (ascending - 1, 2, 3...)
        $categories = ComplaintCategory::orderBy('id', 'asc')->paginate(15);
        return view('admin.category.index', compact('categories'));
    }

    public function store(Request $request)
    {
        if (!Schema::hasTable('complaint_categories')) {
            return back()->with('error', 'Run migrations to create complaint_categories table (php artisan migrate).');
        }
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:complaint_categories,name',
            'description' => 'nullable|string',
        ]);
        ComplaintCategory::create($validated);
        return back()->with('success', 'Category created');
    }

    public function update(Request $request, $id)
    {
        if (!Schema::hasTable('complaint_categories')) {
            return back()->with('error', 'Run migrations to create complaint_categories table (php artisan migrate).');
        }
        
        try {
            $complaint_category = ComplaintCategory::findOrFail($id);
            
            $rules = [
                'name' => 'required|string|max:100',
                'description' => 'nullable|string',
            ];
            
            // Only validate uniqueness if name changed
            if ($request->name !== $complaint_category->name) {
                $exists = ComplaintCategory::where('name', $request->name)
                    ->where('id', '!=', $id)
                    ->exists();
                
                if ($exists) {
                    return back()->withErrors(['name' => 'The name has already been taken.'])->withInput();
                }
            }
            
            $validated = $request->validate($rules);
            $complaint_category->update($validated);
            
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['success' => true, 'message' => 'Category updated']);
            }
            return back()->with('success', 'Category updated');
        } catch (\Exception $e) {
            Log::error('Category update error: ' . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
            }
            return back()->with('error', 'Error updating category: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        if (!Schema::hasTable('complaint_categories')) {
            return back()->with('error', 'Run migrations to create complaint_categories table (php artisan migrate).');
        }
        
        try {
            $complaint_category = ComplaintCategory::findOrFail($id);
            // Delete category
            $complaint_category->delete();
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => true]);
            }
            return back()->with('success', 'Category removed from list');
        } catch (\Exception $e) {
            Log::error('Category delete error: ' . $e->getMessage());
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Error deleting category: ' . $e->getMessage());
        }
    }
}


