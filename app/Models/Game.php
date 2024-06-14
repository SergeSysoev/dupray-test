<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_date',
        'home_team',
        'away_team',
        'home_score',
        'away_score',
    ];

//    public function getGameDateAttribute($value)
//    {
//        return Carbon::parse($value)->format('Y-m-d');
//    }
}
