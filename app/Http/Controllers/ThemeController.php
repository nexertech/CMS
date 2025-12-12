<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

class ThemeController extends Controller
{
    /**
     * Update user's theme preference
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'theme' => 'required|string|in:light,dark,night,auto'
        ]);

        $theme = $request->input('theme');

        // Update user preference if authenticated
        if (Auth::check()) {
            Auth::user()->update(['theme' => $theme]);
        }

        // Set cookie for persistence across sessions
        $cookie = Cookie::make('theme', $theme, 60 * 24 * 365); // 1 year

        return response()->json([
            'success' => true,
            'theme' => $theme,
            'message' => 'Theme updated successfully'
        ])->withCookie($cookie);
    }

    /**
     * Get current theme preference
     */
    public function get(Request $request): JsonResponse
    {
        $theme = $request->cookie('theme', 'auto');
        
        // If user is authenticated, use their preference
        if (Auth::check() && Auth::user()->theme) {
            $theme = Auth::user()->theme;
        }

        return response()->json([
            'theme' => $theme
        ]);
    }

    /**
     * Get theme class for server-side rendering
     */
    public static function getThemeClass(Request $request): string
    {
        $theme = $request->cookie('theme', 'auto');
        
        // If user is authenticated, use their preference
        if (Auth::check() && Auth::user()->theme) {
            $theme = Auth::user()->theme;
        }

        // Handle auto theme
        if ($theme === 'auto') {
            $prefersDark = $request->header('sec-ch-prefers-color-scheme') === 'dark' ||
                          $request->header('accept') && str_contains($request->header('accept'), 'dark');
            $theme = $prefersDark ? 'dark' : 'light';
        }

        return "theme-{$theme}";
    }
}
