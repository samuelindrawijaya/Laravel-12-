<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api', 'role:admin'])->get('/admin-only', function () {
    return response()->json(['message' => 'Welcome, admin!']);
});

Route::middleware(['auth:api', 'role:user,admin'])->get('/user-or-admin', function () {
    return response()->json(['message' => 'Accessible by user or admin']);
});
Route::middleware(['auth:api'])->get('/user-only', function () {
    return response()->json(['message' => 'Welcome, user!']);
});
