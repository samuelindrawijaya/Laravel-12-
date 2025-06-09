<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserIsActive
{
    public function handle($request, Closure $next)
    {
        // $user = auth('api')->user(); // pakai guard yang benar

        // // // if (!$user || !$user->is_active) {
        // //     abort(403, 'User is inactive or unauthorized');
        // // }

        return $next($request);
    }
}

