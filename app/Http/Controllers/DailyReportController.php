<?php
namespace App\Http\Controllers;

use App\Models\DailyReport;
use App\Models\FoodLog;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Resources\DailyReportResource;

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

        // Hitung laporan hari ini
        $reportCountToday = DailyReport::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->count();

        if ($reportCountToday >= 5 || $user->geminitoken <= 0) {
            if (DailyReport::where('user_id', $user->id)->whereDate('date', $date)->exists()) {
               return response()->json(['message' => 'Batas harian tercapai atau token habis.'], 403);
            }
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
        DB::beginTransaction();
        try {
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
            // Kurangi token Gemini
            $user->geminitoken = max(0, $user->geminitoken - 1); // Pastikan token tidak negatif
            $user->save();
            DB::commit();

            return response()->json([
                'message' => 'Daily report saved',
                'report'  => $report
            ]);
        }
        catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error processing AI response', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $report = DailyReport::with(['foodLogs'])->findOrFail($id);
        if (!$report) {
            return response()->json(['message' => 'Daily report not found'], 404);
        }

        // Cek apakah report ini milik user yang sedang login
        if ($report->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return new DailyReportResource($report);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        // $query = DailyReport::where('user_id', $user->id);

        // if ($request->has('date')) {
        //     $query->forDate($request->get('date'));
        //  manually overide the date filter}
        $query = DailyReport::where('user_id', $user->id)
        ->when($request->has('date'), function ($q) use ($request) {
            $q->forDate($request->get('date'));
        })->when($request->has('sort'), function ($q) use ($request) {
            if (in_array($request->get('sort'), ['asc', 'desc'])) {
                $q->orderBy('date', $request->get('sort'));
            } else {
                $q->orderBy('date', 'desc');
            }
        }, function ($q) {
            $q->OrderBy('date', 'desc');
        });

        $paginated = $query->paginate($perPage);

        if ($paginated->isEmpty()) {
            return response()->json([
                'message' => 'No daily reports found'
            ], 404);
        }

        return DailyReportResource::collection($paginated)
        ->additional([
            'message' => 'List of daily reports',
            'meta' => [
                'total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
            ]
        ]);
    }
}

