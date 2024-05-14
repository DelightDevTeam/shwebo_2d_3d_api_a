<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\TwoD\Lottery;
use App\Models\TwoD\TwoDigit;
use App\Models\TwoD\HeadDigit;
use App\Models\TwoD\TwoDLimit;
use App\Models\TwoD\TwodSetting;
use Illuminate\Support\Facades\DB;
use App\Models\TwoD\TwodGameResult;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\TwoD\LotteryTwoDigitPivot;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TwoDPlayService
{
    
     public function play($totalAmount, array $amounts)
    {
        // Check for authentication
        if (!Auth::check()) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        $user = Auth::user();

        DB::beginTransaction();

        try {
            // Access `Limit` with error handling
            $limit = $user->limit ?? null;

            if ($limit === null) {
                throw new \Exception("Commission rate 'limit' is not set for user.");
            }

            if ($user->main_balance < $totalAmount) {
                return "Insufficient funds.";
            }

            $preOver = [];
            foreach ($amounts as $amount) {
                $preCheck = $this->preProcessAmountCheck($amount);
                if (is_array($preCheck)) {
                    $preOver[] = $preCheck[0];
                }
            }
            if (!empty($preOver)) {
                return $preOver;
            }

            // Create a new lottery entry
            $lottery = Lottery::create([
                'pay_amount' => $totalAmount,
                'total_amount' => $totalAmount,
                'user_id' => $user->id,
            ]);

            $over = [];
            foreach ($amounts as $amount) {
                $check = $this->processAmount($amount, $lottery->id);
                if (is_array($check)) {
                    $over[] = $check[0];
                }
            }
            if (!empty($over)) {
                return $over;
            }

            $user->balance -= $totalAmount;
            $user->save();

            DB::commit();

            return "Bet placed successfully.";

        } catch (ModelNotFoundException $e) {
            DB::rollback();
            Log::error('Model not found in TwoDService play method: ' . $e->getMessage());
            return "Resource not found.";
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error in TwoDService play method: ' . $e->getMessage());
            return $e->getMessage(); // Handle general exceptions
        }
    }

    
    protected function preProcessAmountCheck($amount)
{
    $twoDigit = str_pad($amount['num'], 2, '0', STR_PAD_LEFT); // Ensure two-digit format
    $break = Auth::user()->limit ?? 0; // Set default value if `cor` is not set
    
    Log::info("User's commission limit (limit): {$break}");
    Log::info("Checking bet_digit: {$twoDigit}");

    $totalBetAmountForTwoDigit = DB::table('lottery_two_digit_pivot')
                        ->where('bet_digit', $twoDigit)
                        ->sum('sub_amount');

    Log::info("Total bet amount for {$twoDigit}: {$totalBetAmountForTwoDigit}");

    $subAmount = $amount['amount'];

    if ($totalBetAmountForTwoDigit + $subAmount > $break) {
        Log::warning("Bet on {$twoDigit} exceeds limit.");
        return [$amount['num']]; // Indicates over-limit
    }

    return null; // Indicates no over-limit
}


    protected function processAmount($amount, $lotteryId)
    {
        
    $twoDigit = str_pad($amount['num'], 2, '0', STR_PAD_LEFT); // Ensure three-digit format

        //$break = TwoDLimit::latest()->first()->two_d_limit;
        $break = Auth::user()->limit;
        
        $totalBetAmountForTwoDigit = DB::table('lottery_two_digit_pivot')
                        ->where('bet_digit', $twoDigit)
                        ->sum('sub_amount');
        $subAmount = $amount['amount'];
        $betDigit = $amount['num'];

        if ($totalBetAmountForTwoDigit + $subAmount <= $break) {
            $today = Carbon::now()->format('Y-m-d');
        // Retrieve results for today where status is 'open'
        $results = TwodSetting::where('result_date', $today) // Match today's date
                             ->where('status', 'open')      // Check if the status is 'open'
                             ->first();

            LotteryTwoDigitPivot::create([
                'lottery_id' => $lotteryId,
                'twod_game_result_id' => $results->id,
                'bet_digit' => $betDigit,
                'sub_amount' => $subAmount,
                'prize_sent' => false,
                'match_status' => $results->status,
                'res_date' => $results->result_date,
                'res_time' => $results->result_time,
                'session' => $results->session

            ]);
        } else {
            // Handle the case where the bet exceeds the limit
            return [$amount['num']];
        }
    }

    private function determineSession()
    {
        return date('H') < 12 ? 'morning' : 'evening';
    }
}