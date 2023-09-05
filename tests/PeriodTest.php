<?php

declare(strict_types=1);

namespace Linkage\DoctrinePostgreSqlTsTzRange\Tests;

use Linkage\DoctrinePostgreSqlTsTzRange\Period;
use PHPUnit\Framework\TestCase;

class PeriodTest extends TestCase
{
    public function testContainsInclusive(): void
    {
        $SUT = new Period(
            new \DateTimeImmutable('2022-01-01 00:00:00'),
            new \DateTimeImmutable('2022-01-31 23:59:59'),
            true,
            true,
        );
        $this->assertFalse($SUT->contains(new \DateTimeImmutable('2021-12-31 10:00:00')));
        $this->assertTrue($SUT->contains(new \DateTimeImmutable('2022-01-01 10:00:00')));
        $this->assertTrue($SUT->contains(new \DateTimeImmutable('2022-01-31 23:00:00')));
        $this->assertFalse($SUT->contains(new \DateTimeImmutable('2022-02-01 00:00:00')));
    }

    public function testContainsNullStartsAt(): void
    {
        $SUT = new Period(
            null,
            new \DateTimeImmutable('2022-01-31 23:59:59'),
            true,
            true,
        );
        $this->assertTrue($SUT->contains(new \DateTimeImmutable('2021-12-31 10:00:00')));
        $this->assertTrue($SUT->contains(new \DateTimeImmutable('2022-01-01 10:00:00')));
        $this->assertTrue($SUT->contains(new \DateTimeImmutable('2022-01-31 23:00:00')));
        $this->assertFalse($SUT->contains(new \DateTimeImmutable('2022-02-01 00:00:00')));
    }

    public function testContainsNullEndsAt(): void
    {
        $SUT = new Period(
            new \DateTimeImmutable('2022-01-01 00:00:00'),
            null,
            true,
            true,
        );
        $this->assertFalse($SUT->contains(new \DateTimeImmutable('2021-12-31 10:00:00')));
        $this->assertTrue($SUT->contains(new \DateTimeImmutable('2022-01-01 10:00:00')));
        $this->assertTrue($SUT->contains(new \DateTimeImmutable('2022-01-31 23:00:00')));
        $this->assertTrue($SUT->contains(new \DateTimeImmutable('2022-02-01 00:00:00')));
    }

    public function testContainsExclusive(): void
    {
        $SUT = new Period(
            new \DateTimeImmutable('2022-01-01 00:00:00'),
            new \DateTimeImmutable('2022-01-31 23:59:59'),
            false,
            false,
        );
        $this->assertFalse($SUT->contains(new \DateTimeImmutable('2021-12-31 10:00:00')));
        $this->assertFalse($SUT->contains(new \DateTimeImmutable('2022-01-01 00:00:00')));
        $this->assertTrue($SUT->contains(new \DateTimeImmutable('2022-01-01 10:00:00')));
        $this->assertTrue($SUT->contains(new \DateTimeImmutable('2022-01-31 23:00:00')));
        $this->assertFalse($SUT->contains(new \DateTimeImmutable('2022-01-31 23:59:59')));
        $this->assertFalse($SUT->contains(new \DateTimeImmutable('2022-02-01 00:00:00')));
    }
}
