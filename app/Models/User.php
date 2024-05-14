<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Carbon\Carbon;
use App\Models\Admin\Role;
use App\Models\Admin\Event;
use App\Models\Admin\Lottery;
use App\Models\Admin\TwodWiner;
use App\Models\Admin\BetLottery;
use App\Models\Admin\Permission;
use App\Models\ThreeDigit\Lotto;
use App\Models\Admin\FillBalance;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin\LotteryTwoDigit;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'profile',
        'email',
        'password',
        'profile',
        'profile_mime',
        'profile_size',
        'phone',
        'address',
        'kpay_no',
        'cbpay_no',
        'wavepay_no',
        'ayapay_no',
        'balance',
        'agent_id',
        'status'
    ];
    protected $dates = ['created_at', 'updated_at'];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getIsAdminAttribute()
    {
        return $this->roles()->where('id', 1)->exists();
    }

    public function getIsMasterAttribute()
    {
        return $this->roles()->where('id', 2)->exists();
    }

    public function getIsAgentAttribute()
    {
        return $this->roles()->where('id', 3)->exists();
    }
    public function getIsUserAttribute()
    {
        return $this->roles()->where('id', 4)->exists();
    }


    public function getEmailVerifiedAtAttribute($value)
    {
        return $value ? Carbon::createFromFormat('Y-m-d H:i:s', $value)->format(config('panel.date_format') . ' ' . config('panel.time_format')) : null;
    }

    public function setEmailVerifiedAtAttribute($value)
    {
        $this->attributes['email_verified_at'] = $value ? Carbon::createFromFormat(config('panel.date_format') . ' ' . config('panel.time_format'), $value)->format('Y-m-d H:i:s') : null;
    }

    public function setPasswordAttribute($input)
    {
        if ($input) {
            $this->attributes['password'] = app('hash')->needsRehash($input) ? Hash::make($input) : $input;
        }
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }


    public function roles()
    {
        return $this->belongsToMany(Role::class);

    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
    public function hasRole($role)
    {
        return $this->roles->contains('title', $role);
    }

    public function hasPermission($permission)
    {
        return $this->roles->flatMap->permissions->pluck('title')->contains($permission);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    // Other users that this user (a master) has created (agents)
    public function createdAgents()
    {
        return $this->hasMany(User::class, 'agent_id');
    }

    // The master that created this user (an agent)
    public function createdByMaster()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function lotteries()
{
    return $this->hasMany(Lottery::class);
}

public function twodWiners()
    {
        return $this->belongsToMany(TwodWiner::class);
    }

 public function balancedecrement($column, $amount = 1)
    {
        $this->$column = $this->$column - $amount;
        return $this->save();
    }
   
    public function fillBalances()
    {
        return $this->hasMany(FillBalance::class);
    }

//     public static function getUserEarlyMorningTwoDigits($userId) {
//     // Define a time range from today at 5:00 PM to tomorrow at 9:00 AM
//     $startTime = Carbon::now()->setTime(17, 0, 0); // Today at 5:00 PM
//     $endTime = Carbon::tomorrow()->setTime(9, 0, 0); // Tomorrow at 9:00 AM

//     // Retrieve data from the 'lottery_two_digit_pivot' table where the created_at column falls within the specified time range
//     $morningTwoDigits = Lottery::where('user_id', $userId)
//         ->with('twoDigitsEarlyMorning')
//         ->whereHas('twoDigitsEarlyMorning', function ($query) use ($startTime, $endTime) {
//             $query->whereBetween('created_at', [$startTime, $endTime]);
//         })
//         ->get()
//         ->pluck('twoDigitsEarlyMorning')
//         ->collapse(); // Collapse the collection to a single dimension

//     // Sum the 'sub_amount' from the pivot table for the retrieved data
//     $totalAmount = $morningTwoDigits->sum(function ($twoDigit) {
//         return $twoDigit->pivot->sub_amount;
//     });

//     return [
//         'two_digits' => $morningTwoDigits,
//         'total_amount' => $totalAmount
//     ];
// }

    public static function getUserEarlyMorningTwoDigits($userId) {
    $morningTwoDigits = Lottery::where('user_id', $userId)
                               ->with('twoDigitsEarlyMorning')
                               ->get()
                               ->pluck('twoDigitsEarlyMorning')
                               ->collapse(); // Collapse the collection to a single dimension

    // Sum the sub_amount from the pivot table
    $totalAmount = $morningTwoDigits->sum(function ($twoDigit) {
        return $twoDigit->pivot->sub_amount;
    });

    return [
        'two_digits' => $morningTwoDigits,
        'total_amount' => $totalAmount
    ];
}

    public static function getUserMorningTwoDigits($userId) {
    $morningTwoDigits = Lottery::where('user_id', $userId)
                               ->with('twoDigitsMorning')
                               ->get()
                               ->pluck('twoDigitsMorning')
                               ->collapse(); // Collapse the collection to a single dimension

    // Sum the sub_amount from the pivot table
    $totalAmount = $morningTwoDigits->sum(function ($twoDigit) {
        return $twoDigit->pivot->sub_amount;
    });

    return [
        'two_digits' => $morningTwoDigits,
        'total_amount' => $totalAmount
    ];
}

public static function getUserEarlyEveningTwoDigits($userId) {
    $morningTwoDigits = Lottery::where('user_id', $userId)
                               ->with('twoDigitsEarlyEvening')
                               ->get()
                               ->pluck('twoDigitsEarlyEvening')
                               ->collapse(); // Collapse the collection to a single dimension

    // Sum the sub_amount from the pivot table
    $totalAmount = $morningTwoDigits->sum(function ($twoDigit) {
        return $twoDigit->pivot->sub_amount;
    });

    return [
        'two_digits' => $morningTwoDigits,
        'total_amount' => $totalAmount
    ];
}


public static function getUserEveningTwoDigits($userId) {
    $morningTwoDigits = Lottery::where('user_id', $userId)
                               ->with('twoDigitsEvening')
                               ->get()
                               ->pluck('twoDigitsEvening')
                               ->collapse(); // Collapse the collection to a single dimension

    // Sum the sub_amount from the pivot table
    $totalAmount = $morningTwoDigits->sum(function ($twoDigit) {
        return $twoDigit->pivot->sub_amount;
    });

    return [
        'two_digits' => $morningTwoDigits,
        'total_amount' => $totalAmount
    ];
}

// three d
public function betLotteries()
{
    return $this->hasMany(BetLottery::class);
}


public static function getUserThreeDigits($userId) {
    $displayThreeDigits = Lotto::where('user_id', $userId)
                               ->with('DisplayThreeDigits')
                               ->get()
                               ->pluck('DisplayThreeDigits')
                               ->collapse(); 
    $totalAmount = $displayThreeDigits->sum(function ($threeDigit) {
        return $threeDigit->pivot->sub_amount;
    });

    return [
        'threeDigit' => $displayThreeDigits,
        'total_amount' => $totalAmount
    ];
}



}