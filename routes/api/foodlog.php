<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use App\Http\Controllers\FoodLogController;

Route::middleware(['auth:api', 'is_active'])->group(function () {
    Route::get('/food-logs', [FoodLogController::class, 'index']);
    Route::post('/food-logs', [FoodLogController::class, 'store']);
    Route::get('/food-logs/{id}', [FoodLogController::class, 'show']);
    Route::delete('/food-logs/{id}', [FoodLogController::class, 'destroy']);
});
