<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FoodLog extends Model
{
    protected $fillable = [
        'user_id', 'meal_time', 'time', 'foods', 'symptoms', 'concerns'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dailyReport()
    {
        return $this->belongsTo(DailyReport::class, 'date', 'date')
                    ->whereColumn('food_logs.user_id', 'daily_reports.user_id');
    }
}
