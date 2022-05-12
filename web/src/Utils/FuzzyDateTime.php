<?php

namespace App\Utils;

class FuzzyDateTime
{
    public static function get(?\DateTime $dateTime): string
    {
        $out = "n/a";
        if ($dateTime === null) {
            return $out;
        }

        $now = new \DateTime();

        $secs = $now->format('U') - $dateTime->format('U');
        if ($secs < 3600) {
            $mins = round($secs / 60);
            $out = "$mins minute" . ($mins != 1 ? 's' : '') . ' ago';
        } else if (($secs > 3600) && ($secs < 86400)) {
            $hours = round($secs / 3600);
            $out = "$hours hour" . ($hours != 1 ? 's' : '') . ' ago';
        } else {
            $days = round($secs / 86400);
            $out = "$days day" . ($days != 1 ? 's' : '') . ' ago';
        }

        return $out;
    }
}
