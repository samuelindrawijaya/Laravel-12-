<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\FoodLog;
class DailyReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        $foodLogs = FoodLog::where('user_id', $this->user_id)
            ->whereDate('created_at', $this->date)
            ->get();

        return [
            'id'         => $this->id,
            'date'       => $this->date,
            'summary'    => $this->summary,
            'suggestion' => $this->suggestion,
            'concern'    => $this->concern,
            'score'      => $this->score,
            'badges'     => [
                'heart' => $this->score >= 100,
                'guts'  => $this->score >= 100,
                'star'  => $this->score >= 100,
            ],
            'food_logs'  => $foodLogs, // ← ini tambahan penting
        ];
    }

}
