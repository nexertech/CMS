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
    use \App\Traits\LocationFilterTrait;

    public function index()
    {
        if (!Schema::hasTable('sectors')) {
            $sectors = new LengthAwarePaginator([], 0, 15);
            $cities = collect();
            $cmes = collect();
            return view('admin.sector.index', compact('sectors', 'cities', 'cmes'))
                ->with('error', 'Run migrations to create sectors table.');
        }

        // Show all sectors (both active and edit-inactivated)
        $sectors = Sector::with(['city.cme'])->orderBy('id', 'asc')->paginate(15);
        $cities = Schema::hasTable('cities')
            ? City::where('status', 1)->with('cme')->orderBy('id', 'asc')->get()
            : collect();
        $cmes = Schema::hasTable('cmes')
            ? Cme::where('status', 1)->orderBy('name')->get()
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
            'status' => 'required|in:0,1',
        ]);

        // Check uniqueness: same sector name can exist for different cities
        $exists = Sector::where('name', $request->name)
            ->where('city_id', $request->city_id)
            ->where('status', 1)
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
                'status' => 'required|in:0,1',
            ];

            // Only validate uniqueness if name changed and check against active sectors only
            if ($request->name !== $sector->name || $request->city_id != $sector->city_id) {
                $exists = Sector::where('name', $request->name)
                    ->where('city_id', $request->city_id)
                    ->where('status', 1)
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
            $sector->delete();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => true]);
            }
            return back()->with('success', 'GE Node deleted successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            $sector = Sector::find($id);
            if ($sector) {
                $sector->update(['status' => 0]);
            }
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => true]);
            }
            return back()->with('success', 'GE Node marked as inactive.');
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
        // Support both single city_id and array of city_ids
        $cityIds = $request->input('city_id');
        
        if (!is_array($cityIds)) {
            $cityIds = $cityIds ? explode(',', $cityIds) : [];
        }

        if (empty($cityIds)) {
            return response()->json([]);
        }
        
        $user = auth()->user();

        $query = Sector::whereIn('city_id', $cityIds)
            ->where('status', 1);
            
        // Apply data isolation if user is logged in
        if ($user) {
            $roleName = strtolower($user->role->role_name ?? '');

            // Admin/Director see all sectors, others see only their assigned sectors
            if (!in_array($roleName, ['admin', 'director'])) {
                $sectorIds = $this->getUserSectorIds($user);
                if ($sectorIds !== null) {
                    $query->whereIn('id', $sectorIds);
                }
            }
        }

        $sectors = $query->orderBy('id', 'asc')
            ->get(['id', 'name']);

        return response()->json($sectors);
    }
}
