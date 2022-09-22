<?php

namespace App\Utils;
use Symfony\Component\Uid\Uuid;

class ApiKey {
    public static function generate(): string
    {
        return Uuid::v4();
    }
}

?>
