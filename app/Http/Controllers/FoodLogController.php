<?php

namespace App\Http\Controllers;

use App\Models\FoodLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreFoodLogRequest;
use App\Http\Resources\FoodLogResource;
use Carbon\Carbon;

class FoodLogController extends Controller
{
    public function index()
    {
        $logs = FoodLog::where('user_id', Auth::id())->latest()->get();
        return FoodLogResource::collection($logs);
    }

    public function store(StoreFoodLogRequest $request)
    {
        $validated = $request->validated();
        $validated['time'] = Carbon::parse($validated['time'])->format('H:i:s');

        $exists = FoodLog::where('user_id', Auth::id())
            ->whereDate('created_at', now()->toDateString())
            ->where('meal_time', $validated['meal_time'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Food log already exists for this meal time today.'], 409);
        }

        $log = FoodLog::create([
            'user_id'   => Auth::id(),
            'meal_time' => $validated['meal_time'],
            'time'      => $validated['time'], // ✅ pakai hasil format Carbon
            'foods'     => $validated['foods'],
            'symptoms'  => $validated['symptoms'] ?? null,
            'concerns'  => $validated['concerns'] ?? null,
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
