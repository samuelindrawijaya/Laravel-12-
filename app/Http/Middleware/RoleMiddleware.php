<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{

    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = auth()->user();
        $user->loadMissing('role');

        if (!$user || !$user->role || !in_array($user->role->name, $roles)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
// This middleware checks if the authenticated user has one of the specified roles.
// If not, it returns a 403 Unauthorized response.
// To use this middleware, you can register it in your `app/Http/Kernel.php` file and apply it to your routes like so:

