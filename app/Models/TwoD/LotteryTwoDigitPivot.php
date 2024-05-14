<?php

namespace App\Models\TwoD;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LotteryTwoDigitPivot extends Model
{
    use HasFactory;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        self::boot();  // Ensure the model is booted
    }

    protected $table = 'lottery_two_digit_pivot';

    protected $fillable = ['lottery_id', 'twod_game_result_id', 'two_digit_id',  'user_id', 'bet_digit', 'sub_amount', 'prize_sent', 'match_status', 'res_date', 'res_time', 'session', 'admin_log', 'user_log', 'play_date', 'play_time'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function booted()
    {
        static::created(function ($pivot) {
            LotteryTwoDigitCopy::create($pivot->toArray());
        });
    }
}
