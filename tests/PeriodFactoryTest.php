<?php

declare(strict_types=1);

namespace Linkage\DoctrinePostgreSqlTsTzRange\Tests;

use Linkage\DoctrinePostgreSqlTsTzRange\PeriodFactory;
use PHPUnit\Framework\TestCase;

class PeriodFactoryTest extends TestCase
{
    public function testCreateFromString(): void
    {
        $SUT = new PeriodFactory();

        // both inclusive
        $actual1 = $SUT->createFromString('["2023-01-01 00:00:00+09","2023-01-31 23:59:59+09"]');
        $this->assertTrue($actual1->startsAtInclusive);
        $this->assertTrue($actual1->endsAtInclusive);
        $this->assertEquals('2023-01-01 00:00:00', $actual1->startsAt->format('Y-m-d H:i:s'));
        $this->assertEquals('+09:00', $actual1->startsAt->getTimezone()->getName());
        $this->assertEquals('2023-01-31 23:59:59', $actual1->endsAt->format('Y-m-d H:i:s'));
        $this->assertEquals('+09:00', $actual1->endsAt->getTimezone()->getName());

        // both exclusive
        $actual2 = $SUT->createFromString('("2023-01-01 00:00:00+09","2023-01-31 23:59:59+09")');
        $this->assertFalse($actual2->startsAtInclusive);
        $this->assertFalse($actual2->endsAtInclusive);

        // startsAt inclusive, endsAt exclusive
        $actual3 = $SUT->createFromString('["2023-01-01 00:00:00+09","2023-01-31 23:59:59+09")');
        $this->assertTrue($actual3->startsAtInclusive);
        $this->assertFalse($actual3->endsAtInclusive);

        // null startsAt
        $actual4 = $SUT->createFromString('[,"2023-01-31 23:59:59+09")');
        $this->assertTrue($actual4->startsAtInclusive);
        $this->assertFalse($actual4->endsAtInclusive);
        $this->assertNull($actual4->startsAt);
        $this->assertNotNull($actual4->endsAt);

        // null startsAt
        $actual5 = $SUT->createFromString('["2023-01-01 00:00:00+09",)');
        $this->assertTrue($actual5->startsAtInclusive);
        $this->assertFalse($actual5->endsAtInclusive);
        $this->assertNotNull($actual5->startsAt);
        $this->assertNull($actual5->endsAt);
    }
}
