<?php

namespace App\Http\Controllers;

use App\Models\FoodLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreFoodLogRequest;
use App\Http\Resources\FoodLogResource;

class FoodLogController extends Controller
{
    public function index()
    {
        $logs = FoodLog::where('user_id', Auth::id())->latest()->get();
        return FoodLogResource::collection($logs);
    }

    public function store(StoreFoodLogRequest $request)
    {
        $log = FoodLog::create([
            'user_id'   => Auth::id(),
            'meal_time' => $request->meal_time,
            'time'      => $request->time,
            'foods'     => $request->foods,
            'symptoms'  => $request->symptoms,
            'concerns'  => $request->concerns,
        ]);

        return new FoodLogResource($log);
    }

    public function show($id)
    {
        $log = FoodLog::where('user_id', Auth::id())->findOrFail($id);
        return new FoodLogResource($log);
    }

    public function destroy($id)
    {
        $log = FoodLog::where('user_id', Auth::id())->findOrFail($id);
        $log->delete();

        return response()->json(['message' => 'Log deleted']);
    }
}
