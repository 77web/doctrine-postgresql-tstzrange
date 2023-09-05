<?php

declare(strict_types=1);

namespace Linkage\DoctrinePostgreSqlTsTzRange\Tests;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Linkage\DoctrinePostgreSqlTsTzRange\Period;
use Linkage\DoctrinePostgreSqlTsTzRange\TsTzRangeType;
use PHPUnit\Framework\TestCase;

class TsTzRangeTypeTest extends TestCase
{
    public function testConvertToPHPValue(): void
    {
        $platformMock = $this->createMock(PostgreSQLPlatform::class);
        $SUT = new TsTzRangeType();

        // both inclusive
        $actual1 = $SUT->convertToPHPValue('["2023-01-01 00:00:00+09","2023-01-31 23:59:59+09"]', $platformMock);
        $this->assertTrue($actual1 instanceof Period);
        $this->assertTrue($actual1->startsAtInclusive);
        $this->assertTrue($actual1->endsAtInclusive);
        $this->assertEquals('2023-01-01 00:00:00', $actual1->startsAt->format('Y-m-d H:i:s'));
        $this->assertEquals('+09:00', $actual1->startsAt->getTimezone()->getName());
        $this->assertEquals('2023-01-31 23:59:59', $actual1->endsAt->format('Y-m-d H:i:s'));
        $this->assertEquals('+09:00', $actual1->endsAt->getTimezone()->getName());

        // both exclusive
        $actual2 = $SUT->convertToPHPValue('("2023-01-01 00:00:00+09","2023-01-31 23:59:59+09")', $platformMock);
        $this->assertTrue($actual2 instanceof Period);
        $this->assertFalse($actual2->startsAtInclusive);
        $this->assertFalse($actual2->endsAtInclusive);

        // startsAt inclusive, endsAt exclusive
        $actual3 = $SUT->convertToPHPValue('["2023-01-01 00:00:00+09","2023-01-31 23:59:59+09")', $platformMock);
        $this->assertTrue($actual3 instanceof Period);
        $this->assertTrue($actual3->startsAtInclusive);
        $this->assertFalse($actual3->endsAtInclusive);

        // null startsAt
        $actual4 = $SUT->convertToPHPValue('[,"2023-01-31 23:59:59+09")', $platformMock);
        $this->assertTrue($actual4 instanceof Period);
        $this->assertTrue($actual4->startsAtInclusive);
        $this->assertFalse($actual4->endsAtInclusive);
        $this->assertNull($actual4->startsAt);
        $this->assertNotNull($actual4->endsAt);

        // null startsAt
        $actual5 = $SUT->convertToPHPValue('["2023-01-01 00:00:00+09",)', $platformMock);
        $this->assertTrue($actual5 instanceof Period);
        $this->assertTrue($actual5->startsAtInclusive);
        $this->assertFalse($actual5->endsAtInclusive);
        $this->assertNotNull($actual5->startsAt);
        $this->assertNull($actual5->endsAt);
    }

    public function testConvertToDatabaseValue(): void
    {
        $platformMock = $this->createMock(PostgreSQLPlatform::class);
        $SUT = new TsTzRangeType();

        // both inclusive
        $actual1 = $SUT->convertToDatabaseValue(new Period(
            new \DateTimeImmutable('2023-01-01 00:00:00', new \DateTimeZone('Asia/Tokyo')),
            new \DateTimeImmutable('2023-01-31 23:59:59', new \DateTimeZone('Asia/Tokyo')),
            true,
            true,
        ), $platformMock);
        $this->assertEquals('[2023-01-01T00:00:00+09:00,2023-01-31T23:59:59+09:00]', $actual1);

        // both exclusive
        $actual2 = $SUT->convertToDatabaseValue(new Period(
            new \DateTimeImmutable('2023-01-01 00:00:00', new \DateTimeZone('Asia/Tokyo')),
            new \DateTimeImmutable('2023-01-31 23:59:59', new \DateTimeZone('Asia/Tokyo')),
            false,
            false,
        ), $platformMock);
        $this->assertEquals('(2023-01-01T00:00:00+09:00,2023-01-31T23:59:59+09:00)', $actual2);

        // startsAt inclusive, endsAt exclusive
        $actual2 = $SUT->convertToDatabaseValue(new Period(
            new \DateTimeImmutable('2023-01-01 00:00:00', new \DateTimeZone('Asia/Tokyo')),
            new \DateTimeImmutable('2023-01-31 23:59:59', new \DateTimeZone('Asia/Tokyo')),
            true,
            false,
        ), $platformMock);
        $this->assertEquals('[2023-01-01T00:00:00+09:00,2023-01-31T23:59:59+09:00)', $actual2);
    }
}