<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            abort(403, 'Unauthorized access.');
        }

        $user = Auth::user();

        if ($role === 'admin' && !($user instanceof Admin)) {
            abort(403, 'Unauthorized access.');
        }

        if ($role === 'user' && !($user instanceof User)) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
