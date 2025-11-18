<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if not authenticated
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Skip if user doesn't have a tenant (shouldn't happen)
        if (!$user->tenant) {
            return $next($request);
        }

        $tenant = $user->tenant;
        $settings = $tenant->settings ?? [];

        // Check if profile is completed
        $profileCompleted = $settings['profile_completed'] ?? false;
        $setupRequired = $settings['setup_required'] ?? true;

        // Skip middleware for certain routes
        $excludedRoutes = [
            'profile.setup',
            'logout',
            'profile.skip',
            'api.*'
        ];

        foreach ($excludedRoutes as $pattern) {
            if ($request->routeIs($pattern)) {
                return $next($request);
            }
        }

        // Skip if it's an AJAX request (to prevent redirect loops)
        if ($request->ajax() || $request->wantsJson()) {
            return $next($request);
        }

        // Redirect to profile setup if not completed
        if (!$profileCompleted || $setupRequired) {
            // Only redirect if we're not already on the profile setup page
            if (!$request->routeIs('profile.setup')) {
                return redirect()->route('profile.setup')
                    ->with('message', 'Please complete your business profile to access all features.');
            }
        }

        return $next($request);
    }
}
