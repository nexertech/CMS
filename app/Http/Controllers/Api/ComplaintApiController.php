<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Client;
use App\Models\City;
use App\Models\Sector;
use App\Models\ComplaintLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ComplaintApiController extends Controller
{
    /**
     * Register a new complaint via API
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'category' => 'required|string|max:100',
            'city_id' => 'required|exists:cities,id',
            'sector_id' => 'required|exists:sectors,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'availability_time' => 'nullable|string|max:255',
            'client_email' => 'required|email', // Identifier for the user/client
            'client_name' => 'required|string|max:255',
            'client_phone' => 'nullable|string|max:50',
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
            $city = City::find($request->city_id);
            $sector = Sector::find($request->sector_id);

            // Find or create Client
            $client = Client::firstOrCreate(
                ['email' => $request->client_email],
                [
                    'client_name' => $request->client_name,
                    'contact_person' => $request->client_name,
                    'phone' => $request->client_phone ?? '',
                    'status' => 'active',
                    'city' => $city ? $city->name : '',
                    'sector' => $sector ? $sector->name : '',
                    'address' => 'Auto-created via Mobile API',
                ]
            );

            // Create Complaint
            $complaint = Complaint::create([
                'title' => $request->title,
                'client_id' => $client->id,
                'city_id' => $request->city_id,
                'sector_id' => $request->sector_id,
                'category' => $request->category,
                'priority' => 'medium',
                'description' => $request->description,
                'availability_time' => $request->availability_time,
                'status' => 'new',
            ]);

            // Log activity
            ComplaintLog::create([
                'complaint_id' => $complaint->id,
                'action_by' => null, // Mobile user
                'action' => 'created',
                'remarks' => 'Complaint registered via Mobile API by ' . $request->client_name,
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
                'message' => 'Failed to register complaint. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
