<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test', fn () => response()->json(['ping' => 'kong']));
