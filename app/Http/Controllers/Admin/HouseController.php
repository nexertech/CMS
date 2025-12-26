<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\House;
use App\Models\City;
use App\Models\Sector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Traits\LocationFilterTrait;
use Exception;

class HouseController extends Controller
{
    use LocationFilterTrait;
    /**
     * Display a listing of the houses.
     */
    public function index(Request $request)
    {
        $query = House::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Filter by city (GE Group)
        if ($request->has('city_id') && $request->city_id) {
            $query->where('city_id', $request->city_id);
        }

        // Filter by sector (GE Node)
        if ($request->has('sector_id') && $request->sector_id) {
            $query->where('sector_id', $request->sector_id);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $user = Auth::user();
        $this->filterHousesByLocation($query, $user);

        $houses = $query->with(['city', 'sector'])->orderBy('id', 'desc')->paginate(10);
        
        // Get cities and sectors for filter dropdowns based on user permissions
        $cityIds = $this->getUserCityIds($user);
        $sectorIds = $this->getUserSectorIds($user);

        $citiesQuery = City::where('status', 'active');
        if ($cityIds !== null) {
            $citiesQuery->whereIn('id', $cityIds);
        }
        $cities = $citiesQuery->orderBy('name')->get();

        $sectorsQuery = Sector::where('status', 'active');
        if ($sectorIds !== null) {
            $sectorsQuery->whereIn('id', $sectorIds);
        }
        $sectors = $sectorsQuery->orderBy('name')->get();
        
        return view('admin.houses.index', compact('houses', 'cities', 'sectors'));
    }

    /**
     * Show the form for creating a new house.
     */
    public function create()
    {
        $user = Auth::user();
        $cityIds = $this->getUserCityIds($user);

        $citiesQuery = City::where('status', 'active');
        if ($cityIds !== null) {
            $citiesQuery->whereIn('id', $cityIds);
        }
        $cities = $citiesQuery->orderBy('name')->get();
        
        $defaultCityId = $user->city_id;
        $defaultSectorId = $user->sector_id;
        
        return view('admin.houses.create', compact('cities', 'defaultCityId', 'defaultSectorId'));
    }

    /**
     * Store a newly created house in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:150|unique:houses,username',
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'city_id' => 'required|exists:cities,id',
            'sector_id' => 'required|exists:sectors,id',
            'address' => 'nullable|string|max:500',
            'status' => 'nullable|in:active,inactive',
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

            $house = House::create([
                'username' => $request->username,
                'name' => $request->name,
                'phone' => $request->phone,
                'password' => $request->password, // Will be hashed by mutator
                'city_id' => $request->city_id,
                'sector_id' => $request->sector_id,
                'address' => $request->address,
                'status' => $request->status ?? 'active',
            ]);

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'House created successfully.',
                    'house' => $house
                ]);
            }

            return redirect()->route('admin.houses.index')
                ->with('success', 'House created successfully.');

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating house: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating house: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Error creating house: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified house.
     */
    public function show(House $house)
    {
        $house->load(['city', 'sector']);
        
        if (request()->ajax() && request()->header('Accept') === 'text/html') {
            return view('admin.houses.show', compact('house'));
        }
        
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'house' => $house
            ]);
        }
        
        return view('admin.houses.show', compact('house'));
    }

    /**
     * Show the form for editing the specified house.
     */
    public function edit(House $house)
    {
        $user = Auth::user();
        $cityIds = $this->getUserCityIds($user);
        $sectorIds = $this->getUserSectorIds($user);

        // Filter cities
        $citiesQuery = City::where('status', 'active');
        if ($cityIds !== null) {
            $citiesQuery->whereIn('id', $cityIds);
        }
        $cities = $citiesQuery->orderBy('name')->get();

        // Filter sectors
        $sectorsQuery = Sector::where('status', 'active')->where('city_id', $house->city_id);
        if ($sectorIds !== null) {
            $sectorsQuery->whereIn('id', $sectorIds);
        }
        $sectors = $sectorsQuery->orderBy('name')->get();
        
        return view('admin.houses.edit', compact('house', 'cities', 'sectors'));
    }

    /**
     * Update the specified house in storage.
     */
    public function update(Request $request, House $house)
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:150', Rule::unique('houses')->ignore($house->id)],
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
            'city_id' => 'required|exists:cities,id',
            'sector_id' => 'required|exists:sectors,id',
            'address' => 'nullable|string|max:500',
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

            $updateData = [
                'username' => $request->username,
                'name' => $request->name,
                'phone' => $request->phone,
                'city_id' => $request->city_id,
                'sector_id' => $request->sector_id,
                'address' => $request->address,
                'status' => $request->status,
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = $request->password; // Will be hashed by mutator
            }

            $house->update($updateData);

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'House updated successfully.',
                    'house' => $house
                ]);
            }

            return redirect()->route('admin.houses.index')
                ->with('success', 'House updated successfully.');
                
        } catch (Exception $e) {
            DB::rollBack();
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating house: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Error updating house: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified house from storage.
     */
    public function destroy($id)
    {
        try {
            $house = House::findOrFail($id);
            
            Log::info('Attempting to soft delete house ID: ' . $house->id);
            
            $house->delete(); // Soft delete
            Log::info('House soft deleted successfully for ID: ' . $house->id);

            if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => true,
                    'message' => 'House deleted successfully.'
                ]);
            }

            return redirect()->route('admin.houses.index')
                ->with('success', 'House deleted successfully.');

        } catch (Exception $e) {
            Log::error('Error deleting house ID ' . ($house->id ?? $id) . ': ' . $e->getMessage());
            
            if (request()->ajax() || request()->wantsJson() || request()->header('Accept') === 'application/json') {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting house: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Error deleting house: ' . $e->getMessage());
        }
    }

    /**
     * Get sectors by city (AJAX)
     */
    public function getSectorsByCity(Request $request)
    {
        $user = Auth::user();
        $cityId = $request->input('city_id');
        
        if (!$cityId || $cityId <= 0) {
            return response()->json(['sectors' => []]);
        }

        $query = Sector::where('city_id', '=', $cityId)
            ->where('status', '=', 'active');

        // Apply data isolation
        $sectorIds = $this->getUserSectorIds($user);
        if ($sectorIds !== null) {
            $query->whereIn('id', $sectorIds);
        }

        $sectors = $query->orderBy('name', 'asc')->get(['id', 'name']);
        
        return response()->json(['sectors' => $sectors]);
    }
}
