<?php

namespace App\Tests\Utils;

use PHPUnit\Framework\TestCase;
use App\Utils\FuzzyDateTime;

class FuzzyDateTimeTest extends TestCase
{
    public function testNullDate(): void
    {
        $result = FuzzyDateTime::get(null);
        $this->assertSame('Unknown', $result);
    }

    public function testPlural(): void
    {
        $then = new \DateTime();

        $then->setTimestamp(time());
        $result = FuzzyDateTime::get($then);
        $this->assertSame('0 minutes ago', $result);

        $then->setTimestamp(time() - 50);
        $result = FuzzyDateTime::get($then);
        $this->assertSame('1 minute ago', $result);

        $then->setTimestamp(time() - (60 * 30));
        $result = FuzzyDateTime::get($then);
        $this->assertSame('30 minutes ago', $result);

        $then->setTimestamp(time() - 3601);
        $result = FuzzyDateTime::get($then);
        $this->assertSame('1 hour ago', $result);

        $then->setTimestamp(time() - (3601 * 2));
        $result = FuzzyDateTime::get($then);
        $this->assertSame('2 hours ago', $result);

        $then->setTimeStamp(time() - (86400 + 1));
        $result = FuzzyDateTime::get($then);
        $this->assertSame('1 day ago', $result);

        $then->setTimeStamp(time() - (10 * 86400 + 1));
        $result = FuzzyDateTime::get($then);
        $this->assertSame('10 days ago', $result);
    }
}
