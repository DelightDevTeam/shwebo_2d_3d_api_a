<?php

namespace App\Models\TwoD;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TwodSetting extends Model
{
    use HasFactory;
    protected $fillable = ['result_date', 'result_time', 'result_number', 'session', 'status'];

    // protected static function booted()
    // {
    //     static::updated(function ($twodWiner) {
    //          if ($twodWiner->session === 'morning') {
    //         CheckForMorningWinners::dispatch($twodWiner);
    //         }else {
    //         CheckForEveningWinners::dispatch($twodWiner);
    //         }
    //     });
    // }
}
