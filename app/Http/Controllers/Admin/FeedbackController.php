<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\ComplaintFeedback;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FeedbackController extends Controller
{
    /**
     * Display a listing of feedbacks.
     */
    public function index(Request $request)
    {
        $query = ComplaintFeedback::with(['complaint', 'client', 'enteredBy']);

        // Filter by rating
        if ($request->has('rating') && $request->rating) {
            $query->where('overall_rating', $request->rating);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('feedback_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('feedback_date', '<=', $request->date_to);
        }

        // Search by complaint ID or client name
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('complaint', function($complaintQuery) use ($search) {
                    $complaintQuery->where('title', 'like', "%{$search}%");
                })->orWhereHas('client', function($clientQuery) use ($search) {
                    $clientQuery->where('client_name', 'like', "%{$search}%");
                });
            });
        }

        $feedbacks = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.feedbacks.index', compact('feedbacks'));
    }

    /**
     * Show the form for creating a new feedback.
     */
    public function create(Complaint $complaint)
    {
        // Check if feedback already exists
        if ($complaint->feedback) {
            if (request()->ajax() || request()->wantsJson() || request()->has('modal')) {
                return redirect()->route('admin.feedback.edit', ['feedback' => $complaint->feedback, 'modal' => 1])
                    ->with('info', 'Feedback already exists. You can edit it.');
            }
            return redirect()->route('admin.feedback.edit', $complaint->feedback)
                ->with('info', 'Feedback already exists. You can edit it.');
        }

        // Check if complaint is resolved
        if (!in_array($complaint->status, ['resolved', 'closed'])) {
            if (request()->ajax() || request()->wantsJson() || request()->has('modal')) {
                return response()->json(['error' => 'Feedback can only be added for resolved complaints.'], 400);
            }
            return redirect()->back()
                ->with('error', 'Feedback can only be added for resolved complaints.');
        }

        // Return full view content for modal (JS extracts content)
        if (request()->ajax() || request()->wantsJson() || request()->has('modal')) {
            return view('admin.feedbacks.create', compact('complaint'))->render();
        }

        return view('admin.feedbacks.create', compact('complaint'));
    }

    /**
     * Store a newly created feedback.
     */
    public function store(Request $request, Complaint $complaint)
    {
        // Check if feedback already exists
        if ($complaint->feedback) {
            return redirect()->back()
                ->with('error', 'Feedback already exists for this complaint.');
        }

        // Check if complaint is resolved
        if (!in_array($complaint->status, ['resolved', 'closed'])) {
            return redirect()->back()
                ->with('error', 'Feedback can only be added for resolved complaints.');
        }

        $validator = Validator::make($request->all(), [
            'overall_rating' => 'required|in:excellent,good,average,poor',
            'rating_score' => 'nullable|integer|min:1|max:5',
            'service_quality' => 'nullable|in:excellent,good,average,poor',
            'response_time' => 'nullable|in:excellent,good,average,poor',
            'resolution_quality' => 'nullable|in:excellent,good,average,poor',
            'staff_behavior' => 'nullable|in:excellent,good,average,poor',
            'comments' => 'nullable|string|max:1000',
            'remarks' => 'nullable|string|max:1000',
            'feedback_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $feedback = ComplaintFeedback::create([
                'complaint_id' => $complaint->id,
                'client_id' => $complaint->client_id,
                'entered_by' => Auth::id(),
                'overall_rating' => $request->overall_rating,
                'rating_score' => $request->rating_score ?? $this->getRatingScore($request->overall_rating),
                'service_quality' => $request->service_quality,
                'response_time' => $request->response_time,
                'resolution_quality' => $request->resolution_quality,
                'staff_behavior' => $request->staff_behavior,
                'comments' => $request->comments,
                'remarks' => $request->remarks,
                'feedback_date' => now(), // Always use current date and time when adding feedback
                'entered_at' => now(),
            ]);

            DB::commit();

            // Refresh the complaint to ensure feedback relationship is loaded
            $complaint->load('feedback.enteredBy');

            // If request is from modal, return JSON response
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Feedback added successfully.',
                    'complaint_id' => $complaint->id
                ]);
            }
            
            // Redirect back to approvals page (Complaints Regn) with complaint ID to open in modal
            return redirect()->route('admin.approvals.index', ['view_complaint' => $complaint->id])
                ->with('success', 'Feedback added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to add feedback: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()
                ->with('error', 'Failed to add feedback: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified feedback.
     */
    public function edit(ComplaintFeedback $feedback)
    {
        $user = Auth::user();
        
        // Check if user is GE (Garrison Engineer)
        $isGE = false;
        if ($user && $user->role) {
            $roleName = strtolower($user->role->role_name ?? '');
            $isGE = in_array($roleName, ['garrison_engineer', 'garrison engineer']) || 
                    strpos(strtolower($roleName), 'garrison') !== false ||
                    strpos(strtolower($roleName), 'ge') !== false;
        }
        
        // Only GE can edit feedback
        if (!$isGE) {
            if (request()->ajax() || request()->wantsJson() || request()->has('modal')) {
                return response()->json([
                    'error' => 'Only Garrison Engineer (GE) can edit feedback.'
                ], 403);
            }
            return redirect()->back()
                ->with('error', 'Only Garrison Engineer (GE) can edit feedback.');
        }
        
        $feedback->load(['complaint', 'client']);
        
        // Return full view content for modal (JS extracts content)
        if (request()->ajax() || request()->wantsJson() || request()->has('modal')) {
            return view('admin.feedbacks.edit', compact('feedback'))->render();
        }
        
        return view('admin.feedbacks.edit', compact('feedback'));
    }

    /**
     * Update the specified feedback.
     */
    public function update(Request $request, ComplaintFeedback $feedback)
    {
        $user = Auth::user();
        
        // Check if user is GE (Garrison Engineer)
        $isGE = false;
        if ($user && $user->role) {
            $roleName = strtolower($user->role->role_name ?? '');
            $isGE = in_array($roleName, ['garrison_engineer', 'garrison engineer']) || 
                    strpos(strtolower($roleName), 'garrison') !== false ||
                    strpos(strtolower($roleName), 'ge') !== false;
        }
        
        // Only GE can update feedback
        if (!$isGE) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only Garrison Engineer (GE) can update feedback.'
                ], 403);
            }
            return redirect()->back()
                ->with('error', 'Only Garrison Engineer (GE) can update feedback.');
        }
        
        $validator = Validator::make($request->all(), [
            'overall_rating' => 'required|in:excellent,good,average,poor',
            'rating_score' => 'nullable|integer|min:1|max:5',
            'service_quality' => 'nullable|in:excellent,good,average,poor',
            'response_time' => 'nullable|in:excellent,good,average,poor',
            'resolution_quality' => 'nullable|in:excellent,good,average,poor',
            'staff_behavior' => 'nullable|in:excellent,good,average,poor',
            'comments' => 'nullable|string|max:1000',
            'remarks' => 'nullable|string|max:1000',
            'feedback_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $feedback->update([
                'overall_rating' => $request->overall_rating,
                'rating_score' => $request->rating_score ?? $this->getRatingScore($request->overall_rating),
                'service_quality' => $request->service_quality,
                'response_time' => $request->response_time,
                'resolution_quality' => $request->resolution_quality,
                'staff_behavior' => $request->staff_behavior,
                'comments' => $request->comments,
                'remarks' => $request->remarks,
                'feedback_date' => now(), // Auto-update to current date and time when editing
            ]);

            DB::commit();

            // Refresh the feedback relationship to ensure it's loaded
            $feedback->refresh();
            $feedback->load('enteredBy');

            // If request is from modal, return JSON response
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Feedback updated successfully.',
                    'complaint_id' => $feedback->complaint_id
                ]);
            }
            
            // Redirect back to approvals page (Complaints Regn) with complaint ID to open in modal
            return redirect()->route('admin.approvals.index', ['view_complaint' => $feedback->complaint_id])
                ->with('success', 'Feedback updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update feedback: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()
                ->with('error', 'Failed to update feedback: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified feedback.
     */
    public function destroy(ComplaintFeedback $feedback)
    {
        $complaintId = $feedback->complaint_id;
        
        try {
            $feedback->delete();
            // Redirect back to approvals page (Complaints Regn) with complaint ID to open in modal
            return redirect()->route('admin.approvals.index', ['view_complaint' => $complaintId])
                ->with('success', 'Feedback deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete feedback: ' . $e->getMessage());
        }
    }

    /**
     * Get rating score from rating text
     */
    private function getRatingScore($rating): int
    {
        return match($rating) {
            'excellent' => 5,
            'good' => 4,
            'average' => 3,
            'poor' => 2,
            default => 3
        };
    }
}

