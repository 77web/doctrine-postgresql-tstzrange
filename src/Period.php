<?php

declare(strict_types=1);

namespace Linkage\DoctrinePostgreSqlTsTzRange;

class Period
{
    public function __construct(
        public \DateTimeImmutable|null $startsAt,
        public \DateTimeImmutable|null $endsAt,
        public bool $startsAtInclusive = true,
        public bool $endsAtInclusive = false,
    ) {
    }

    public function contains(\DateTimeInterface $target): bool
    {
        return (!$this->startsAt || ($this->startsAtInclusive && $this->startsAt <= $target) || (!$this->startsAtInclusive && $this->startsAt < $target))
            && (!$this->endsAt || ($this->endsAtInclusive && $target <= $this->endsAt) || (!$this->endsAtInclusive && $target < $this->endsAt));
    }
}
