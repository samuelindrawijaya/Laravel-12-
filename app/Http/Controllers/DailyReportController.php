<?php
namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\FoodLog;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DailyReportController extends Controller
{
    protected $gemini;

    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date'        => 'required|date',
            'symptoms'    => 'nullable|string|max:255',
            'concerns'    => 'nullable|string|max:255',
            'notes'       => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $date = $request->date;
        // Cek apakah sudah ada laporan harian untuk tanggal ini
        if (!$date) {
            $date = now()->format('Y-m-d');
        } else {
            $date = date('Y-m-d', strtotime($date));
        }
        if (DailyReport::where('user_id', $user->id)->whereDate('date', $date)->exists()) {
            return response()->json(['message' => 'Report for this date already exists'], 409);
        }
        // Ambil semua foodlog user di tanggal itu
        $logs = FoodLog::where('user_id', $user->id)
                    ->whereDate('created_at', $date)
                    ->get();

        if ($logs->isEmpty()) {
            return response()->json(['message' => 'No food logs found for this date'], 404);
        }

        // Gabungkan deskripsi makanan
        $textLogs = $logs->pluck('description')->implode("\n");

        // Kirim ke Gemini
        $aiResult = $this->gemini->analyzeDailyLogs($textLogs);

        // Simpan laporan harian
        $report = DailyReport::updateOrCreate(
            ['user_id' => $user->id, 'date' => $date],
            [
                'symptoms'   => $request->symptoms,
                'concerns'   => $request->concerns,
                'notes'      => $request->notes,
                'summary'    => $aiResult['summary'] ?? null,
                'suggestion' => $aiResult['suggestion'] ?? null,
                'concern'    => $aiResult['concern'] ?? null,
                'score'      => $aiResult['score'] ?? null,
            ]
        );

        return response()->json([
            'message' => 'Daily report saved',
            'report'  => $report
        ]);
    }

    public function show($id)
    {
        $report = DailyReport::findOrFail($id);

        // Cek apakah report ini milik user yang sedang login
        if ($report->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Optional: sertakan juga food logs untuk tanggal tersebut
        $foodLogs = FoodLog::where('user_id', $report->user_id)
                    ->whereDate('created_at', $report->date)
                    ->get();

        return response()->json([
            'report' => $report,
            'food_logs' => $foodLogs
        ]);
    }

    public function index()
    {
        $user = Auth::user();

        $reports = DailyReport::where('user_id', $user->id)
                    ->orderBy('date', 'desc')
                    ->get();

        return response()->json($reports);
    }

}

