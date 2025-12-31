<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Client;
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
     * Get all complaints for the logged-in house
     */
    public function index(Request $request)
    {
        $house = $request->user();
        
        $complaints = Complaint::where('house_id', $house->id)
            ->with(['category_detail', 'assigned_employee:id,name']) // Load relationships if needed, adjust based on your models
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($complaint) {
                return [
                    'id' => $complaint->id,
                    'ticket_number' => $complaint->ticket_number,
                    'title' => $complaint->title,
                    'category' => $complaint->category,
                    'status' => $complaint->status,
                    'created_at' => $complaint->created_at->format('d M Y, h:i A'),
                    'description' => $complaint->description,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $complaints
        ]);
    }

    /**
     * Get single complaint details
     */
    public function show(Request $request, $id)
    {
        $house = $request->user();
        $complaint = Complaint::where('house_id', $house->id)->where('id', $id)->first();

        if (!$complaint) {
            return response()->json(['success' => false, 'message' => 'Complaint not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $complaint->id,
                'ticket_number' => $complaint->ticket_number,
                'title' => $complaint->title,
                'category' => $complaint->category,
                'status' => $complaint->status,
                'priority' => $complaint->priority,
                'description' => $complaint->description,
                'created_at' => $complaint->created_at->format('d M Y, h:i A'),
                'availability_time' => $complaint->availability_time,
                'remarks' => $complaint->remarks, // feedback from admin if any
                'logs' => $complaint->logs()->orderBy('created_at', 'desc')->get()->map(function($log) {
                    return [
                        'status' => $log->action,
                        'remarks' => $log->remarks,
                        'created_at' => $log->created_at->format('d M Y, h:i A')
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
        $house = $request->user(); // Authenticated House

        $validator = Validator::make($request->all(), [
            'category' => 'required|string',
            'title' => 'required|string',
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

        DB::beginTransaction();
        try {
            // Find or create Client (representing the house owner/resident)
            // Ideally we link to the House directly, but if your system relies on Client ID:
            $client = Client::firstOrCreate(
                ['email' => $house->username . '@cms.com'], // Dummy email or use house field if added
                [
                    'client_name' => $house->name ?? $house->username,
                    'contact_person' => $house->name ?? $house->username,
                    'phone' => $house->phone ?? '',
                    'status' => 'active',
                    'city' => $house->city ? $house->city->name : '',
                    'sector' => $house->sector ? $house->sector->name : '',
                    'address' => $house->address,
                ]
            );

            // Create Complaint
            $complaint = Complaint::create([
                'uid' => uniqid('C-'), // Ensure you have this or let db handle it
                'title' => $request->title,
                'house_id' => $house->id,
                'client_id' => $client->id,
                'city_id' => $house->city_id,
                'sector_id' => $house->sector_id,
                'category' => $request->category,
                'priority' => $request->priority ?? 'medium',
                'description' => $request->description,
                'availability_time' => $request->availability_time,
                'status' => 'new',
            ]);

            // Log activity
            ComplaintLog::create([
                'complaint_id' => $complaint->id,
                'action' => 'created',
                'remarks' => 'Complaint registered via App by ' . ($house->name ?? $house->username),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Complaint registered successfully',
                'ticket_number' => $complaint->ticket_number,
                'complaint_id' => $complaint->id
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('API Complaint Registration Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to register complaint.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Complaint Categories
     */
    public function categories()
    {
        // If you have a Category model or table
        // $categories = Category::where('status', 'active')->pluck('name');
        
        // Hardcoded as per your view logic if needed, or fetch from DB
        // Assuming you have 'categories' table or config
        $categories = DB::table('categories')->pluck('name'); // Adjust table name
        
        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * Get Complaint Titles (Types)
     */
    public function titles(Request $request)
    {
        $query = DB::table('complaint_titles')->where('status', 'active'); // Adjust table name if needed
        
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        $titles = $query->orderBy('title')->get(['id', 'title', 'category']);

        return response()->json([
            'success' => true,
            'data' => $titles
        ]);
    }

    /**
     * Submit Feedback
     */
    public function feedback(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'complaint_id' => 'required|exists:complaints,id',
            'rating' => 'required|integer|min:1|max:5',
            'comments' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $house = $request->user();
        $complaint = Complaint::where('id', $request->complaint_id)->where('house_id', $house->id)->first();

        if (!$complaint) {
            return response()->json(['success' => false, 'message' => 'Complaint not found or unauthorized'], 403);
        }

        if ($complaint->status !== 'completed' && $complaint->status !== 'closed') {
             // Optional: Allow feedback only on closed complaints?
             // return response()->json(['success' => false, 'message' => 'Complaint is not resolved yet'], 400);
        }

        ComplaintFeedback::create([
            'complaint_id' => $complaint->id,
            'rating' => $request->rating,
            'comments' => $request->comments,
            'submitted_by' => $house->id // Or name
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Feedback submitted successfully'
        ]);
    }
}
