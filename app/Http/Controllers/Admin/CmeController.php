<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class CmeController extends Controller
{
    public function index()
    {
        if (!Schema::hasTable('cmes')) {
            $cmes = new LengthAwarePaginator([], 0, 15);
            return view('admin.cmes.index', compact('cmes'))
                ->with('error', 'Run migrations to create CMES table.');
        }

        $cmes = Cme::orderBy('id', 'asc')->paginate(15);
        return view('admin.cmes.index', compact('cmes'));
    }

    public function store(Request $request)
    {
        if (!Schema::hasTable('cmes')) {
            return back()->with('error', 'Run migrations to create CMES table (php artisan migrate).');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150|unique:cmes,name',
            'status' => 'required|in:active,inactive',
        ]);

        Cme::create($validated);

        return back()->with('success', 'CMES added successfully.');
    }

    public function update(Request $request, $id)
    {
        if (!Schema::hasTable('cmes')) {
            return back()->with('error', 'Run migrations to create CMES table (php artisan migrate).');
        }

        try {
            $cme = Cme::findOrFail($id);

            $rules = [
                'name' => 'required|string|max:150',
                'status' => 'required|in:active,inactive',
            ];

            if ($request->name !== $cme->name) {
                $exists = Cme::where('name', $request->name)
                    ->where('id', '!=', $id)
                    ->exists();

                if ($exists) {
                    return back()->withErrors(['name' => 'The CMES name has already been taken.'])->withInput();
                }
            }

            $validated = $request->validate($rules);
            $cme->update($validated);

            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['success' => true, 'message' => 'CMES updated']);
            }

            return back()->with('success', 'CMES updated successfully.');
        } catch (\Exception $e) {
            Log::error('CMES update error: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
            }

            return back()->with('error', 'Error updating CMES: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        if (!Schema::hasTable('cmes')) {
            return back()->with('error', 'Run migrations to create CMES table (php artisan migrate).');
        }

        try {
            $cme = Cme::findOrFail($id);
            $cme->update([
                'status' => 'inactive'
            ]);

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => true]);
            }

            return back()->with('success', 'CMES removed from list.');
        } catch (\Exception $e) {
            Log::error('CMES delete error: ' . $e->getMessage());

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }

            return back()->with('error', 'Error deleting CMES: ' . $e->getMessage());
        }
    }
}

