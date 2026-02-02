<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RegisteredDevice;
use App\Models\House;
use Illuminate\Support\Facades\Validator;

class DeviceController extends Controller
{
    /**
     * Check if the device is registered and active
     */
    public function checkDevice(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string',
        ]);

        $device = RegisteredDevice::where('device_id', $request->device_id)->first();

        // Check if device exists and is active
        if ($device && $device->is_active) {
            return response()->json([
                'success' => true,
                'message' => 'Device is authorized',
                'is_allowed' => true,
                'device' => [
                    'id' => $device->id,
                    'device_id' => $device->device_id,
                    'device_name' => $device->device_name,
                    'assigned_to_house_no' => $device->assigned_to_house_no,
                    'city_id' => $device->city_id,
                    'sector_id' => $device->sector_id,
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Device not registered or inactive',
            'is_allowed' => false
        ], 404);
    }

    /**
     * Register a new device
     */
    public function registerDevice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'device_id' => 'required|string|unique:registered_devices,device_id',
            'device_name' => 'nullable|string|max:255',
            'assigned_to_house_no' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Find the house to get city_id and sector_id
        $house = House::where('house_no', $request->assigned_to_house_no)
            ->where('status', 'active')
            ->first();

        if (!$house) {
            return response()->json([
                'success' => false,
                'message' => 'House not found or inactive'
            ], 404);
        }

        // Create the device
        $device = RegisteredDevice::create([
            'device_id' => $request->device_id,
            'device_name' => $request->device_name,
            'assigned_to_house_no' => $request->assigned_to_house_no,
            'city_id' => $house->city_id,
            'sector_id' => $house->sector_id,
            'is_active' => true, // Default to active
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Device registered successfully',
            'device' => [
                'id' => $device->id,
                'device_id' => $device->device_id,
                'device_name' => $device->device_name,
                'assigned_to_house_no' => $device->assigned_to_house_no,
                'city_id' => $device->city_id,
                'sector_id' => $device->sector_id,
                'is_active' => $device->is_active,
            ]
        ], 201);
    }

    /**
     * Get device details
     */
    public function getDevice(Request $request)
    {
        $request->validate([
            'device_id' => 'required|string',
        ]);

        $device = RegisteredDevice::with(['city', 'sector'])
            ->where('device_id', $request->device_id)
            ->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'device' => [
                'id' => $device->id,
                'device_id' => $device->device_id,
                'device_name' => $device->device_name,
                'assigned_to_house_no' => $device->assigned_to_house_no,
                'city' => $device->city ? $device->city->name : null,
                'sector' => $device->sector ? $device->sector->name : null,
                'is_active' => $device->is_active,
                'created_at' => $device->created_at->format('Y-m-d H:i:s'),
            ]
        ]);
    }
}
