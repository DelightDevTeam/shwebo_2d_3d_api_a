<?php

namespace App\Http\Controllers\Api\V1\TwoD;

use Illuminate\Http\Request;
use App\Models\TwoD\HeadDigit;
use App\Models\TwoD\TwodSetting;
use App\Services\TwoDPlayService;
use Illuminate\Http\JsonResponse;
use App\Models\TwoD\CloseTwoDigit;
use App\Models\TwoD\TwodGameResult;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\TwoD\TwoDPlayRequest;

class TwoDPlayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(TwoDPlayRequest $request, TwoDPlayService $twoDService): JsonResponse
    {
        $currentDate = TwodSetting::where('status', 'open')->first();

        // If no result date is found or it's closed, return an error
        if (! $currentDate || $currentDate->status === 'closed') {
            return response()->json([
                'success' => false,
                'message' => 'This 2D lottery match is closed for at this time. Welcome back Next Time!',
            ], 401);
        }
        Log::info($request->all());

        // Retrieve the validated data from the request
        $totalAmount = $request->input('totalAmount');
        $amounts = $request->input('amounts');

        try {
            // Fetch all head digits not allowed
            $closedHeadDigits = HeadDigit::query()
                ->get(['digit_one', 'digit_two', 'digit_three'])
                ->flatMap(function ($item) {
                    return [$item->digit_one, $item->digit_two, $item->digit_three];
                })
                ->unique()
                ->all();

            // return response()->json($closedHeadDigits);
            foreach ($amounts as $amount) {
                $headDigitOfSelected = substr(sprintf('%02d', $amount['num']), 0, 1); // Ensure
                if (in_array($headDigitOfSelected, $closedHeadDigits)) {
                    return response()->json(['message' => "ထိပ်ဂဏန်း '{$headDigitOfSelected}'  ကိုပိတ်ထားသောကြောင့် ကံစမ်း၍ မရနိုင်ပါ ၊ ကျေးဇူးပြု၍ ဂဏန်းပြန်ရွှေးချယ်ပါ။ "], 401);
                }
            }

            $closedTwoDigits = CloseTwoDigit::query()
                ->pluck('digit')
                ->map(function ($digit) {
                    // Ensure formatting as a two-digit string
                    return sprintf('%02d', $digit);
                })
                ->unique()
                ->filter()
                ->values()
                ->all();

            foreach ($request->input('amounts') as $amount) {
                $twoDigitOfSelected = sprintf('%02d', $amount['num']); // Ensure two-digit format
                if (in_array($twoDigitOfSelected, $closedTwoDigits)) {
                    return response()->json(['message' => "2D -  '{$twoDigitOfSelected}'  ကိုပိတ်ထားသောကြောင့် ကံစမ်း၍ မရနိုင်ပါ ၊ ကျေးဇူးပြု၍ ဂဏန်းပြန်ရွှေးချယ်ပါ။ "], 401);
                }
            }

            $result = $twoDService->play($totalAmount, $amounts);
            if ($result === 'Insufficient funds.') {
                // Insufficient funds message
                return response()->json(['message' => 'လက်ကျန်ငွေ မလုံလောက်ပါ။'], 401);
            }
            if (is_array($result) && ! empty($result)) {
                $digitStrings = collect($result)->implode(', '); // Over-limit digits
                $message = "သင့်ရွှေးချယ်ထားသော {$digitStrings} ဂဏန်းမှာ သတ်မှတ် အမောင့်ထက်ကျော်လွန်ပါသောကြောင့် ကံစမ်း၍မရနိုင်ပါ။";

                return response()->json(['message' => $message], 401);
            }

            // If $result is neither "Insufficient funds." nor an array, assuming success.
            return $this->success($result);

        } catch (\Exception $e) {
            // In case of an exception, return an error response
            return response()->json(['success' => false, 'message' => $e->getMessage()], 401);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}