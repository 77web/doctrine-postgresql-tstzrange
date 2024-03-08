<?php

declare(strict_types=1);

namespace Linkage\DoctrinePostgreSqlTsTzRange\Tests;

use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Linkage\DoctrinePostgreSqlTsTzRange\Period;
use Linkage\DoctrinePostgreSqlTsTzRange\TsTzRangeType;
use PHPUnit\Framework\TestCase;

class TsTzRangeTypeTest extends TestCase
{
    public function testGetSQLDeclaration(): void
    {
        $platformMock = $this->createMock(PostgreSQLPlatform::class);
        $SUT = new TsTzRangeType();
        $column = [];
        $actual = $SUT->getSQLDeclaration($column, $platformMock);
        $this->assertEquals('tstzrange', $actual);
    }

    public function testGetSQLDeclarationWithNotPostgres(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("TsTzRangeType only supports postgresql.");

        $platformMock = $this->createMock(MySQLPlatform::class);
        $SUT = new TsTzRangeType();
        $column = [];
        $SUT->getSQLDeclaration($column, $platformMock);
    }

    public function testConvertToPHPValue(): void
    {
        $platformMock = $this->createMock(PostgreSQLPlatform::class);
        $SUT = new TsTzRangeType();

        $value = '["2023-01-01 00:00:00+09","2023-01-31 23:59:59+09"]';
        $actual = $SUT->convertToPHPValue($value, $platformMock);
        $this->assertEquals(
            new Period(
                startsAt: new \DateTimeImmutable('2023-01-01 00:00:00+09'),
                endsAt: new \DateTimeImmutable('2023-01-31 23:59:59+09'),
                startsAtInclusive: true,
                endsAtInclusive: true
            ),
            $actual
        );
    }

    public function testConvertToPHPValueWithNotString(): void
    {
        $this->expectException(\RuntimeException::class);

        $platformMock = $this->createMock(PostgreSQLPlatform::class);
        $SUT = new TsTzRangeType();

        $notString = null;
        $SUT->convertToPHPValue($notString, $platformMock);
    }

    public function testConvertToPHPValueWithInvalidString(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/^unexpected value from DB/');

        $platformMock = $this->createMock(PostgreSQLPlatform::class);
        $SUT = new TsTzRangeType();

        $invalidString = 'abc';
        $SUT->convertToPHPValue($invalidString, $platformMock);
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
