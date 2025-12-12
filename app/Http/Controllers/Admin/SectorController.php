<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sector;
use App\Models\City;
use App\Models\Cme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;

class SectorController extends Controller
{
    public function index()
    {
        if (!Schema::hasTable('sectors')) {
            $sectors = new LengthAwarePaginator([], 0, 15);
            $cities = collect();
            $cmes = collect();
            return view('admin.sector.index', compact('sectors', 'cities', 'cmes'))
                ->with('error', 'Run migrations to create sectors table.');
        }

        // Show all sectors; status column indicates active/inactive
        $sectors = Sector::with(['city.cme'])->orderBy('id', 'asc')->paginate(15);
        $cities = Schema::hasTable('cities')
            ? City::where('status', 'active')->with('cme')->orderBy('id', 'asc')->get()
            : collect();
        $cmes = Schema::hasTable('cmes')
            ? Cme::where('status', 'active')->orderBy('name')->get()
            : collect();

        return view('admin.sector.index', compact('sectors', 'cities', 'cmes'));
    }

    public function store(Request $request)
    {
        if (!Schema::hasTable('sectors') || !Schema::hasTable('cities') || !Schema::hasTable('cmes')) {
            return back()->with('error', 'Run migrations to create sectors/cities/CMES tables (php artisan migrate).');
        }
        $validated = $request->validate([
            'cme_id' => ['required', 'integer', Rule::exists('cmes', 'id')],
            'city_id' => [
                'required',
                'integer',
                Rule::exists('cities', 'id')->where(function ($query) use ($request) {
                    return $query->where('cme_id', $request->cme_id);
                }),
            ],
            'name' => 'required|string|max:100',
            'status' => 'required|in:active,inactive',
        ]);

        // Check uniqueness: same sector name can exist for different cities
        $exists = Sector::where('name', $request->name)
            ->where('city_id', $request->city_id)
            ->where('status', 'active')
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'The sector name has already been taken for this city.'])->withInput();
        }
        $payload = collect($validated)->only(['cme_id', 'city_id', 'name', 'status'])->toArray();
        Sector::create($payload);
        return back()->with('success', 'Sector created');
    }

    public function update(Request $request, $id)
    {
        if (!Schema::hasTable('sectors') || !Schema::hasTable('cities') || !Schema::hasTable('cmes')) {
            return back()->with('error', 'Run migrations to create sectors/cities/CMES tables (php artisan migrate).');
        }

        try {
            $sector = Sector::findOrFail($id);

            $rules = [
                'cme_id' => ['required', 'integer', Rule::exists('cmes', 'id')],
                'city_id' => [
                    'required',
                    'integer',
                    Rule::exists('cities', 'id')->where(function ($query) use ($request) {
                        return $query->where('cme_id', $request->cme_id);
                    }),
                ],
                'name' => 'required|string|max:100',
                'status' => 'required|in:active,inactive',
            ];

            // Only validate uniqueness if name changed and check against active sectors only
            if ($request->name !== $sector->name || $request->city_id != $sector->city_id) {
                $exists = Sector::where('name', $request->name)
                    ->where('city_id', $request->city_id)
                    ->where('status', 'active')
                    ->where('id', '!=', $id)
                    ->exists();

                if ($exists) {
                    return back()->withErrors(['name' => 'The name has already been taken for this city.'])->withInput();
                }
            }

            $validated = $request->validate($rules);
            $payload = collect($validated)->only(['cme_id', 'city_id', 'name', 'status'])->toArray();
            $sector->update($payload);

            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['success' => true, 'message' => 'Sector updated']);
            }
            return back()->with('success', 'Sector updated');
        } catch (\Exception $e) {
            Log::error('Sector update error: ' . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
            }
            return back()->with('error', 'Error updating sector: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        if (!Schema::hasTable('sectors')) {
            return back()->with('error', 'Run migrations to create sectors table (php artisan migrate).');
        }

        try {
            $sector = Sector::findOrFail($id);
            // Soft delete without migration: mark as inactive
            $sector->update([
                'status' => 'inactive'
            ]);

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => true]);
            }
            return back()->with('success', 'Sector removed from list');
        } catch (\Exception $e) {
            Log::error('Sector delete error: ' . $e->getMessage());
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
            }
            return back()->with('error', 'Error deleting sector: ' . $e->getMessage());
        }
    }

    /**
     * Get sectors by city (AJAX endpoint)
     */
    public function getSectorsByCity(Request $request)
    {
        $cityId = $request->query('city_id');

        if (!$cityId) {
            return response()->json([]);
        }

        $sectors = Sector::where('city_id', $cityId)
            ->where('status', 'active')
            ->orderBy('id', 'asc')
            ->get(['id', 'name']);

        return response()->json($sectors);
    }
}
