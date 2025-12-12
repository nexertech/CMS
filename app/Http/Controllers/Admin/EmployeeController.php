<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\ComplaintCategory;
use App\Models\Designation;
use App\Models\City;
use App\Models\Sector;
use App\Traits\LocationFilterTrait;
// Removed User and Role dependencies as employees no longer link to users
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Exception;

class EmployeeController extends Controller
{
    use LocationFilterTrait;
    public function __construct()
    {
        // Middleware is handled in routes/web.php
    }

    /**
     * Display a listing of the employees.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Employee::query();

        // Apply location-based filtering
        $this->filterEmployeesByLocation($query, $user);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $employees = $query->with(['city', 'sector'])->orderBy('id', 'asc')->paginate(10);
        
        // Get categories for filter dropdown from ComplaintCategory table
        $categories = collect();
        if (Schema::hasTable('complaint_categories')) {
            $categories = ComplaintCategory::orderBy('name')->pluck('name');
        } else {
            // Fallback: Get from employees with location filtering
            $categoriesQuery = Employee::select('category')
                ->whereNotNull('category')
                ->where('category', '!=', '');
            $this->filterEmployeesByLocation($categoriesQuery, $user);
            $categories = $categoriesQuery->distinct()
                ->orderBy('category')
                ->pluck('category');
        }
        
        return view('admin.employees.index', compact('employees', 'categories'));
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create()
    {
        // Clear any old input data to ensure clean form
        request()->session()->forget('_old_input');
        
        $categories = Schema::hasTable('complaint_categories')
            ? ComplaintCategory::orderBy('name')->pluck('name')
            : collect();
        
        $cities = Schema::hasTable('cities')
            ? City::where('status', 'active')->orderBy('id', 'asc')->get()
            : collect();
        
        $designations = Schema::hasTable('designations')
            ? Designation::where('status', 'active')->orderBy('name')->get()
            : collect();
        
        $response = response()->view('admin.employees.create', compact('categories', 'cities', 'designations'));
        
        // Add cache-busting headers
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(Request $request)
    {
        // Log incoming request data for debugging
        Log::info('Employee create request:', [
            'all_data' => $request->all(),
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
        ]);

        $categoryRule = 'required|string|max:100';
        
        if (Schema::hasTable('complaint_categories')) {
            $categoryRule .= '|exists:complaint_categories,name';
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'category' => $categoryRule,
            'designation' => 'required|string|max:100',
            'phone' => 'nullable|string|min:11|max:20',
            // 'emp_id' removed
            'date_of_hire' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'city_id' => 'required|exists:cities,id',
            'sector_id' => 'required|exists:sectors,id',
            'status' => 'nullable|in:active,inactive',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed:', $validator->errors()->toArray());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();
            Log::info('Starting employee creation transaction');

            // Create employee record (no user creation)
            $employee = Employee::create([
                'name' => $request->name,
                'category' => $request->category,
                'designation' => $request->designation,
                'phone' => $request->phone,
                // 'emp_id' removed
                'date_of_hire' => $request->date_of_hire,
                'address' => $request->address,
                'city_id' => $request->city_id,
                'sector_id' => $request->sector_id,
                'status' => $request->status ?? 'active',
            ]);
            Log::info('Employee created successfully with ID: ' . $employee->id);

            DB::commit();
            Log::info('Transaction committed successfully');

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Employee created successfully.',
                    'employee' => $employee
                ]);
            }

            return redirect()->route('admin.employees.index')
                ->with('success', 'Employee created successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating employee: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating employee: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Error creating employee: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified employee.
     */
    public function show(Employee $employee)
    {
        $employee->load(['city', 'sector']);
        
        if (request()->ajax() && request()->header('Accept') === 'text/html') {
            // Return HTML for modal
            return view('admin.employees.show', compact('employee'));
        }
        
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'employee' => $employee
            ]);
        }
        
        return view('admin.employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit(Employee $employee)
    {
        $categories = Schema::hasTable('complaint_categories')
            ? ComplaintCategory::orderBy('name')->pluck('name')
            : collect();
        
        $cities = Schema::hasTable('cities')
            ? City::where('status', 'active')->orderBy('id', 'asc')->get()
            : collect();
        
        $designations = Schema::hasTable('designations')
            ? Designation::where('status', 'active')->orderBy('name')->get()
            : collect();
        
        return view('admin.employees.edit', compact('employee', 'categories', 'cities', 'designations'));
    }


    /**
     * Get designations by category (AJAX)
     */
    public function getDesignationsByCategory(Request $request)
    {
        if (!Schema::hasTable('designations')) {
            return response()->json(['designations' => []]);
        }

        $category = $request->input('category');
        
        if (!$category) {
            return response()->json(['designations' => []]);
        }

        // Get active designations for this category
        $designations = Designation::where('category', $category)
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name']);
        
        return response()->json(['designations' => $designations]);
    }

    /**
     * Get sectors by city (AJAX)
     */
    public function getSectorsByCity(Request $request)
    {
        if (!Schema::hasTable('sectors')) {
            return response()->json(['sectors' => []]);
        }

        $cityId = $request->input('city_id');
        
        // If city_name is provided instead of city_id, find the city
        if ($request->has('city_name') && $request->city_name && !$cityId) {
            $city = City::where('name', $request->city_name)->first();
            if ($city) {
                $cityId = $city->id;
            }
        }

        // Convert to integer if it's a string
        if ($cityId) {
            $cityId = (int) $cityId;
        }

        if (!$cityId || $cityId <= 0) {
            Log::info('No valid city ID provided', [
                'city_id' => $request->input('city_id'),
                'city_name' => $request->input('city_name'),
                'all_request' => $request->all()
            ]);
            return response()->json(['sectors' => []]);
        }

        // Check if city exists
        $city = City::find($cityId);
        if (!$city) {
            Log::warning('City not found', ['city_id' => $cityId]);
            return response()->json(['sectors' => []]);
        }

        // Explicitly filter by city_id - ensure only sectors for this city are returned
        $sectors = Sector::where('city_id', '=', $cityId)
            ->where('status', '=', 'active')
            ->orderBy('id', 'asc')
            ->get(['id', 'name']);
        
        // Log all sectors in database for debugging (remove in production)
        $allSectors = Sector::select('id', 'name', 'city_id', 'status')->get();
        Log::info('Sectors fetched', [
            'requested_city_id' => $cityId,
            'city_name' => $city->name,
            'filtered_sectors_count' => $sectors->count(),
            'all_sectors_in_db' => $allSectors->toArray()
        ]);
        
        return response()->json(['sectors' => $sectors]);
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $categoryRule = 'required|string|max:100';
        
        if (Schema::hasTable('complaint_categories')) {
            $categoryRule .= '|exists:complaint_categories,name';
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:150',
            'phone' => 'nullable|string|min:11|max:20',
            'category' => $categoryRule,
            'designation' => 'required|string|max:100',
            // 'emp_id' removed
            'date_of_hire' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'city_id' => 'required|exists:cities,id',
            'sector_id' => 'required|exists:sectors,id',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Update employee
            $employee->update([
                'name' => $request->name,
                'category' => $request->category,
                'designation' => $request->designation ?? $employee->designation,
                'phone' => $request->phone,
                // 'emp_id' removed
                'date_of_hire' => $request->date_of_hire,
                'address' => $request->address,
                'city_id' => $request->city_id,
                'sector_id' => $request->sector_id,
                'status' => $request->status,
            ]);

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Employee updated successfully.',
                    'employee' => $employee
                ]);
            }

            return redirect()->route('admin.employees.index')
                ->with('success', 'Employee updated successfully.');
                
        } catch (Exception $e) {
            DB::rollBack();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating employee: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Error updating employee: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified employee from storage.
     */
    public function destroy($id)
    {
        try {
            // Find employee by ID instead of using route model binding
            $employee = Employee::findOrFail($id);
            
            Log::info('Attempting to soft delete employee ID: ' . $employee->id);
            
            // Soft delete - no need to check for related records as soft delete preserves them
            $employee->delete(); // This will now soft delete due to SoftDeletes trait
            Log::info('Employee soft deleted successfully for ID: ' . $employee->id);

            // Check if request expects JSON response
            if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => true,
                    'message' => 'Employee deleted successfully.'
                ]);
            }

            return redirect()->route('admin.employees.index')
                ->with('success', 'Employee deleted successfully.');

        } catch (Exception $e) {
            Log::error('Error deleting employee ID ' . ($employee->id ?? $id) . ': ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting employee: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Error deleting employee: ' . $e->getMessage());
        }
    }

    /**
     * Get employee data for editing (AJAX)
     */
    public function getEditData(Employee $employee)
    {
        return response()->json([
            'success' => true,
            'employee' => $employee
        ]);
    }

    /**
     * Toggle employee status
     */
    public function toggleStatus(Employee $employee)
    {
        try {
            $newStatus = $employee->status === 'active' ? 'inactive' : 'active';
            $employee->update(['status' => $newStatus]);

            return response()->json([
                'success' => true,
                'message' => 'Employee status updated successfully.',
                'new_status' => $newStatus
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating employee status: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Get employee performance
     */
    public function getPerformance(Employee $employee)
    {
        $metrics = $employee->getPerformanceMetrics();
        
        return response()->json([
            'success' => true,
            'metrics' => $metrics
        ]);
    }

    /**
     * Bulk action for employees
     */
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|string|in:delete,activate,deactivate',
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $employees = Employee::whereIn('id', $request->employee_ids);
            
            switch ($request->action) {
                case 'delete':
                    $employees->delete();
                    $message = 'Selected employees deleted successfully.';
                    break;
                case 'activate':
                    $employees->update(['status' => 'active']);
                    $message = 'Selected employees activated successfully.';
                    break;
                case 'deactivate':
                    $employees->update(['status' => 'inactive']);
                    $message = 'Selected employees deactivated successfully.';
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error performing bulk action: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export employees
     */
    public function export()
    {
        // Implementation for exporting employees
        return response()->json([
            'success' => true,
            'message' => 'Export functionality will be implemented.'
        ]);
    }
}
