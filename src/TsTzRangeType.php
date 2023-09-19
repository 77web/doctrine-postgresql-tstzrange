<?php

declare(strict_types=1);

namespace Linkage\DoctrinePostgreSqlTsTzRange;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Types\Type;

class TsTzRangeType extends Type
{
    /**
     * @return string
     */
    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        if ($platform instanceof PostgreSQLPlatform) {
            throw new \LogicException('TsTzRangeType only supports postgresql.');
        }

        return 'tstzrange';
    }

    /**
     * convert database value to "Period" instance
     * @return Period
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (!is_string($value)) {
            throw new \RuntimeException('tstzrange expects only string. unexpected value from DB: ' . $value);
        }
        if (!preg_match('/(\[|\()(.*)\,(.*)(\]|\))/', $value, $matches)) {
            throw new \RuntimeException('unexpected value from DB: ' . $value);
        }
        $startParenthesis = $matches[1];
        $startsAtString = trim($matches[2], '"');
        $endsAtString = trim($matches[3], '"');
        $endParenthesis = $matches[4];

        $startsAt = $startsAtString === '' ? null : new \DateTimeImmutable($startsAtString);
        $endsAt = $endsAtString === '' ? null : new \DateTimeImmutable($endsAtString);
        $startInclusive = $startParenthesis === '[';
        $endInclusive = $endParenthesis === ']';

        return new Period($startsAt, $endsAt, $startInclusive, $endInclusive);
    }

    /**
     * convert "Period" object to database value
     * @return string
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        assert($value instanceof Period);

        $startsAt = $value->startsAt ? $value->startsAt->format(\DateTimeInterface::ATOM) : '';
        $endsAt = $value->endsAt ? $value->endsAt->format(\DateTimeInterface::ATOM) : '';
        $startParenthesis = $value->startsAtInclusive ? '[' : '(';
        $endParenthesis = $value->endsAtInclusive ? ']' : ')';

        return sprintf('%s%s,%s%s', $startParenthesis, $startsAt, $endsAt, $endParenthesis);
    }

    /**
     * @return bool
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'tstzrange';
    }
}
