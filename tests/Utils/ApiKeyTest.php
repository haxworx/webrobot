<?php

namespace App\Tests\Utils;

use PHPUnit\Framework\TestCase;
use App\Utils\ApiKey;

class ApiKeyTest extends TestCase
{
    public function testGenerate(): void
    {
        $key = ApiKey::generate();
        $this->assertTrue(strlen($key) === 64);
    }

}
