<?php

declare(strict_types=1);

namespace Linkage\DoctrinePostgreSqlTsTzRange\Tests\Functional;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Linkage\DoctrinePostgreSqlTsTzRange\Period;
use Linkage\DoctrinePostgreSqlTsTzRange\TsTzRangeType;
use PHPUnit\Framework\TestCase;

class TsTzRangeUsageTest extends TestCase
{
    private Connection $conn;

    public static function setUpBeforeClass(): void
    {
        Type::addType('tstzrange', TsTzRangeType::class);
    }

    protected function setUp(): void
    {
        $connectionParams = [
            'dbname' => 'test',
            'user' => 'test',
            'password' => 'password',
            'host' => 'localhost',
            'driver' => 'pdo_pgsql',
        ];
        $this->conn = DriverManager::getConnection($connectionParams);
        $this->conn->executeQuery(file_get_contents(__DIR__ . '/drop_table.sql'));
        $this->conn->executeQuery(file_get_contents(__DIR__ . '/create_table.sql'));

        $this->conn->getDatabasePlatform()->registerDoctrineTypeMapping('tstzrange', 'tstzrange');
    }

    public function test_writing(): void
    {
        $qb = $this->conn->createQueryBuilder();
        $qb->insert('reservation')
            ->setValue('id', '?')
            ->setValue('name', '?')
            ->setValue('period', '?')
            ->setParameter(0, 1)
            ->setParameter(1, 'test1')
            ->setParameter(2, new Period(
                new \DateTimeImmutable('2023-10-01 00:00:00', new \DateTimeZone('Asia/Tokyo')),
                new \DateTimeImmutable('2023-10-01 01:00:00', new \DateTimeZone('Asia/Tokyo')),
            ), 'tstzrange')
            ->executeQuery();

        $actual = $this->conn->fetchAssociative('select * from reservation');
        $this->assertEquals('["2023-10-01 00:00:00+09","2023-10-01 01:00:00+09")', $actual['period'], 'Period object has been converted into pgsql\'s tstzrange expression and saved successfully.');
    }

    public function test_period_upper_null_value(): void
    {
        $qb = $this->conn->createQueryBuilder();
        $qb->insert('reservation')
            ->setValue('id', '?')
            ->setValue('name', '?')
            ->setValue('period', '?')
            ->setParameter(0, 2)
            ->setParameter(1, 'test2')
            ->setParameter(2, new Period(
                new \DateTimeImmutable('2023-10-01 00:00:00', new \DateTimeZone('Asia/Tokyo')),
                null,
            ), 'tstzrange')
            ->executeQuery();

        $actual = $this->conn->fetchAssociative('select * from reservation where id = 2');
        $this->assertEquals('["2023-10-01 00:00:00+09",)', $actual['period'], 'Period object has been converted into pgsql\'s tstzrange expression in cases where the upper limit is null');
    }
}
