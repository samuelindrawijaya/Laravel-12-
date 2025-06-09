<?php

namespace App\Providers;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Response::macro('success', function ($data = null, $message = 'Success', $status = 200) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $data
            ], $status);
        });

        Response::macro('error', function ($message = 'Error', $status = 400, $errors = null) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => $errors
            ], $status);
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email');
            return Limit::perMinute(5)->by($request->ip() . '|' . $email);
        });

        Route::middleware('api')
            ->prefix('api/test')
            ->group(base_path('routes/api/test_api.php'));

        Route::middleware('api')
            ->prefix('api/public')
            ->group(base_path('routes/api/public.php'));

        Route::middleware('api')
            ->prefix('api/auth')
            ->group(base_path('routes/api/auth.php'));

        Route::middleware('api')
            ->prefix('api/summary')
            ->group(base_path('routes/api/summary.php'));

        Route::middleware('web')
            ->group(base_path('routes/web.php'));
    }

}
