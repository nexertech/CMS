<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Spare;
use App\Models\SpareStockLog;
use App\Models\ComplaintSpare;
use App\Models\ComplaintCategory;
use App\Models\City;
use App\Models\Sector;
use App\Models\StockAddData;
use App\Models\Complaint;
use App\Models\SpareApprovalPerforma;
use App\Models\SpareApprovalItem;
use App\Traits\LocationFilterTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class SpareController extends Controller
{
    use LocationFilterTrait;

    public function __construct()
    {
        // Middleware is applied in routes
    }

    /**
     * Display a listing of spare parts
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Spare::query();

        // Apply location-based filtering
        $this->filterSparesByLocation($query, $user);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        // Filter by stock status
        if ($request->has('stock_status') && $request->stock_status) {
            switch ($request->stock_status) {
                case 'in_stock':
                    $query->inStock();
                    break;
                case 'low_stock':
                    $query->lowStock();
                    break;
                case 'out_of_stock':
                    $query->outOfStock();
                    break;
            }
        }

        // Filter by price range
        if ($request->has('price_from') && $request->price_from) {
            $query->where('unit_price', '>=', $request->price_from);
        }

        if ($request->has('price_to') && $request->price_to) {
            $query->where('unit_price', '<=', $request->price_to);
        }

        $spares = $query->with(['stockLogs', 'city', 'sector'])->orderBy('id', 'asc')->paginate(15);
        
        // Get categories from complaint_categories table
        $dbCategories = Schema::hasTable('complaint_categories')
            ? ComplaintCategory::orderBy('name')->pluck('name')->toArray()
            : [];
        
        // Get unique categories from spares table (with location filtering)
        $spareCategoriesQuery = Spare::whereNotNull('category')
            ->where('category', '!=', '');
        $this->filterSparesByLocation($spareCategoriesQuery, $user);
        $spareCategories = $spareCategoriesQuery->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->toArray();
        
        // Merge and get unique categories
        $allCategories = array_unique(array_merge($dbCategories, $spareCategories));
        sort($allCategories);
        
        $categories = collect($allCategories);

        return view('admin.spares.index', compact('spares', 'categories'));
    }

    /**
     * Show the form for creating a new spare part
     */
    public function create()
    {
        $user = Auth::user();
        $categories = Schema::hasTable('complaint_categories')
            ? ComplaintCategory::orderBy('name')->pluck('name')
            : collect();
        
        // Get cities and sectors based on user role
        $cities = Schema::hasTable('cities')
            ? City::where('status', 'active')->orderBy('id', 'asc')->get()
            : collect();
        
        $sectors = collect();
        // If user has city_id, show only sectors from that city
        if ($user && $user->city_id) {
            $sectors = Schema::hasTable('sectors')
                ? Sector::where('city_id', $user->city_id)
                    ->where('status', 'active')
                    ->orderBy('name')
                    ->get()
                : collect();
        }
        // Defaults for Department Staff: preselect their city and sector
        $defaultCityId = null;
        $defaultSectorId = null;
        if ($user && $user->role && strtolower($user->role->role_name) === 'department_staff') {
            $defaultCityId = $user->city_id;
            $defaultSectorId = $user->sector_id;
        }
        
        return view('admin.spares.create', compact('categories', 'cities', 'sectors', 'defaultCityId', 'defaultSectorId'));
    }

    /**
     * Store a newly created spare part
     */
    public function store(Request $request)
    {
        // Get categories from ComplaintCategory table
        $categories = Schema::hasTable('complaint_categories')
            ? ComplaintCategory::orderBy('name')->pluck('name')->toArray()
            : [];
        $categoryKeys = implode(',', array_keys(Spare::getCategories()));
        $dbCategories = implode(',', Spare::getCanonicalCategories());
        $allowedCategories = implode(',', array_merge($categories, array_keys(Spare::getCategories()), Spare::getCanonicalCategories()));
        
        $validator = Validator::make($request->all(), [
            'item_name' => 'required|string|max:150',
            'product_code' => 'nullable|string|max:50',
            'brand_name' => 'nullable|string|max:100',
            // Accept categories from ComplaintCategory table and legacy categories
            'category' => 'required|string',
            'city_id' => 'nullable|exists:cities,id',
            'sector_id' => 'nullable|exists:sectors,id',
            'unit_price' => 'nullable|numeric|min:0',
            'total_received_quantity' => 'nullable|integer|min:0',
            'issued_quantity' => 'nullable|integer|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'threshold_level' => 'nullable|integer|min:0',
            'supplier' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'last_stock_in_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ]);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Validate category is from allowed list
        if (!in_array($request->category, array_merge($categories, array_keys(Spare::getCategories()), Spare::getCanonicalCategories()))) {
            return redirect()->back()
                ->withErrors(['category' => 'The selected category is invalid.'])
                ->withInput();
        }

        // Use category as is (from ComplaintCategory table)
        // Since category is now a string column, we can save any value directly
        $normalizedCategory = $request->category;

        // Check if same product (item_name) exists (any brand)
        // If exists, update it; if brand different, update brand and track old brand
        $user = Auth::user();
        $existingSpareQuery = Spare::where('item_name', $request->item_name);
        
        // Apply location filtering
        $this->filterSparesByLocation($existingSpareQuery, $user);
        
        $existingSpare = $existingSpareQuery->first();

        if ($existingSpare) {
            // Same product exists, update it (even if brand is different)
            $newStockQty = (int)($request->stock_quantity ?? 0);
            $oldBrandName = $existingSpare->brand_name;
            $newBrandName = $request->brand_name;
            $brandChanged = !empty($oldBrandName) && !empty($newBrandName) && $oldBrandName !== $newBrandName;
            
            // If brand changed, log the old brand summary before updating
            if ($brandChanged) {
                // Get old brand history summary
                $oldBrandInLogs = $existingSpare->stockLogs()
                    ->where('change_type', 'in')
                    ->where('brand_name', $oldBrandName)
                    ->orderBy('created_at', 'asc')
                    ->get();
                
                $oldBrandTotalReceived = $oldBrandInLogs->sum('quantity');
                $oldBrandStartDate = $oldBrandInLogs->first() ? $oldBrandInLogs->first()->created_at : $existingSpare->created_at;
                $oldBrandEndDate = $oldBrandInLogs->last() ? $oldBrandInLogs->last()->created_at : now();
                
                // Calculate used quantity during old brand period
                $oldBrandOutLogs = $existingSpare->stockLogs()
                    ->where('change_type', 'out')
                    ->whereBetween('created_at', [$oldBrandStartDate, $oldBrandEndDate])
                    ->get();
                $oldBrandUsed = $oldBrandOutLogs->sum('quantity');
                
                // Get supplier
                $oldSupplier = $existingSpare->supplier ?? 'N/A';
                
                // Log brand change with old brand summary
                SpareStockLog::create([
                    'spare_id' => $existingSpare->id,
                    'change_type' => 'in',
                    'quantity' => 0, // No quantity change, just brand change
                    'brand_name' => $oldBrandName,
                    'remarks' => "Brand Changed: Old Brand '{$oldBrandName}' Summary - Total Received: {$oldBrandTotalReceived}, Used: {$oldBrandUsed}, Start: {$oldBrandStartDate->format('d M Y, h:i A')}, End: {$oldBrandEndDate->format('d M Y, h:i A')}, Supplier: {$oldSupplier}",
                ]);
            }
            
            // If brand changed, reset issued_quantity to 0 for new brand
            $updateData = [
                'product_code' => $request->product_code ?? $existingSpare->product_code,
                'brand_name' => $newBrandName ?? $existingSpare->brand_name, // Update brand if changed
                'category' => $request->category,
                'city_id' => $request->city_id ?? $existingSpare->city_id,
                'sector_id' => $request->sector_id ?? $existingSpare->sector_id,
                'unit_price' => $request->unit_price ?? $existingSpare->unit_price,
                'total_received_quantity' => $existingSpare->total_received_quantity + $newStockQty,
                'stock_quantity' => $existingSpare->stock_quantity + $newStockQty,
                'threshold_level' => $request->threshold_level ?? $existingSpare->threshold_level,
                'supplier' => $request->supplier ?? $existingSpare->supplier,
                'description' => $request->description ?? $existingSpare->description,
                'last_stock_in_at' => now(),
            ];
            
            // Reset issued_quantity to 0 when brand changes
            if ($brandChanged) {
                $updateData['issued_quantity'] = 0;
            }
            
            $existingSpare->update($updateData);

            // Log the stock addition with new brand
            if ($newStockQty > 0) {
                SpareStockLog::create([
                    'spare_id' => $existingSpare->id,
                    'change_type' => 'in',
                    'quantity' => $newStockQty,
                    'brand_name' => $newBrandName ?? $existingSpare->brand_name,
                    'remarks' => $brandChanged ? "New brand '{$newBrandName}' stock added" : 'Stock added to existing product',
                ]);
            }

            $spare = $existingSpare;
            $message = $brandChanged ? "Product updated with new brand '{$newBrandName}'. Old brand '{$oldBrandName}' history preserved." : 'Stock added to existing product successfully.';
        } else {
            // Different brand or new product, create new record
            $spare = Spare::create([
                'item_name' => $request->item_name,
                'product_code' => $request->product_code,
                'brand_name' => $request->brand_name,
                'category' => $request->category,
                'city_id' => $request->city_id,
                'sector_id' => $request->sector_id,
                'unit_price' => $request->unit_price,
                'total_received_quantity' => (int)($request->total_received_quantity ?? $request->stock_quantity ?? 0),
                'issued_quantity' => (int)($request->issued_quantity ?? 0),
                'stock_quantity' => (int)($request->stock_quantity ?? 0),
                'threshold_level' => (int)($request->threshold_level ?? 0),
                'supplier' => $request->supplier,
                'description' => $request->description,
                'last_stock_in_at' => $request->last_stock_in_at,
            ]);

            // Log initial stock with brand name
            if ($request->stock_quantity > 0) {
                SpareStockLog::create([
                    'spare_id' => $spare->id,
                    'change_type' => 'in',
                    'quantity' => $request->stock_quantity,
                    'brand_name' => $request->brand_name ?? $spare->brand_name,
                    'remarks' => 'Initial stock',
                ]);
            }

            $message = 'Spare part created successfully.';
        }

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'spare' => $spare
            ]);
        }

        return redirect()->route('admin.spares.index')
            ->with('success', $message);
    }

    /**
     * Display the specified spare part
     */
    public function show(Spare $spare)
    {
        if (request()->get('format') === 'html') {
            return view('admin.spares.show', compact('spare'));
        }

        if (request()->ajax() || request()->wantsJson() || request()->header('X-Requested-With') === 'XMLHttpRequest') {
            // Return JSON for modal
        return response()->json([
            'id' => $spare->id,
            'name' => $spare->item_name,
            'product_code' => $spare->product_code,
            'brand_name' => $spare->brand_name,
            'category' => $spare->category,
            'price' => $spare->unit_price,
            'total_received_quantity' => $spare->total_received_quantity,
            'issued_quantity' => $spare->issued_quantity,
            'stock_quantity' => $spare->stock_quantity,
            'threshold_level' => $spare->threshold_level,
            'supplier' => $spare->supplier,
            'description' => $spare->description,
            'last_stock_in_at' => $spare->last_stock_in_at ? $spare->last_stock_in_at->format('M d, Y H:i') : 'N/A',
            'updated_at' => $spare->updated_at ? $spare->updated_at->format('M d, Y H:i') : 'N/A',
            'status' => $spare->stock_quantity > 0 ? 'in_stock' : 'out_of_stock',
            'stock_status' => $spare->stock_quantity <= $spare->threshold_level ? 'low_stock' : 'normal',
        ]);
        }

        $spare->load(['complaintSpares.complaint.client', 'approvalItems.performa']);

        return view('admin.spares.show', compact('spare'));
    }

    /**
     * Print spare part slip
     */
    public function printSlip(Spare $spare)
    {
        $spare->load(['stockLogs', 'complaintSpares.complaint.client', 'approvalItems.performa']);
        
        return view('admin.spares.print-slip', compact('spare'));
    }

    /**
     * Show the form for editing the spare part
     */
    public function edit(Spare $spare)
    {
        $user = Auth::user();
        $categories = Schema::hasTable('complaint_categories')
            ? ComplaintCategory::orderBy('name')->pluck('name')
            : collect();
        
        // Get cities and sectors based on user role
        $cities = Schema::hasTable('cities')
            ? City::where('status', 'active')->orderBy('id', 'asc')->get()
            : collect();
        
        $sectors = collect();
        // If user has city_id or spare has city_id, show sectors from that city
        $cityId = $user && $user->city_id ? $user->city_id : ($spare->city_id ?? null);
        if ($cityId) {
            $sectors = Schema::hasTable('sectors')
                ? Sector::where('city_id', $cityId)
                    ->where('status', 'active')
                    ->orderBy('name')
                    ->get()
                : collect();
        }
        
        return view('admin.spares.edit', compact('spare', 'categories', 'cities', 'sectors'));
    }

    /**
     * Get spare data for editing (AJAX)
     */
    public function editData(Spare $spare)
    {
        return response()->json([
            'id' => $spare->id,
            'name' => $spare->item_name,
            'product_code' => $spare->product_code,
            'brand_name' => $spare->brand_name,
            'category' => $spare->category,
            'city_id' => $spare->city_id,
            'sector_id' => $spare->sector_id,
            'price' => $spare->unit_price,
            'total_received_quantity' => $spare->total_received_quantity,
            'issued_quantity' => $spare->issued_quantity,
            'stock_quantity' => $spare->stock_quantity,
            'threshold_level' => $spare->threshold_level,
            'supplier' => $spare->supplier ?? '',
            'description' => $spare->description ?? '',
            'last_stock_in_at' => $spare->last_stock_in_at,
            'status' => $spare->stock_quantity > 0 ? 'active' : 'inactive',
        ]);
    }

    /**
     * Update the specified spare part
     */
    public function update(Request $request, Spare $spare)
    {
        // Get categories from ComplaintCategory table
        $categories = Schema::hasTable('complaint_categories')
            ? ComplaintCategory::orderBy('name')->pluck('name')->toArray()
            : [];
        $categoryKeys = implode(',', array_keys(Spare::getCategories()));
        $dbCategories = implode(',', Spare::getCanonicalCategories());
        
        $validator = Validator::make($request->all(), [
            'item_name' => 'required|string|max:150',
            'product_code' => 'nullable|string|max:50',
            'brand_name' => 'nullable|string|max:100',
            // Accept categories from ComplaintCategory table and legacy categories
            'category' => 'required|string',
            'city_id' => 'nullable|exists:cities,id',
            'sector_id' => 'nullable|exists:sectors,id',
            'unit_price' => 'nullable|numeric|min:0',
            'total_received_quantity' => 'nullable|integer|min:0',
            'issued_quantity' => 'nullable|integer|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'threshold_level' => 'required|integer|min:0',
            'supplier' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'last_stock_in_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ]);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Validate category is from allowed list
        $allAllowedCategories = array_merge($categories, array_keys(Spare::getCategories()), Spare::getCanonicalCategories());
        if (!in_array($request->category, $allAllowedCategories)) {
            return redirect()->back()
                ->withErrors(['category' => 'The selected category is invalid.'])
                ->withInput();
        }

        // Use category as is (from ComplaintCategory table)
        // Since category is now a string column, we can save any value directly
        $normalizedCategory = $request->category;

        // Compute safe values
        $newTotalReceived = $request->has('total_received_quantity')
            ? (int) $request->total_received_quantity
            : $spare->total_received_quantity;

        $newIssued = $request->has('issued_quantity')
            ? (int) $request->issued_quantity
            : $spare->issued_quantity;

        // If stock_quantity not explicitly provided but totals changed, auto-balance
        $newStock = $request->has('stock_quantity')
            ? (int) $request->stock_quantity
            : (($request->has('total_received_quantity') || $request->has('issued_quantity'))
                ? max($newTotalReceived - $newIssued, 0)
                : $spare->stock_quantity);

        $spare->update([
            'item_name' => $request->item_name,
            'product_code' => $request->product_code,
            'brand_name' => $request->brand_name,
            'category' => $normalizedCategory,
            'city_id' => $request->city_id,
            'sector_id' => $request->sector_id,
            'unit_price' => $request->unit_price,
            'total_received_quantity' => $newTotalReceived,
            'issued_quantity' => $newIssued,
            'stock_quantity' => $newStock,
            'threshold_level' => $request->has('threshold_level') ? (int) $request->threshold_level : $spare->threshold_level,
            'supplier' => $request->supplier,
            'description' => $request->description,
            'last_stock_in_at' => $request->last_stock_in_at,
        ]);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Spare part updated successfully.',
                'spare' => $spare
            ]);
        }

        return redirect()->route('admin.spares.index')
            ->with('success', 'Spare part updated successfully.');
    }

    /**
     * Remove the specified spare part
     */
    public function destroy(Spare $spare)
    {
        // Soft delete - no need to manually delete related records as soft delete preserves them
        try {
            $spare->delete(); // This will now soft delete due to SoftDeletes trait

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Spare part deleted successfully.'
                ]);
            }

            return redirect()->route('admin.spares.index')
                ->with('success', 'Spare part deleted successfully.');
        } catch (\Throwable $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete spare part. Please try again.',
                ], 500);
            }

            return redirect()->back()->with('error', 'Failed to delete spare part. Please try again.');
        }
    }

    /**
     * Add stock to spare part
     */
    public function addStock(Request $request, Spare $spare)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string',
            'reference_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        // Get available stock before adding
        $availableStockBefore = $spare->stock_quantity;
        
        $spare->addStock(
            $request->quantity,
            $request->remarks,
            $request->reference_id
        );

        // Reload spare to get updated stock quantity
        $spare->refresh();

        // Create stock add data record
        try {
            StockAddData::create([
                'spare_id' => $spare->id,
                'add_date' => now(),
                'category' => $spare->category,
                'product_name' => $spare->item_name,
                'quantity_added' => $request->quantity,
                'available_stock_after' => $spare->stock_quantity,
                'remarks' => $request->remarks,
                'added_by' => auth()->user() && auth()->user()->employee ? auth()->user()->employee->id : null,
                'reference_id' => $request->reference_id,
            ]);
        } catch (\Exception $e) {
            \Log::warning('Failed to create stock add data: ' . $e->getMessage());
        }

        return redirect()->back()
            ->with('success', 'Stock added successfully.');
    }

    /**
     * Remove stock from spare part
     */
    public function removeStock(Request $request, Spare $spare)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
            'remarks' => 'nullable|string',
            'reference_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        if (!$spare->isStockSufficient($request->quantity)) {
            return redirect()->back()
                ->with('error', 'Insufficient stock available.');
        }

        $spare->removeStock(
            $request->quantity,
            $request->remarks,
            $request->reference_id
        );

        return redirect()->back()
            ->with('success', 'Stock removed successfully.');
    }

    /**
     * Issue stock from spare part (for AJAX requests)
     */
    public function issueStock(Request $request, Spare $spare)
    {
        try {
            $validator = Validator::make($request->all(), [
                'quantity' => 'required|integer|min:1',
                'remarks' => 'nullable|string',
                'item_id' => 'nullable|integer',
                'approval_id' => 'nullable|integer',
                'reason' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $quantity = (int)$request->quantity;

            // Check if stock is sufficient
            if (!$spare->isStockSufficient($quantity)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient stock available. Available: ' . $spare->stock_quantity . ', Requested: ' . $quantity
                ], 400);
            }

            // Use reason or remarks for stock log
            $remarks = $request->reason ?? $request->remarks ?? 'Stock issued from approval';
            
            // Get approval_id from request (set when issuing from approval modal)
            $approvalId = $request->approval_id ?? null;
            
            // Get complaint_id from request or from approval
            $complaintId = $request->complaint_id ?? null;
            
            // Debug logging
            \Log::info('Stock issue request received', [
                'spare_id' => $spare->id,
                'approval_id' => $approvalId,
                'complaint_id' => $complaintId,
                'item_id' => $request->item_id ?? null,
                'quantity' => $quantity,
                'all_request' => $request->all()
            ]);

            // Get available stock before issuing
            $availableStockBefore = $spare->stock_quantity;
            
            // Get complaint and approval details
            $complaint = null;
            $approval = null;
            $approvalItem = null;
            $requestedQty = 0;
            
            // First try to get approval if approval_id is provided
            if ($approvalId) {
                $approval = SpareApprovalPerforma::find($approvalId);
                if ($approval) {
                    $complaint = $approval->complaint;
                    // Set complaint_id from approval if not already set
                    if ($complaint && !$complaintId) {
                        $complaintId = $complaint->id;
                    }
                }
            }
            
            // If complaint_id is provided, get complaint directly
            if ($complaintId && !$complaint) {
                $complaint = Complaint::find($complaintId);
            }
            
            // Try to get complaint from item_id if not already found
            $itemId = $request->item_id ?? null;
            if (!$complaint && $itemId) {
                $approvalItem = SpareApprovalItem::find($itemId);
                if ($approvalItem && $approvalItem->performa) {
                    $approval = $approvalItem->performa;
                    $complaint = $approval->complaint;
                    if ($complaint && !$complaintId) {
                        $complaintId = $complaint->id;
                    }
                }
            }
            
            // Get approval item to find requested quantity
            if ($spare && $complaint) {
                if (!$approvalItem) {
                    $approvalItem = SpareApprovalItem::whereHas('performa', function($q) use ($complaint) {
                        $q->where('complaint_id', $complaint->id);
                    })->where('spare_id', $spare->id)->first();
                }
                
                if ($approvalItem) {
                    $requestedQty = $approvalItem->quantity_requested;
                    if (!$approval) {
                        $approval = $approvalItem->performa;
                    }
                }
            }
            
            // If approval_id was provided but approval not found, try to find it
            if ($approvalId && !$approval) {
                $approval = SpareApprovalPerforma::find($approvalId);
                if ($approval && !$complaint) {
                    $complaint = $approval->complaint;
                    if ($complaint && !$complaintId) {
                        $complaintId = $complaint->id;
                    }
                }
            }

            // Set reference_id to complaint_id (for stock logs to link to complaint)
            $referenceId = $complaintId ?? $itemId ?? $approvalId ?? null;

            // Issue stock (decrease inventory)
            $result = $spare->removeStock(
                $quantity,
                $remarks,
                $referenceId
            );

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to issue stock. Please try again.'
                ], 500);
            }

            // Reload spare to get updated stock quantity
            $spare->refresh();

            // Create stock approval data record if approval_id is provided (from approval modal)
            // Convert approval_id to integer if it's a string
            $finalApprovalId = null;
            if ($approvalId) {
                $finalApprovalId = is_numeric($approvalId) ? (int)$approvalId : $approvalId;
                \Log::info('Approval ID from request', ['approval_id' => $approvalId, 'final_approval_id' => $finalApprovalId]);
            } elseif ($approval && $approval->id) {
                $finalApprovalId = $approval->id;
                \Log::info('Approval ID from approval object', ['approval_id' => $finalApprovalId]);
            }
            
            \Log::info('Final approval ID check', [
                'final_approval_id' => $finalApprovalId,
                'approval_id_from_request' => $approvalId,
                'approval_object_exists' => $approval ? true : false
            ]);
            
            
            // Save authority number to approval if present in remarks
            if ($finalApprovalId && $approval) {
                try {
                    // Extract authority number from remarks if present
                    $authorityNumber = null;
                    if ($remarks && preg_match('/Authority\s*No\.?\s*:\s*([A-Za-z0-9\-]+)/i', $remarks, $matches)) {
                        $authorityNumber = trim($matches[1]);
                    }
                    
                    // Update approval with authority number if extracted
                    if ($authorityNumber) {
                        DB::table('spare_approval_performa')
                            ->where('id', $finalApprovalId)
                            ->update(['authority_number' => $authorityNumber]);
                        
                        \Log::info('Authority number saved to approval', [
                            'approval_id' => $finalApprovalId,
                            'authority_number' => $authorityNumber
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::warning('Failed to save authority number to approval', [
                        'approval_id' => $finalApprovalId,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Auto-update complaint status to "in_progress" if stock is issued from approval
            if ($complaint && ($complaint->status === 'assigned' || $complaint->status === 'new')) {
                try {
                    $complaint->status = 'in_progress';
                    $complaint->save();
                    \Log::info('Complaint status auto-updated to in_progress after stock issue', [
                        'complaint_id' => $complaint->id,
                        'approval_id' => $finalApprovalId
                    ]);
                } catch (\Exception $e) {
                    \Log::warning('Failed to auto-update complaint status', [
                        'complaint_id' => $complaint->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // waiting_for_authority removed - stock is issued directly when authority code is added
            // No need to check or update waiting_for_authority flag

            return response()->json([
                'success' => true,
                'message' => 'Stock issued successfully',
                'data' => [
                    'spare_id' => $spare->id,
                    'item_name' => $spare->item_name,
                    'quantity_issued' => $quantity,
                    'remaining_stock' => $spare->stock_quantity,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error issuing stock', [
                'spare_id' => $spare->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while issuing stock: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get low stock items
     */
    public function getLowStock()
    {
        $lowStockItems = Spare::lowStock()
            ->orderBy('stock_quantity', 'asc')
            ->get();

        return response()->json($lowStockItems);
    }

    /**
     * Get out of stock items
     */
    public function getOutOfStock()
    {
        $outOfStockItems = Spare::outOfStock()
            ->orderBy('item_name')
            ->get();

        return response()->json($outOfStockItems);
    }

    /**
     * Get stock alerts
     */
    public function getStockAlerts()
    {
        $alerts = [
            'low_stock' => Spare::lowStock()->count(),
            'out_of_stock' => Spare::outOfStock()->count(),
            'items' => Spare::lowStock()
                ->orWhere('stock_quantity', '<=', 0)
                ->orderBy('stock_quantity', 'asc')
                ->limit(10)
                ->get()
        ];

        return response()->json($alerts);
    }

    /**
     * Get stock movement chart data
     */
    public function getStockMovementChart(Spare $spare, Request $request)
    {
        $days = $request->get('days', 30);

        $data = $spare->stockLogs()
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, change_type, SUM(quantity) as total_quantity')
            ->groupBy('date', 'change_type')
            ->orderBy('date')
            ->get();

        return response()->json($data);
    }

    /**
     * Get usage statistics
     */
    public function getUsageStatistics(Spare $spare, Request $request)
    {
        $period = $request->get('period', '30'); // days

        $usage = $spare->complaintSpares()
            ->where('used_at', '>=', now()->subDays($period))
            ->selectRaw('DATE(used_at) as date, SUM(quantity) as total_quantity, SUM(quantity * (SELECT unit_price FROM spares WHERE id = complaint_spares.spare_id)) as total_cost')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($usage);
    }

    /**
     * Get top used spares
     */
    public function getTopUsedSpares(Request $request)
    {
        $period = $request->get('period', '30'); // days

        $topUsed = Spare::join('complaint_spares', 'spares.id', '=', 'complaint_spares.spare_id')
            ->where('complaint_spares.used_at', '>=', now()->subDays($period))
            ->selectRaw('spares.*, SUM(complaint_spares.quantity) as total_used, SUM(complaint_spares.quantity * spares.unit_price) as total_cost')
            ->groupBy('spares.id')
            ->orderBy('total_used', 'desc')
            ->limit(10)
            ->get();

        return response()->json($topUsed);
    }

    /**
     * Get category-wise statistics
     */
    public function getCategoryStatistics()
    {
        $user = Auth::user();
        $query = Spare::query();
        
        // Apply location-based filtering
        $this->filterSparesByLocation($query, $user);
        
        $stats = $query->selectRaw('category, COUNT(*) as count, SUM(stock_quantity) as total_stock, SUM(stock_quantity * unit_price) as total_value')
            ->groupBy('category')
            ->get();

        return response()->json($stats);
    }

    /**
     * Bulk actions on spares
     */
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:add_stock,remove_stock,change_category,change_threshold,delete',
            'spare_ids' => 'required|array|min:1',
            'spare_ids.*' => 'exists:spares,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $spareIds = $request->spare_ids;
        $action = $request->action;

        switch ($action) {
            case 'add_stock':
                $validator = Validator::make($request->all(), [
                    'quantity' => 'required|integer|min:1',
                    'remarks' => 'nullable|string',
                ]);
                
                if ($validator->fails()) {
                    return redirect()->back()->withErrors($validator);
                }
                
                Spare::whereIn('id', $spareIds)->get()->each(function($spare) use ($request) {
                    $spare->addStock($request->quantity, $request->remarks);
                });
                $message = 'Stock added to selected spare parts successfully.';
                break;

            case 'remove_stock':
                $validator = Validator::make($request->all(), [
                    'quantity' => 'required|integer|min:1',
                    'remarks' => 'nullable|string',
                ]);
                
                if ($validator->fails()) {
                    return redirect()->back()->withErrors($validator);
                }
                
                $spares = Spare::whereIn('id', $spareIds)->get();
                foreach ($spares as $spare) {
                    if ($spare->isStockSufficient($request->quantity)) {
                        $spare->removeStock($request->quantity, $request->remarks);
                    }
                }
                $message = 'Stock removed from selected spare parts successfully.';
                break;

            case 'change_category':
                $validCategories = Schema::hasTable('complaint_categories')
                    ? ComplaintCategory::orderBy('name')->pluck('name')->toArray()
                    : [];
                $validator = Validator::make($request->all(), [
                    'category' => 'required|string|max:100' . (!empty($validCategories) ? '|in:' . implode(',', $validCategories) : ''),
                ]);
                
                if ($validator->fails()) {
                    return redirect()->back()->withErrors($validator);
                }
                
                Spare::whereIn('id', $spareIds)->update(['category' => $request->category]);
                $message = 'Category changed for selected spare parts successfully.';
                break;

            case 'change_threshold':
                $validator = Validator::make($request->all(), [
                    'threshold_level' => 'required|integer|min:0',
                ]);
                
                if ($validator->fails()) {
                    return redirect()->back()->withErrors($validator);
                }
                
                Spare::whereIn('id', $spareIds)->update(['threshold_level' => $request->threshold_level]);
                $message = 'Threshold level changed for selected spare parts successfully.';
                break;

            case 'delete':
                // Check for related records
                $sparesWithRecords = Spare::whereIn('id', $spareIds)
                    ->where(function($q) {
                        $q->whereHas('complaintSpares')
                          ->orWhereHas('approvalItems');
                    })
                    ->count();

                if ($sparesWithRecords > 0) {
                    return redirect()->back()
                        ->with('error', 'Some spare parts cannot be deleted due to existing usage records.');
                }

                Spare::whereIn('id', $spareIds)->delete();
                $message = 'Selected spare parts deleted successfully.';
                break;
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Export spares data
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $query = Spare::query();
        
        // Apply location-based filtering
        $this->filterSparesByLocation($query, $user);

        // Apply same filters as index
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->has('category') && $request->category) {
            $query->where('category', $request->category);
        }

        $spares = $query->get();

        // Implementation for export
        return response()->json(['message' => 'Export functionality not implemented yet']);
    }

    /**
     * Get all categories for dropdown
     */
    public function getCategories(Request $request)
    {
        try {
            // Get categories from ComplaintCategory table
            $dbCategories = [];
            if (\Schema::hasTable('complaint_categories')) {
                $dbCategories = \App\Models\ComplaintCategory::orderBy('name')->pluck('name')->toArray();
            }
            
            // Get unique categories from spares table
            $spareCategories = Spare::whereNotNull('category')
                ->where('category', '!=', '')
                ->distinct()
                ->orderBy('category')
                ->pluck('category')
                ->toArray();
            
            // Merge and get unique categories
            $allCategories = array_unique(array_merge($dbCategories, $spareCategories));
            sort($allCategories);
            
            return response()->json([
                'success' => true,
                'categories' => $allCategories
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting categories', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading categories: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get products by category
     */
    public function getProductsByCategory(Request $request)
    {
        try {
            $category = $request->get('category');
            $sectorName = $request->get('sector');
            $cityName = $request->get('city');
            $user = Auth::user();
            
            $query = Spare::query();
            
            // First apply user-based location filtering (this is the primary filter)
            $this->filterSparesByLocation($query, $user);
            
            // Only apply additional location filtering from request if user is Director/Admin
            // For other roles, user-based filtering is already applied and should not be overridden
            if ($user && $user->role) {
                $roleName = strtolower($user->role->role_name ?? '');
                
                // Only Director/Admin can override location filtering with request parameters
                if (in_array($roleName, ['director', 'admin'])) {
                    // Then apply additional location filtering based on request parameters (if provided)
                    if ($sectorName) {
                        // Find sector by name (handle both string name and object)
                        $sectorNameStr = is_string($sectorName) ? $sectorName : (is_object($sectorName) ? ($sectorName->name ?? null) : null);
                        
                        if ($sectorNameStr) {
                            $sector = \App\Models\Sector::where('name', $sectorNameStr)->first();
                            if ($sector && $sector->id) {
                                $query->where('sector_id', $sector->id);
                            } else {
                                // If sector not found, try to find by ID if it's numeric
                                if (is_numeric($sectorName)) {
                                    $query->where('sector_id', $sectorName);
                                }
                            }
                        }
                    } elseif ($cityName) {
                        // If only city is provided, filter by city_id
                        // Handle both string name and object
                        $cityNameStr = is_string($cityName) ? $cityName : (is_object($cityName) ? ($cityName->name ?? null) : null);
                        
                        if ($cityNameStr) {
                            $city = \App\Models\City::where('name', $cityNameStr)->first();
                            if ($city && $city->id) {
                                $query->where('city_id', $city->id);
                            } else {
                                // If city not found, try to find by ID if it's numeric
                                if (is_numeric($cityName)) {
                                    $query->where('city_id', $cityName);
                                }
                            }
                        }
                    }
                }
                // For other roles (complaint_center, department_staff, garrison_engineer), 
                // user-based filtering is already applied and request parameters are ignored
            }
            
            // Apply category filter
            if ($category && $category !== '') {
                $query->where(function($q) use ($category) {
                    $q->where('category', $category);
                    // Also include products with null or empty category if searching for "Uncategorized"
                    if (strtolower($category) === 'uncategorized' || $category === '') {
                        $q->orWhereNull('category')
                          ->orWhere('category', '');
                    }
                });
            }
            
            $products = $query->orderBy('item_name')
                ->get(['id', 'item_name', 'stock_quantity', 'category', 'unit_price']);
            
            return response()->json([
                'success' => true,
                'products' => $products
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting products by category', [
                'category' => $request->get('category'),
                'sector' => $request->get('sector'),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error loading products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get product stock history grouped by brand
     */
    public function getProductHistory(Spare $spare)
    {
        try {
            // Get all stock in logs for this product, ordered by date
            $stockLogs = $spare->stockLogs()
                ->where('change_type', 'in')
                ->orderBy('created_at', 'desc')
                ->get();

            // Group by brand and prepare history data
            $historyByBrand = [];
            $allHistory = [];

            foreach ($stockLogs as $log) {
                // Use log's brand_name if available, otherwise use spare's current brand_name, or 'N/A'
                $brandName = $log->brand_name;
                if (empty($brandName)) {
                    $brandName = $spare->brand_name ?? 'N/A';
                }
                $date = $log->created_at->format('Y-m-d H:i:s');
                $quantity = $log->quantity;
                $remarks = $log->remarks ?? '';

                // Add to all history
                $allHistory[] = [
                    'id' => $log->id,
                    'brand_name' => $brandName,
                    'quantity' => $quantity,
                    'date' => $date,
                    'formatted_date' => $log->created_at->format('d M Y, h:i A'),
                    'remarks' => $remarks,
                ];

                // Group by brand
                if (!isset($historyByBrand[$brandName])) {
                    $historyByBrand[$brandName] = [
                        'brand_name' => $brandName,
                        'total_quantity' => 0,
                        'entries' => [],
                        'first_entry_date' => $date,
                        'last_entry_date' => $date,
                    ];
                }

                $historyByBrand[$brandName]['total_quantity'] += $quantity;
                $historyByBrand[$brandName]['entries'][] = [
                    'id' => $log->id,
                    'quantity' => $quantity,
                    'date' => $date,
                    'formatted_date' => $log->created_at->format('d M Y, h:i A'),
                    'remarks' => $remarks,
                ];

                // Update dates
                if ($date < $historyByBrand[$brandName]['first_entry_date']) {
                    $historyByBrand[$brandName]['first_entry_date'] = $date;
                }
                if ($date > $historyByBrand[$brandName]['last_entry_date']) {
                    $historyByBrand[$brandName]['last_entry_date'] = $date;
                }
            }

            // Convert to array and sort by last entry date (most recent first)
            $historyByBrand = array_values($historyByBrand);
            usort($historyByBrand, function($a, $b) {
                return strtotime($b['last_entry_date']) - strtotime($a['last_entry_date']);
            });

            // Get old brand summaries (brands that are no longer current)
            $oldBrandSummaries = [];
            $currentBrand = $spare->brand_name ?? '';
            
            // Get all unique brands from stock logs
            $allBrandsInLogs = $spare->stockLogs()
                ->where('change_type', 'in')
                ->whereNotNull('brand_name')
                ->where('brand_name', '!=', '')
                ->distinct()
                ->pluck('brand_name')
                ->toArray();
            
            foreach ($allBrandsInLogs as $oldBrandName) {
                // Skip if this is the current brand
                if (!empty($currentBrand) && $oldBrandName === $currentBrand) {
                    continue;
                }
                
                // Skip if already processed in historyByBrand
                $alreadyInHistory = false;
                foreach ($historyByBrand as $brandData) {
                    if ($brandData['brand_name'] === $oldBrandName) {
                        $alreadyInHistory = true;
                        break;
                    }
                }
                
                // Get all logs for this old brand
                $oldBrandInLogs = $spare->stockLogs()
                    ->where('change_type', 'in')
                    ->where('brand_name', $oldBrandName)
                    ->orderBy('created_at', 'asc')
                    ->get();
                
                if ($oldBrandInLogs->isEmpty()) {
                    continue;
                }
                
                // Calculate used quantity - get all out logs that happened during old brand period
                $oldBrandStartDate = $oldBrandInLogs->first()->created_at;
                $oldBrandEndDate = $oldBrandInLogs->last()->created_at;
                
                // Get out logs between start and end date (approximate - all out logs during this period)
                $oldBrandOutLogs = $spare->stockLogs()
                    ->where('change_type', 'out')
                    ->whereBetween('created_at', [$oldBrandStartDate, $oldBrandEndDate])
                    ->get();
                
                $totalReceived = $oldBrandInLogs->sum('quantity');
                $totalUsed = $oldBrandOutLogs->sum('quantity');
                $startDate = $oldBrandInLogs->first()->created_at;
                $endDate = $oldBrandInLogs->last()->created_at;
                
                // Get supplier from spare (or from first log remarks if available)
                $supplier = $spare->supplier ?? 'N/A';
                // Try to extract supplier from remarks if available
                $firstLogRemarks = $oldBrandInLogs->first()->remarks ?? '';
                if (stripos($firstLogRemarks, 'Supplier:') !== false) {
                    preg_match('/Supplier:\s*([^,]+)/i', $firstLogRemarks, $matches);
                    if (!empty($matches[1])) {
                        $supplier = trim($matches[1]);
                    }
                }
                
                $oldBrandSummaries[] = [
                    'brand_name' => $oldBrandName,
                    'total_quantity_received' => $totalReceived,
                    'total_quantity_used' => $totalUsed,
                    'start_date' => $startDate->format('Y-m-d H:i:s'),
                    'start_date_formatted' => $startDate->format('d M Y, h:i A'),
                    'end_date' => $endDate->format('Y-m-d H:i:s'),
                    'end_date_formatted' => $endDate->format('d M Y, h:i A'),
                    'supplier' => $supplier,
                ];
            }

            // Get related brands (same item_name, different brands)
            $user = Auth::user();
            $relatedSparesQuery = Spare::where('item_name', $spare->item_name)
                ->where('id', '!=', $spare->id);
            $this->filterSparesByLocation($relatedSparesQuery, $user);
            $relatedSpares = $relatedSparesQuery->get();

            return response()->json([
                'success' => true,
                'product' => [
                    'id' => $spare->id,
                    'item_name' => $spare->item_name,
                    'product_code' => $spare->product_code,
                    'current_brand' => $spare->brand_name,
                    'category' => $spare->category,
                ],
                'history_by_brand' => $historyByBrand,
                'all_history' => $allHistory,
                'old_brand_summaries' => $oldBrandSummaries,
                'related_brands' => $relatedSpares->map(function($relatedSpare) {
                    return [
                        'id' => $relatedSpare->id,
                        'brand_name' => $relatedSpare->brand_name,
                        'product_code' => $relatedSpare->product_code,
                        'stock_quantity' => $relatedSpare->stock_quantity,
                        'total_received_quantity' => $relatedSpare->total_received_quantity,
                    ];
                })->values(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting product history', [
                'spare_id' => $spare->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error loading product history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all brands for same product name (item_name)
     */
    public function getProductBrands(Request $request)
    {
        try {
            $itemName = $request->get('item_name');
            $currentSpareId = $request->get('spare_id');
            
            if (!$itemName) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product name is required'
                ], 400);
            }

            $user = Auth::user();
            $query = Spare::where('item_name', $itemName);
            
            // Apply location filtering
            $this->filterSparesByLocation($query, $user);
            
            $brands = $query->whereNotNull('brand_name')
                ->where('brand_name', '!=', '')
                ->distinct()
                ->orderBy('brand_name')
                ->pluck('brand_name')
                ->toArray();

            // Get all spares with same item_name but different brands
            $relatedSpares = $query->get();

            return response()->json([
                'success' => true,
                'brands' => $brands,
                'related_spares' => $relatedSpares->map(function($spare) use ($currentSpareId) {
                    return [
                        'id' => $spare->id,
                        'item_name' => $spare->item_name,
                        'brand_name' => $spare->brand_name,
                        'product_code' => $spare->product_code,
                        'stock_quantity' => $spare->stock_quantity,
                        'total_received_quantity' => $spare->total_received_quantity,
                        'issued_quantity' => $spare->issued_quantity,
                        'is_current' => $spare->id == $currentSpareId,
                    ];
                })
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting product brands', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error loading product brands: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show old brand history page
     */
    public function showOldBrandHistory(Request $request, $itemName, $brandName)
    {
        try {
            $user = Auth::user();
            
            // Get all spares with same item_name
            $query = Spare::where('item_name', $itemName);
            $this->filterSparesByLocation($query, $user);
            
            $allSpares = $query->get();
            
            // Get the specific brand's spare
            $spare = $allSpares->where('brand_name', $brandName)->first();
            
            if (!$spare) {
                return redirect()->route('admin.spares.index')
                    ->with('error', 'Product with this brand not found.');
            }

            // Get all related brands (same item_name, different brands)
            $relatedSpares = $allSpares->where('brand_name', '!=', $brandName)->values();

            // Get stock history for this brand
            $stockLogs = $spare->stockLogs()
                ->where('change_type', 'in')
                ->orderBy('created_at', 'desc')
                ->get();

            // Group by brand
            $historyByBrand = [];
            foreach ($stockLogs as $log) {
                $logBrandName = $log->brand_name ?? $spare->brand_name ?? 'N/A';
                
                if (!isset($historyByBrand[$logBrandName])) {
                    $historyByBrand[$logBrandName] = [
                        'brand_name' => $logBrandName,
                        'total_quantity' => 0,
                        'entries' => [],
                    ];
                }
                
                $historyByBrand[$logBrandName]['total_quantity'] += $log->quantity;
                $historyByBrand[$logBrandName]['entries'][] = [
                    'id' => $log->id,
                    'quantity' => $log->quantity,
                    'date' => $log->created_at->format('Y-m-d H:i:s'),
                    'formatted_date' => $log->created_at->format('d M Y, h:i A'),
                    'remarks' => $log->remarks ?? '',
                ];
            }

            return view('admin.spares.old-brand-history', compact(
                'spare',
                'relatedSpares',
                'historyByBrand',
                'itemName',
                'brandName'
            ));
        } catch (\Exception $e) {
            \Log::error('Error showing old brand history', [
                'item_name' => $itemName,
                'brand_name' => $brandName,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('admin.spares.index')
                ->with('error', 'Error loading brand history: ' . $e->getMessage());
        }
    }
}
