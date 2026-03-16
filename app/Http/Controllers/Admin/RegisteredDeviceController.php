<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RegisteredDevice;
use App\Models\City;
use App\Models\Sector;
use App\Models\House;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\LocationFilterTrait;
use Illuminate\Support\Facades\Auth;

class RegisteredDeviceController extends Controller
{
    use LocationFilterTrait;

    public function index(Request $request)
    {
        $query = RegisteredDevice::with(['city', 'sector']);

        $user = Auth::user();
        if (!empty($user->city_ids)) {
            $query->whereIn('city_id', $user->city_ids);
        }
        if (!empty($user->sector_ids)) {
            $query->whereIn('sector_id', $user->sector_ids);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('device_id', 'like', "%{$search}%")
                  ->orWhere('device_name', 'like', "%{$search}%")
                  ->orWhere('assigned_to_house_no', 'like', "%{$search}%");
            });
        }

        $devices = $query->orderBy('created_at', 'desc')->paginate(15);
        return view('admin.registered_devices.index', compact('devices'));
    }

    public function create()
    {
        $user = Auth::user();
        $cityIds = $this->getUserCityIds($user);
        
        $citiesQuery = City::where('status', 'active');
        if ($cityIds !== null) {
            $citiesQuery->whereIn('id', $cityIds);
        }
        $cities = $citiesQuery->orderBy('name')->get();
        
        $defaultCityId = !empty($user->city_ids) ? $user->city_ids[0] : null;
        $defaultSectorId = !empty($user->sector_ids) ? $user->sector_ids[0] : null;
        
        // Initial sectors if city is pre-determined
        $sectors = collect();
        if ($defaultCityId) {
             $sectorIds = $this->getUserSectorIds($user);
             $sectorsQuery = Sector::where('city_id', $defaultCityId)->where('status', 'active');
             if ($sectorIds !== null) {
                 $sectorsQuery->whereIn('id', $sectorIds);
             }
             $sectors = $sectorsQuery->orderBy('name')->get();
        }

        return view('admin.registered_devices.create', compact('cities', 'sectors', 'defaultCityId', 'defaultSectorId'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|unique:registered_devices,device_id',
            'device_name' => 'nullable|string',
            'assigned_to_house_no' => 'nullable|string',
            'city_id' => 'nullable|exists:cities,id',
            'sector_id' => 'nullable|exists:sectors,id',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Use logged-in user's city and sector if they have them (for data isolation)
        $cityId = (!empty($user->city_ids) ? $user->city_ids[0] : null) ?? $request->city_id;
        $sectorId = (!empty($user->sector_ids) ? $user->sector_ids[0] : null) ?? $request->sector_id;

        RegisteredDevice::create([
            'device_id' => $request->device_id,
            'device_name' => $request->device_name,
            'assigned_to_house_no' => $request->assigned_to_house_no,
            'city_id' => $cityId,
            'sector_id' => $sectorId,
            'is_active' => $request->status,
        ]);

        return redirect()->route('admin.registered-devices.index')->with('success', 'Device registered successfully');
    }

    public function edit(RegisteredDevice $registeredDevice)
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

        // Filter sectors for the device's city
        $sectors = collect();
        if ($registeredDevice->city_id) {
            $sectorsQuery = Sector::where('city_id', $registeredDevice->city_id)->where('status', 'active');
            if ($sectorIds !== null) {
                $sectorsQuery->whereIn('id', $sectorIds);
            }
            $sectors = $sectorsQuery->orderBy('name')->get();
        }
        
        // Get houses for the device's sector
        $houses = collect();
        if ($registeredDevice->sector_id) {
            $houses = House::where('sector_id', $registeredDevice->sector_id)
                ->where('status', 'active')
                ->orderBy('house_no')
                ->get();
        }

        return view('admin.registered_devices.edit', compact('registeredDevice', 'cities', 'sectors', 'houses'));
    }

    public function update(Request $request, RegisteredDevice $registeredDevice)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|unique:registered_devices,device_id,' . $registeredDevice->id,
            'device_name' => 'nullable|string',
            'assigned_to_house_no' => 'nullable|string',
            'city_id' => 'nullable|exists:cities,id',
            'sector_id' => 'nullable|exists:sectors,id',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Use logged-in user's city and sector if they have them (for data isolation)
        $cityId = (!empty($user->city_ids) ? $user->city_ids[0] : null) ?? $request->city_id;
        $sectorId = (!empty($user->sector_ids) ? $user->sector_ids[0] : null) ?? $request->sector_id;

        $registeredDevice->update([
            'device_id' => $request->device_id,
            'device_name' => $request->device_name,
            'assigned_to_house_no' => $request->assigned_to_house_no,
            'city_id' => $cityId,
            'sector_id' => $sectorId,
            'is_active' => $request->status,
        ]);

        return redirect()->route('admin.registered-devices.index')->with('success', 'Device updated successfully');
    }

    public function destroy(RegisteredDevice $registeredDevice)
    {
        try {
            $registeredDevice->delete();
            return response()->json(['success' => true, 'message' => 'Device deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete device'], 500);
        }
    }

    /**
     * Get houses by sector (AJAX)
     */
    public function getHousesBySector(Request $request)
    {
        $sectorId = $request->input('sector_id');
        
        if (!$sectorId || $sectorId <= 0) {
            return response()->json(['houses' => []]);
        }

        $houses = House::where('sector_id', $sectorId)
            ->where('status', 'active')
            ->orderBy('house_no', 'asc')
            ->get(['id', 'house_no', 'name']); // Fetch name if needed for display like "H-101 (Owner Name)"
        
        return response()->json(['houses' => $houses]);
    }
}
