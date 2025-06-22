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
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        if (!is_numeric($perPage) || $perPage <= 0) {
            $perPage = 10; // Default value
        }
        $query = FoodLog::where('user_id', Auth::id())
        ->when(request()->has('meal_time'), function ($q) {
            return $q->where('meal_time', request()->get('meal_time'));
        })->when(request()->has('date'), function ($q) {
            return $q->whereDate('created_at', request()->get('date'));
        })->when(request()->has('foods'), function ($q) {
            return $q->where('foods', 'like', '%' . request()->get('foods') . '%');
        })->when(request()->has('sort'), function ($q) {
            return $q->orderBy('date', request()->get('sort') === 'asc' ? 'asc' : 'desc');
        });

        $paginated = $query->paginate($perPage)->appends($request->query());
        if($paginated->isEmpty()) {
            return response()->json(['message' => 'No food logs found'], 404);
        }

        return FoodLogResource::collection($paginated)->additional([
            'message' => 'List of food logs',
            'meta' => [
                'total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
            ]
        ]);
    }

    public function store(StoreFoodLogRequest $request)
    {
        $validated = $request->validated();
        $validated['time'] = Carbon::parse($validated['time'])->format('H:i:s');
        $userId = Auth::id();


        $DailyReportCount = DailyReport::where('user_id', $userId)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        if ($DailyReportCount >= 5) {

             $total_foodlog = FoodLog::where('user_id', $userId)
                ->whereDate('created_at', now()->toDateString())
                ->count();
            if($total_foodlog <> 0){
                return response()->json([
                    'message' => 'Daily limit of 5 reports reached. You can no longer modify food logs today.'
                ], 403);
            }
        }

        DB::beginTransaction();
        try {
            $log = FoodLog::updateOrCreate(
                [
                    'user_id'   => $userId,
                    'meal_time' => $validated['meal_time'],
                ],
                [
                    'time'      => $validated['time'],
                    'foods'     => $validated['foods'],
                    'symptoms'  => $validated['symptoms'] ?? null,
                    'concerns'  => $validated['concerns'] ?? null,
                ]
            );

            DB::commit();
            return new FoodLogResource($log);

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json([
                'message' => 'Something went wrong while saving your food log.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $log = FoodLog::where('user_id', Auth::id())->findOrFail($id);
        return new FoodLogResource($log);
    }

    public function destroy($id)
    {

        $log = FoodLog::where('user_id', Auth::id())->findOrFail($id);

        // Check if the log was already submitted in a daily report
        $dailyReportExists = DailyReport::where('user_id', $log->user_id)
            ->whereDate('date', $log->created_at->toDateString())
            ->exists();

        if ($dailyReportExists) {
            return response()->json([
                'message' => 'Cannot delete this food log. It has already been submitted in a daily report.'
            ], 403);
        }
        // Check if the log is older than 1 day
        if ($log->created_at < now()->subDay()) {
            return response()->json([
                'message' => 'Cannot delete food logs older than 1 day.'
            ], 403);
        }
        $log->delete();

        return response()->json(['message' => 'Log deleted']);
    }
}
