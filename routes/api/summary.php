<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DailyReportController;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;


Route::middleware(['auth:api', 'is_active'])->group(function () {
    Route::apiResource('daily-reports', DailyReportController::class)->only([
        'index', 'store', 'show'
    ]);
});
