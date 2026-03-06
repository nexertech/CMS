<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\City;
use App\Models\Sector;
use App\Models\ComplaintLog;
use App\Models\Category; // Assuming you have this or hardcoded
use App\Models\ComplaintTitle; // Assuming you have this
use App\Models\ComplaintFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ComplaintApiController extends Controller
{
    /**
     * Helper to get authenticated house from token or request
     * CRITICAL FIX: Never use $request->user() as it triggers session middleware
     */
    private function getAuthenticatedHouse(Request $request)
    {
        // CRITICAL: Always use manual token lookup to avoid session middleware
        $token = $request->bearerToken();
        if (!$token) {
            return null;
        }
        
        $personalAccessToken = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
        if (!$personalAccessToken || $personalAccessToken->tokenable_type !== \App\Models\House::class) {
            return null;
        }
        
        // Load House directly with only required columns to prevent memory leak
        return \App\Models\House::select('id', 'username', 'house_no', 'name', 'phone', 'city_id', 'sector_id', 'address', 'status', 'password_updated_at')
            ->find($personalAccessToken->tokenable_id);
    }

    /**
     * Get all complaints for the logged-in house
     */
    public function index(Request $request)
    {
        // Get house from manual.auth middleware
        $house = $request->input('authenticated_house') ?: $request->user();
        
        if (!$house) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Please login again.'
            ], 401);
        }
        
        $complaints = Complaint::where('house_id', $house->id)
            ->with(['assignedEmployee:id,name', 'category', 'complaintTitle']) // Load relations
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($complaint) {
                return [
                    'id' => $complaint->id,
                    'cmp' => $complaint->cmp, // Verify if this accessor exists, otherwise ticket_number
                    'ticket_number' => $complaint->ticket_number,
                    'category' => $complaint->category ? $complaint->category->name : 'Unknown',
                    'category_id' => $complaint->category_id,
                    'title' => $complaint->title ?? ($complaint->complaintTitle ? $complaint->complaintTitle->title : 'Other'),
                    'title_id' => $complaint->complaint_title_id,
                    'designation' => $complaint->designation, // Verify attribute
                    'description' => $complaint->description,
                    'availability_time' => $complaint->availability_time,
                    'status' => $complaint->mapped_status,
                    'status_label' => $complaint->status_display,
                    'created_at' => $complaint->created_at->timezone('Asia/Karachi')->format('M d, Y H:i'),
                    'closed_at' => $complaint->closed_at ? $complaint->closed_at->timezone('Asia/Karachi')->format('M d, Y H:i') : null,
                    'assigned_employee' => $complaint->assignedEmployee ? $complaint->assignedEmployee->name : null,
                ];
            });

        return response()->json([
            'success' => true,
            'complaints' => $complaints
        ]);
    }

    /**
     * Get single complaint details
     */
    public function show(Request $request, $id)
    {
        $house = $request->input('authenticated_house') ?: $request->user();
        if (!$house) {
            return response()->json(['success' => false, 'message' => 'Unauthorized. Please login again.'], 401);
        }

        $complaint = Complaint::where('id', $id)
            ->where('house_id', $house->id)
            ->with(['category', 'complaintTitle'])
            ->first();

        if (!$complaint) {
            return response()->json(['success' => false, 'message' => 'Complaint not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $complaint->id,
                'ticket_number' => $complaint->ticket_number,
                'title' => $complaint->title ?? ($complaint->complaintTitle ? $complaint->complaintTitle->title : 'Unknown'),
                'category' => $complaint->category ? $complaint->category->name : 'Unknown',
                'status' => $complaint->mapped_status,
                'status_label' => $complaint->status_display,
                'priority' => $complaint->priority,
                'description' => $complaint->description,
                'created_at' => $complaint->created_at->timezone('Asia/Karachi')->format('d M Y, h:i A'),
                'availability_time' => $complaint->availability_time,
                'remarks' => $complaint->remarks, // feedback from admin if any
                'feedback' => $complaint->feedback ? [
                    'rating' => $complaint->feedback->rating_score,
                    'stars' => $complaint->feedback->rating_score . ' Stars',
                    'comments' => $complaint->feedback->comments,
                    'status' => $complaint->feedback->overall_rating_display,
                    'date' => $complaint->feedback->created_at->timezone('Asia/Karachi')->format('d M Y, h:i A')
                ] : null,
                'logs' => $complaint->logs()->orderBy('created_at', 'desc')->get()->map(function($log) {
                    return [
                        'status' => $log->action,
                        'remarks' => $log->remarks,
                        'created_at' => $log->created_at->timezone('Asia/Karachi')->format('d M Y, h:i A')
                    ];
                })
            ]
        ]);
    }

    /**
     * Register a new complaint
     */
    public function register(Request $request)
    {
        // Get house from custom middleware (manual.auth)
        $house = $request->input('authenticated_house');
        
        // Check if house exists
        if (!$house) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. No valid session found.'
            ], 401);
        }

        // Validate: Accept ID OR Name for compatibility
        $validator = Validator::make($request->all(), [
            'category' => 'required', // Relaxed to check manually
            'title' => 'required',
            'description' => 'required|string',
            'availability_time' => 'nullable|string',
            'priority' => 'nullable|in:low,medium,high,urgent'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Resolve Category (ID or Name)
        $categoryInput = $request->category;
        $categoryId = null;
        if (is_numeric($categoryInput)) {
            $categoryId = $categoryInput;
        } else {
            $catObj = DB::table('complaint_categories')->where('name', $categoryInput)->first();
            $categoryId = $catObj ? $catObj->id : null;
        }

        if (!$categoryId) {
             return response()->json([
                'success' => false,
                'message' => 'Invalid Category',
            ], 422);
        }

        // Resolve Title (ID or Name)
        $titleInput = $request->title;
        $titleId = null;
        $customTitle = null;

        if (is_numeric($titleInput)) {
            $titleId = $titleInput;
        } else {
            // Try matching Name within the Category
            $titleObj = DB::table('complaint_titles')
                ->where('title', $titleInput)
                ->where('category_id', $categoryId)
                ->first();
            
            if ($titleObj) {
                $titleId = $titleObj->id;
            } else {
                // Treated as Custom Title if not found? Or Error?
                // Assuming "Other" or custom text.
                $customTitle = (string) $titleInput;
            }
        }

        DB::beginTransaction();
        try {

            // Create complaint without firing model events to avoid heavy listeners
            $complaint = Complaint::withoutEvents(function () use ($titleId, $customTitle, $house, $categoryId, $request) {
                return Complaint::create([
                    'complaint_title_id' => $titleId,
                    'title'              => $customTitle,
                    'house_id'           => $house->id,
                    'city_id'            => $house->city_id,
                    'sector_id'          => $house->sector_id,
                    'category_id'        => $categoryId,
                    'priority'           => $request->priority ?? 'medium',
                    'description'        => $request->description,
                    'availability_time'  => $request->availability_time,
                    'status'             => 'new',
                ]);
            });

            ComplaintLog::create([
                'complaint_id' => $complaint->id,
                'action'       => 'created',
                'remarks'      => 'Complaint registered via App by ' . ($house->house_no ?? $house->name ?? 'User'),
            ]);

            DB::commit();

            // Return only scalar values to keep the JSON payload small
            return response()->json([
                'success'          => true,
                'message'          => 'Complaint registered successfully',
                'ticket_number'    => $complaint->ticket_number,
                'complaint_id'     => $complaint->id,
                'availability_time'=> $complaint->availability_time,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('API Complaint Registration Error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to register complaint.',
            ], 500);
        }
    }

    /**
     * Get Complaint Categories
     */
    public function categories()
{
    $rows = DB::table('complaint_categories as c')
        ->leftJoin('complaint_titles as t', 't.category_id', '=', 'c.id')
        ->select(
            'c.id as cat_id',
            'c.name as cat_name',
            'c.app_name as cat_app_name',
            't.id as subcat_id',
            't.title as subcat_title'
        )
        ->orderBy('c.name')
        ->orderBy('t.title')
        ->get();

    $data = $rows->groupBy('cat_id')->map(function ($items) {
        $firstItem = $items->first();
        // Use app_name if available, otherwise fallback to name
        $displayName = $firstItem->cat_app_name ?: $firstItem->cat_name;

        return [
            "cat_title" => $displayName,
            "subcats" => $items->whereNotNull('subcat_id')->map(function ($row) {
                return [
                    "subcat_id" => $row->subcat_id,
                    "subcat_title" => $row->subcat_title
                ];
            })->values()->toArray()
        ];
    })->toArray(); // ✅ important: values() NOT used so cat_id stays as key

    return response()->json([
        "success" => true,
        "data" => $data
    ]);
}
    /**
     * Get Complaint Titles (Types)
     */
    public function titles(Request $request)
    {
        $query = DB::table('complaint_titles')
            ->join('complaint_categories', 'complaint_titles.category_id', '=', 'complaint_categories.id');
        
        if ($request->has('category')) {
            $categoryInput = $request->category;
            if (is_numeric($categoryInput)) {
                $query->where('complaint_titles.category_id', $categoryInput);
            } else {
                $query->where('complaint_categories.name', $categoryInput);
            }
        }

        $titles = $query->orderBy('complaint_titles.title')
            ->get([
                'complaint_titles.id', 
                'complaint_titles.title', 
                'complaint_categories.name as category',
                'complaint_categories.id as category_id'
            ]);

        return response()->json([
            'success' => true,
            'data' => $titles
        ]);
    }

    /**
     * Get Titles for a specific category (RESTful)
     */
    public function getTitlesByCategory($category)
    {
        // Check if category is ID or Name
        $query = DB::table('complaint_titles')
            ->join('complaint_categories', 'complaint_titles.category_id', '=', 'complaint_categories.id');

        if (is_numeric($category)) {
             $query->where('complaint_titles.category_id', $category);
        } else {
             $categoryName = urldecode($category);
             $query->where('complaint_categories.name', $categoryName);
        }

        $titles = $query->orderBy('complaint_titles.title')
            ->get([
                'complaint_titles.id', 
                'complaint_titles.title', 
                'complaint_categories.name as category',
                'complaint_categories.id as category_id'
            ]);

        return response()->json([
            'success' => true,
            'data' => $titles
        ]);
    }

    /**
     * Submit Feedback
     */
    public function feedback(Request $request, $id = null)
    {
        // Use ID from URL if available, otherwise from request body
        $complaintId = $id ?? $request->complaint_id;

        $validator = Validator::make(array_merge($request->all(), ['complaint_id' => $complaintId]), [
            'complaint_id' => 'required|exists:complaints,id',
            'overall_rating' => 'required|in:excellent,good,satisfied,fair,poor',
            'comments' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $house = $this->getAuthenticatedHouse($request);
        if (!$house) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 401);
        }

        $complaint = Complaint::where('id', $complaintId)
            ->where('house_id', $house->id)
            ->first(); 

        if (!$complaint) {
            return response()->json(['success' => false, 'message' => 'Complaint not found or unauthorized'], 403);
        }

        if ($complaint->status !== 'resolved' && $complaint->status !== 'closed') {
             // Optional: Allow feedback only on resolved complaints
        }

        $feedback = ComplaintFeedback::create([
            'complaint_id' => $complaint->id,
            'house_id' => $house->id,
            'overall_rating' => $request->overall_rating,
            'rating_score' => $this->getRatingScore($request->overall_rating),
            'comments' => $request->comments,
            'feedback_date' => now(),
            'submitted_by' => $house->house_no ?? $house->name ?? 'User'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Feedback submitted successfully',
            'data' => $feedback
        ]);
    }

    private function getRatingScore($rating)
    {
        return match (strtolower($rating)) {
            'excellent' => 5,
            'good' => 4,
            'satisfied' => 3,
            'fair' => 2,
            'poor' => 1,
            default => 0,
        };
    }
}
