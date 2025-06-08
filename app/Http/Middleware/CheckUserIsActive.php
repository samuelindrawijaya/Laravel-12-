<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserIsActive
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && !auth()->user()->is_active) {
            return response()->json(['message' => 'Akun Anda tidak aktif.'], 403);
        }

        return $next($request);
    }
}

