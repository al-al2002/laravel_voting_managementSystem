<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        Log::info('IsAdmin check', [
            'has_auth_type' => session()->has('auth_type'),
            'auth_type' => session('auth_type'),
            'auth_id' => session('auth_id'),
            'all_session' => session()->all()
        ]);

        if (!session()->has('auth_type') || session('auth_type') !== 'admin') {
            Log::info('Admin access denied - redirecting to login');
            return redirect()->route('login');
        }

        return $next($request);
    }
}
