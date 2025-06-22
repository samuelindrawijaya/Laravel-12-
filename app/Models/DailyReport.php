<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class DailyReport extends Model
{


    protected $fillable = ['user_id', 'date', 'summary', 'suggestion', 'concern', 'score'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function foodLogs()
    {
        return $this->hasMany(FoodLog::class, 'user_id', 'user_id')
            ->whereDate('created_at', $this->date);
    }

    public function getBadgesAttribute(): array
    {
        if ($this->score === 100) {
            return ['❤️', '🧠', '⭐'];
        } elseif ($this->score >= 80) {
            return ['❤️', '⭐'];
        } elseif ($this->score >= 60) {
            return ['❤️'];
        }
        return [];
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }
}

