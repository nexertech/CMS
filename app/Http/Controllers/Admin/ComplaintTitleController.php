<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ComplaintTitle;
use App\Models\ComplaintCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ComplaintTitleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ComplaintTitle::with('category');

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('category', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Clear any existing orders and set explicit ascending order by ID
        $complaintTitles = $query->reorder()
            ->orderBy('id', 'asc')
            ->paginate(20);

        // Get categories for filter dropdown
        $categories = ComplaintCategory::where('status', 1)->orderBy('name')->get();

        return view('admin.complaint-titles.index', compact('complaintTitles', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = ComplaintCategory::where('status', 1)->orderBy('name')->get();
        return view('admin.complaint-titles.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category' => 'required|exists:complaint_categories,id', // Expects ID
            'title' => 'required|string|max:255|unique:complaint_titles,title,NULL,id,category_id,' . $request->category . ',deleted_at,NULL',
            'questions' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        ComplaintTitle::create([
            'category_id' => $request->category,
            'title' => $request->title,
            'questions' => $request->questions,
        ]);

        return redirect()->route('admin.complaint-titles.index')
            ->with('success', 'Complaint title created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ComplaintTitle $complaintTitle)
    {
        return view('admin.complaint-titles.show', compact('complaintTitle'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ComplaintTitle $complaintTitle)
    {
        $categories = ComplaintCategory::where('status', 1)->orderBy('name')->get();
        return view('admin.complaint-titles.edit', compact('complaintTitle', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ComplaintTitle $complaintTitle)
    {
        $validator = Validator::make($request->all(), [
            'category' => 'required|exists:complaint_categories,id',
            'title' => 'required|string|max:255|unique:complaint_titles,title,' . $complaintTitle->id . ',id,category_id,' . $request->category . ',deleted_at,NULL',
            'questions' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $complaintTitle->update([
            'category_id' => $request->category,
            'title' => $request->title,
            'questions' => $request->questions,
        ]);

        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['success' => true, 'message' => 'Complaint title updated successfully.']);
        }
        return redirect()->route('admin.complaint-titles.index')
            ->with('success', 'Complaint title updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ComplaintTitle $complaintTitle)
    {
        try {
            $complaintTitle->delete();
            return redirect()->route('admin.complaint-titles.index')
                ->with('success', 'Complaint title deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error deleting complaint title: ' . $e->getMessage());
        }
    }

    /**
     * Get complaint titles by category (AJAX)
     */
    public function getTitlesByCategory(Request $request)
    {
        $category = $request->input('category');
        
        if (!$category) {
            return response()->json([]);
        }
        
        $query = DB::table('complaint_titles');

        if (is_numeric($category)) {
             // If ID provided
             $query->where('category_id', $category);
             $query->join('complaint_categories', 'complaint_titles.category_id', '=', 'complaint_categories.id');
        } else {
            // If Name provided (legacy or other usage)
            $categoryName = $category;
            $query->join('complaint_categories', 'complaint_titles.category_id', '=', 'complaint_categories.id')
                  ->where('complaint_categories.name', $categoryName);
        }

        $titles = $query->orderBy('complaint_titles.title')
            ->get([
                'complaint_titles.id',
                'complaint_titles.title',
                'complaint_titles.questions',
                'complaint_categories.name as category'
            ]);

        return response()->json($titles);
    }
}


