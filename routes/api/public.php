<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\UserManagementController;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;


Route::middleware(['auth:api'])->group(function () {
    Route::get('/profile/{user}', [UserProfileController::class, 'showPublic']);
});
