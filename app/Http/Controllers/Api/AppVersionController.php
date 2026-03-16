<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppUpdate;

class AppVersionController extends Controller
{
    /**
     * Check for app updates
     */
    public function checkUpdate(Request $request)
    {
        $platform = $request->query('platform', 'android');
        $currentVersionCode = $request->query('version_code');

        if (!$currentVersionCode) {
            return response()->json([
                'success' => false,
                'message' => 'Current version code is required'
            ], 422);
        }

        // Get the latest active update for the platform
        $latestUpdate = AppUpdate::where('platform', $platform)
            ->where('status', 1)
            ->orderBy('version_code', 'desc')
            ->first();

        if (!$latestUpdate) {
            return response()->json([
                'success' => true,
                'update_available' => false,
                'message' => 'No update info found'
            ]);
        }

        $updateAvailable = (int)$latestUpdate->version_code > (int)$currentVersionCode;

        return response()->json([
            'success' => true,
            'update_available' => $updateAvailable,
            'latest_version' => [
                'version_name' => $latestUpdate->version_name,
                'version_code' => (int)$latestUpdate->version_code,
                'is_force_update' => (bool)$latestUpdate->is_force_update,
                'update_url' => $latestUpdate->update_url,
                'message' => $latestUpdate->message,
            ]
        ]);
    }
}
