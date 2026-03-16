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

        $designations = Designation::with('category')->orderBy('id', 'asc')->paginate(15);
        $categories = Schema::hasTable('complaint_categories')
            ? ComplaintCategory::where('status', 1)->orderBy('name')->pluck('name', 'id')
            : collect();
        
        return view('admin.designation.index', compact('designations', 'categories'));
    }

    public function store(Request $request)
    {
        if (!Schema::hasTable('designations')) {
            return back()->with('error', 'Run migrations to create designations table (php artisan migrate).');
        }
        
        $rules = [
            'category_id' => 'required|exists:complaint_categories,id',
            'name' => [
                'required',
                'string',
                'max:100',
                function ($attribute, $value, $fail) use ($request) {
                    $query = Designation::where('name', $value)
                        ->where('status', 1)
                        ->where('category_id', $request->category_id);
                    
                    if ($query->exists()) {
                        $fail('The designation name has already been taken for this category.');
                    }
                }
            ],
            'status' => 'required|in:0,1',
        ];
        
        $validated = $request->validate($rules);
        // If legacy column exists, set safe default without requiring department module
        try {
            if (Schema::hasColumn('designations', 'department_id') && !array_key_exists('department_id', $validated)) {
                $validated['department_id'] = 0; // placeholder
            }
        } catch (\Exception $e) {}
        
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
                'category_id' => 'required|exists:complaint_categories,id',
                'name' => [
                    'required',
                    'string',
                    'max:100',
                    function ($attribute, $value, $fail) use ($request, $id) {
                        $query = Designation::where('name', $value)
                            ->where('status', 1)
                            ->where('id', '!=', $id)
                            ->where('category_id', $request->category_id);
                        
                        if ($query->exists()) {
                            $fail('The designation name has already been taken for this category.');
                        }
                    }
                ],
                'description' => 'nullable|string',
                'status' => 'required|in:0,1',
            ];
            
            $validated = $request->validate($rules);
            // If legacy column exists, set safe default without requiring department module
            try {
                if (Schema::hasColumn('designations', 'department_id') && !array_key_exists('department_id', $validated)) {
                    $validated['department_id'] = 0; // placeholder
                }
            } catch (\Exception $e) {}
            
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
                'status' => 0
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

