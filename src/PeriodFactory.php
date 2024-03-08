<?php

declare(strict_types=1);

namespace Linkage\DoctrinePostgreSqlTsTzRange;

class PeriodFactory
{
    public function createFromString(string $value): Period
    {
        if (!preg_match('/(\[|\()(.*)\,(.*)(\]|\))/', $value, $matches)) {
            throw new \RuntimeException('unexpected value: ' . $value);
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
}
