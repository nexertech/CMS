<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FrontendUser;
use App\Models\City;
use App\Models\Sector;
use App\Models\FrontendUserLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FrontendUserController extends Controller
{
    public function __construct()
    {
        // Middleware is applied in routes
    }

    /**
     * Display a listing of frontend users
     */
    public function index(Request $request)
    {
        $query = FrontendUser::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $users = $query->orderBy('id', 'asc')->paginate(15);

        return view('admin.frontend-users.index', compact('users'));
    }

    /**
     * Show the form for creating a new frontend user
     */
    public function create()
    {
        // Get CMEs with their cities and sectors
        $cmes = DB::table('cmes')
            ->select('cmes.id', 'cmes.name as cme_name', 'cities.id as city_id', 'cities.name as city_name', 'sectors.id as sector_id', 'sectors.name as sector_name')
            ->leftJoin('cities', 'cmes.id', '=', 'cities.cme_id')
            ->leftJoin('sectors', 'cmes.id', '=', 'sectors.cme_id')
            ->where('cmes.status', '=', 'active')
            ->orderBy('cmes.name')
            ->orderBy('cities.name')
            ->orderBy('sectors.name')
            ->get();

        return view('admin.frontend-users.create', compact('cmes'));
    }

    /**
     * Store a newly created frontend user
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:100|unique:frontend_users',
            'name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:150|unique:frontend_users,email',
            'phone' => 'nullable|string|min:11|max:20',
            'password' => 'required|string|min:6|confirmed',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $user = FrontendUser::create([
            'username' => $request->username,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'status' => $request->status,
        ]);

        // Handle "Grant all privileges" (Super Admin)
        if ($request->has('is_super_admin') && $request->is_super_admin == 1) {
            // Assign ALL CMEs, Cities, and Sectors
            $user->cme_ids = DB::table('cmes')->where('status', 'active')->pluck('id')->toArray();
            $user->group_ids = DB::table('cities')->where('status', 'active')->pluck('id')->toArray();
            $user->node_ids = DB::table('sectors')->where('status', 'active')->pluck('id')->toArray();
            $user->save();
        }

        return redirect()->route('admin.frontend-users.index')
            ->with('success', 'Frontend user created successfully.');
    }

    /**
     * Display the specified frontend user
     */
    public function show(FrontendUser $frontend_user)
    {
        // Build assigned locations from JSON columns
        $assignedLocations = collect();

        $cityIds = $frontend_user->group_ids ?? [];
        $sectorIds = $frontend_user->node_ids ?? [];

        // Load cities with their sectors
        if (!empty($cityIds) || !empty($sectorIds)) {
            // Load all sectors for the assigned sectors
            $sectors = \App\Models\Sector::whereIn('id', $sectorIds)->with('city')->get();

            // Create location objects for sectors
            foreach ($sectors as $sector) {
                $assignedLocations->push((object)[
                    'city_id' => $sector->city_id,
                    'sector_id' => $sector->id,
                    'city' => $sector->city,
                    'sector' => $sector,
                ]);
            }

            // For cities that are assigned but don't have specific sectors in node_ids,
            // load ALL sectors for those cities
            foreach ($cityIds as $cityId) {
                // Check if this city already has sectors assigned
                $cityHasSectors = $sectors->where('city_id', $cityId)->count() > 0;

                if (!$cityHasSectors) {
                    // Load all sectors for this city
                    $city = \App\Models\City::with('sectors')->find($cityId);
                    if ($city && $city->sectors) {
                        foreach ($city->sectors as $sector) {
                            $assignedLocations->push((object)[
                                'city_id' => $city->id,
                                'sector_id' => $sector->id,
                                'city' => $city,
                                'sector' => $sector,
                            ]);
                        }
                    }
                }
            }
        }

        // Get user privileges from JSON columns
        $userCmeIds = $frontend_user->cme_ids ?? [];
        $userCityIds = $frontend_user->group_ids ?? [];

        // Get all CMEs with their cities for display
        $cmes = DB::table('cmes')
            ->select('cmes.id', 'cmes.name as cme_name', 'cities.id as city_id', 'cities.name as city_name')
            ->leftJoin('cities', 'cmes.id', '=', 'cities.cme_id')
            ->where('cmes.status', '=', 'active')
            ->orderBy('cmes.name')
            ->orderBy('cities.name')
            ->get();

        $groupedCmes = $cmes->groupBy('cme_name');

        if (request()->get('format') === 'html') {
            return view('admin.frontend-users.show', compact('frontend_user', 'assignedLocations', 'groupedCmes', 'userCmeIds', 'userCityIds'));
        }

        return view('admin.frontend-users.show', compact('frontend_user', 'assignedLocations', 'groupedCmes', 'userCmeIds', 'userCityIds'));
    }

    /**
     * Show the form for editing the frontend user
     */
    public function edit(FrontendUser $frontend_user)
    {
        // Get CMEs with their cities and sectors
        $cmes = DB::table('cmes')
            ->select('cmes.id', 'cmes.name as cme_name', 'cities.id as city_id', 'cities.name as city_name', 'sectors.id as sector_id', 'sectors.name as sector_name')
            ->leftJoin('cities', 'cmes.id', '=', 'cities.cme_id')
            ->leftJoin('sectors', 'cmes.id', '=', 'sectors.cme_id')
            ->where('cmes.status', '=', 'active')
            ->orderBy('cmes.name')
            ->orderBy('cities.name')
            ->orderBy('sectors.name')
            ->get();

        // Load user's current CME and city privileges from JSON columns
        $userCmeIds = $frontend_user->cme_ids ?? [];
        $userCityIds = $frontend_user->group_ids ?? [];

        // Determine if user is "Super Admin" (has ALL privileges)
        $totalActiveCmes = DB::table('cmes')->where('status', 'active')->count();
        $totalActiveCities = DB::table('cities')->where('status', 'active')->count();
        $isSuperAdmin = (count($userCmeIds) === $totalActiveCmes) && (count($userCityIds) === $totalActiveCities) && ($totalActiveCmes > 0) && ($totalActiveCities > 0);

        return view('admin.frontend-users.edit', compact('frontend_user', 'cmes', 'userCmeIds', 'userCityIds', 'isSuperAdmin'));
    }

    /**
     * Update the specified frontend user
     */
    public function update(Request $request, FrontendUser $frontend_user)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:100|unique:frontend_users,username,' . $frontend_user->id,
            'name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:150|unique:frontend_users,email,' . $frontend_user->id,
            'phone' => 'nullable|string|min:11|max:20',
            'password' => 'nullable|string|min:6|confirmed',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $updateData = [
                'username' => $request->username,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'status' => $request->status,
            ];

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $frontend_user->update($updateData);

            // Handle "Grant all privileges" (Super Admin)
            if ($request->has('is_super_admin') && $request->is_super_admin == 1) {
                // Assign ALL CMEs, Cities, and Sectors
                $frontend_user->cme_ids = DB::table('cmes')->where('status', 'active')->pluck('id')->toArray();
                $frontend_user->group_ids = DB::table('cities')->where('status', 'active')->pluck('id')->toArray();
                $frontend_user->node_ids = DB::table('sectors')->where('status', 'active')->pluck('id')->toArray();
                $frontend_user->save();
            } else {
                // If "Grant all privileges" is unchecked, remove all privileges
                $frontend_user->cme_ids = [];
                $frontend_user->group_ids = [];
                $frontend_user->node_ids = [];
                $frontend_user->save();
            }

            return redirect()->route('admin.frontend-users.index')
                ->with('success', 'Frontend user updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating frontend user: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified frontend user
     */
    public function destroy(FrontendUser $frontend_user)
    {
        $frontend_user->delete();

        if (request()->expectsJson() || request()->ajax() || request()->header('Accept') === 'application/json') {
            return response()->json([
                'success' => true,
                'message' => 'Frontend user deleted successfully.'
            ]);
        }

        return redirect()->route('admin.frontend-users.index')
            ->with('success', 'Frontend user deleted successfully.');
    }

    /**
     * Toggle frontend user status
     */
    public function toggleStatus(FrontendUser $frontend_user)
    {
        $frontend_user->update([
            'status' => $frontend_user->status === 'active' ? 'inactive' : 'active'
        ]);

        $status = $frontend_user->status === 'active' ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "Frontend user {$status} successfully.");
    }

    /**
     * Get assign locations form data
     */
    public function getAssignForm(FrontendUser $frontend_user)
    {
        $cities = City::where('status', 'active')
            ->with(['cme', 'sectors' => function($query) {
                $query->where('status', 'active')->orderBy('id', 'asc');
            }])
            ->orderBy('id', 'asc')
            ->get();

        // Get already assigned locations from JSON columns
        $assignedCityIds = $frontend_user->group_ids ?? [];
        $assignedSectorIds = $frontend_user->node_ids ?? [];

        // Format cities data for JSON response
        $citiesData = $cities->map(function($city) {
            return [
                'id' => $city->id,
                'name' => $city->name,
                'status' => $city->status,
                'cme_id' => $city->cme_id,
                'cme_name' => $city->cme ? $city->cme->name : 'N/A',
                'sectors' => $city->sectors->map(function($sector) {
                    return [
                        'id' => $sector->id,
                        'name' => $sector->name,
                        'city_id' => $sector->city_id,
                        'status' => $sector->status,
                    ];
                })->toArray(),
            ];
        })->toArray();

        return response()->json([
            'cities' => $citiesData,
            'assignedCityIds' => $assignedCityIds,
            'assignedSectorIds' => $assignedSectorIds,
            'allCmes' => DB::table('cmes')->where('status', 'active')->select('id', 'name')->orderBy('name')->get(),
            'allCities' => DB::table('cities')->where('status', 'active')->select('id', 'name', 'cme_id')->orderBy('name')->get(),
            'userCmeIds' => $frontend_user->cme_ids ?? [],
            'userCityIds' => $frontend_user->group_ids ?? [],
        ]);
    }

    /**
     * Assign locations (cities and sectors) to frontend user
     */
    public function assignLocations(Request $request, FrontendUser $frontend_user)
    {
        $validator = Validator::make($request->all(), [
            'city_ids' => 'nullable|array',
            'city_ids.*' => 'exists:cities,id',
            'sector_ids' => 'nullable|array',
            'sector_ids.*' => 'exists:sectors,id',
            'privilege_cme_ids' => 'nullable|array',
            'privilege_cme_ids.*' => 'exists:cmes,id',
            'privilege_city_ids' => 'nullable|array',
            'privilege_city_ids.*' => 'exists:cities,id',
            'sector_ids' => 'nullable|array',
            'sector_ids.*' => 'exists:sectors,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Save Privileges to JSON columns
            $privilegeCmeIds = $request->input('privilege_cme_ids', []);
            $privilegeCityIds = $request->input('privilege_city_ids', []);
            $sectorIds = $request->input('sector_ids', []);

            // Update user's JSON columns
            $frontend_user->cme_ids = $privilegeCmeIds;
            $frontend_user->group_ids = $privilegeCityIds;
            $frontend_user->node_ids = $sectorIds;
            $frontend_user->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Privileges and locations assigned successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error assigning locations: ' . $e->getMessage()
            ], 500);
        }
    }
}
