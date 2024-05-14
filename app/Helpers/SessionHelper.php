<?php

namespace App\Helpers;

use Carbon\Carbon;

class SessionHelper
{
    /**
     * Determine the current session based on the time of day.
     *
     * @return string
     */
    public static function getCurrentSession()
    {
        $currentTime = Carbon::now()->format('H:i:s');

        if ($currentTime >= '04:00:00' && $currentTime <= '12:01:00') {
            return 'morning';
        } elseif ($currentTime >= '12:01:01' && $currentTime <= '16:30:00') {
            return 'evening';
        } else {
            return 'closed';
        }
    }
}
