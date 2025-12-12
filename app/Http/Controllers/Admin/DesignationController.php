<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use App\Models\ComplaintCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class DesignationController extends Controller
{
    public function index()
    {
        if (!Schema::hasTable('designations')) {
            $designations = new LengthAwarePaginator([], 0, 15);
            return view('admin.designation.index', compact('designations'))
                ->with('error', 'Run migrations to create designations table.');
        }

        $designations = Designation::orderBy('id', 'asc')->paginate(15);
        $categories = Schema::hasTable('complaint_categories')
            ? ComplaintCategory::orderBy('name')->pluck('name')
            : collect();
        
        return view('admin.designation.index', compact('designations', 'categories'));
    }

    public function store(Request $request)
    {
        if (!Schema::hasTable('designations')) {
            return back()->with('error', 'Run migrations to create designations table (php artisan migrate).');
        }
        
        $rules = [
            'category' => 'required|string|max:100',
            'name' => [
                'required',
                'string',
                'max:100',
                function ($attribute, $value, $fail) use ($request) {
                    $query = Designation::where('name', $value)
                        ->where('status', 'active');
                    
                    // Check category if column exists
                    try {
                        if (Schema::hasColumn('designations', 'category')) {
                            $query->where('category', $request->category);
                        }
                    } catch (\Exception $e) {
                        // If column check fails, just check name
                    }
                    
                    if ($query->exists()) {
                        $fail('The designation name has already been taken for this category.');
                    }
                }
            ],
            'status' => 'required|in:active,inactive',
        ];
        
        $validated = $request->validate($rules);
        // If legacy column exists, set safe default without requiring department module
        try {
            if (Schema::hasColumn('designations', 'department_id') && !array_key_exists('department_id', $validated)) {
                $validated['department_id'] = 0; // placeholder
            }
        } catch (\Exception $e) {}
        
        // Remove category if column doesn't exist
        try {
            if (!Schema::hasColumn('designations', 'category')) {
                unset($validated['category']);
            }
        } catch (\Exception $e) {
            // If check fails, assume column exists
        }
        
        Designation::create($validated);
        return back()->with('success', 'Designation created');
    }

    public function update(Request $request, $id)
    {
        if (!Schema::hasTable('designations')) {
            return back()->with('error', 'Run migrations to create designations table (php artisan migrate).');
        }
        
        try {
            $designation = Designation::findOrFail($id);
            
            $rules = [
                'category' => 'required|string|max:100',
                'name' => [
                    'required',
                    'string',
                    'max:100',
                    function ($attribute, $value, $fail) use ($request, $id) {
                        $query = Designation::where('name', $value)
                            ->where('status', 'active')
                            ->where('id', '!=', $id);
                        
                        // Check category if column exists
                        try {
                            if (Schema::hasColumn('designations', 'category')) {
                                $query->where('category', $request->category);
                            }
                        } catch (\Exception $e) {
                            // If column check fails, just check name
                        }
                        
                        if ($query->exists()) {
                            $fail('The designation name has already been taken for this category.');
                        }
                    }
                ],
                'description' => 'nullable|string',
                'status' => 'required|in:active,inactive',
            ];
            
            $validated = $request->validate($rules);
            // If legacy column exists, set safe default without requiring department module
            try {
                if (Schema::hasColumn('designations', 'department_id') && !array_key_exists('department_id', $validated)) {
                    $validated['department_id'] = 0; // placeholder
                }
            } catch (\Exception $e) {}
            
            // Remove category if column doesn't exist
            try {
                if (!Schema::hasColumn('designations', 'category')) {
                    unset($validated['category']);
                }
            } catch (\Exception $e) {
                // If check fails, assume column exists
            }
            
            $designation->update($validated);
            
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['success' => true, 'message' => 'Designation updated']);
            }
            return redirect()->route('admin.designation.index')->with('success', 'Designation updated');
        } catch (\Exception $e) {
            Log::error('Designation update error: ' . $e->getMessage());
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['success' => false, 'message' => 'Error updating designation: ' . $e->getMessage()], 422);
            }
            return redirect()->route('admin.designation.index')->with('error', 'Error updating designation: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        if (!Schema::hasTable('designations')) {
            return back()->with('error', 'Run migrations to create designations table (php artisan migrate).');
        }
        
        try {
            $designation = Designation::findOrFail($id);
            $designation->update([
                'status' => 'inactive'
            ]);
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => true]);
            }
            return back()->with('success', 'Designation removed from list');
        } catch (\Exception $e) {
            Log::error('Designation delete error: ' . $e->getMessage());
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Error deleting designation: ' . $e->getMessage());
        }
    }
}

