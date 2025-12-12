<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index(): View
    {
        return view('admin.settings.index');
    }

    /**
     * Update general settings.
     */
    public function updateGeneral(Request $request)
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'site_description' => 'nullable|string|max:500',
            'timezone' => 'required|string',
            'date_format' => 'required|string',
            'time_format' => 'required|string',
        ]);

        // Here you would typically save to database or config files
        // For now, we'll just return success
        
        return redirect()->route('admin.settings.index')
            ->with('success', 'General settings updated successfully!');
    }

    /**
     * Update notification settings.
     */
    public function updateNotifications(Request $request)
    {
        $request->validate([
            'email_notifications' => 'boolean',
            'push_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
        ]);

        // Here you would typically save to database
        // For now, we'll just return success
        
        return redirect()->route('admin.settings.index')
            ->with('success', 'Notification settings updated successfully!');
    }

    /**
     * Update security settings.
     */
    public function updateSecurity(Request $request)
    {
        $request->validate([
            'two_factor_auth' => 'boolean',
            'session_timeout' => 'required|integer|min:5|max:480',
            'password_policy' => 'required|string',
        ]);

        // Here you would typically save to database
        // For now, we'll just return success
        
        return redirect()->route('admin.settings.index')
            ->with('success', 'Security settings updated successfully!');
    }
}
