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
            'date'      => 'required|date',
            'symptoms'  => 'nullable|string|max:255',
            'concerns'  => 'nullable|string|max:255',
            'notes'     => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $date = date('Y-m-d', strtotime($request->date ?? now()));

        if (DailyReport::where('user_id', $user->id)->whereDate('date', $date)->exists()) {
            // return response()->json(['message' => 'Report for this date already exists'], 409);
        }

        $logs = FoodLog::where('user_id', $user->id)
            ->whereDate('created_at', $date)
            ->get();

        if ($logs->isEmpty()) {
            return response()->json(['message' => 'No food logs found for this date'], 404);
        }

        // Format log makanan
        $textLogs = $logs->map(function ($log) {
            return "Waktu: {$log->meal_time} ({$log->time})\nMakanan: {$log->foods}\nGejala: {$log->symptoms}\nKekhawatiran: {$log->concerns}";
        })->implode("\n\n");

        // Ambil profil user
        $profile = $user->profile;

        // Buat konteks tambahan dari profil
        $hasGerd = $profile->has_gerd ? 'Ya' : 'Tidak';
        $hasAnxiety = $profile->has_anxiety ? 'Ya' : 'Tidak';
        $isOnDiet = $profile->is_on_diet ? 'Ya' : 'Tidak';
        $dietType = $profile->diet_type;
        $personalityNote = $profile->personality_note;
        $dailyGoalNote = $profile->daily_goal_note;

        $profileContext = <<<EOD
        Profil Pengguna:
        - Memiliki GERD: {$hasGerd}
        - Mengalami kecemasan: {$hasAnxiety}
        - Sedang diet: {$isOnDiet}
        - Tipe diet: {$dietType}
        - Catatan kepribadian: {$personalityNote}
        - Catatan tujuan harian: {$dailyGoalNote}
        EOD;

        $combinedInput = $profileContext . "\n\nLog Makanan:\n" . $textLogs;

        // Kirim ke AI
        $aiResult = $this->gemini->analyzeDailyLogs($combinedInput);

        // Simpan laporan
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

