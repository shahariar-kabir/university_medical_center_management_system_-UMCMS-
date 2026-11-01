<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Please log in to access this page.');
        }

        $user = Auth::user();

        // Admin has access to everything
        if ($user->role === 'admin') {
            return $next($request);
        }

        if (in_array($user->role, $roles, true)) {
            return $next($request);
        }

        // Redirect to user's appropriate portal based on role
        $destination = match ($user->role) {
            'doctor' => 'doctor.dashboard',
            'pharmacist' => 'pharmacy.dashboard',
            'patient' => 'patient.dashboard',
            default => 'home',
        };

        return redirect()->route($destination)->with('error', 'You are not authorized to view that page.');
    }
}
