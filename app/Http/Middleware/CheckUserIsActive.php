<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserIsActive
{
    public function handle($request, Closure $next)
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'User inactive'], 403);
        }

        return $next($request);
    }
}

