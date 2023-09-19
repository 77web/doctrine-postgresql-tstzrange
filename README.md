# linkage/doctrine-postgresql-tstzrange

## Installation

```shell
composer require linkage/doctrine-postgresql-tstzrange
```

## Usage

Add TsTzRangeType as dbal column type
```yaml
doctrine:
    dbal:
        types:
            tstzrange:
                class: Linkage\DoctrinePostgreSqlTsTzRange\TsTzRangeType
```

```php
<?php

#[ORM\Entity]
class Something
{
    #[ORM\Column(type: 'tstzrange')]
    private Period $period;
}
```

