<?php

class Common
{
    public static function FuzzyDateTime($timestamp)
    {
        $out = "Unknown";
        if ($timestamp === null) {
            return $out;
        }
        $date = new DateTime($timestamp);
        $now = new DateTime();
        $secs = $now->format('U') - $date->format('U');
        if ($secs < 3600) {
            $out = round($secs / 60, 1) . ' minutes ago';
        } else if (($secs > 3600) && ($secs < 86400)) {
            $out = round($secs / 3600, 1) . ' hours ago';
        } else {
            $out = round($secs / 86400, 1) . ' days ago';
        }
        return $out;
    }
}

?>
