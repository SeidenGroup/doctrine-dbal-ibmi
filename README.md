# doctrine-dbal-ibmi

Doctrine DBAL module for DB2 on the IBM i platform.

The majority of the work done to make DBAL work on i was done by @cassvail and
can be seen in the PR doctrine/dbal#910 - credit where it's due!

# Usage

First, install with Composer:

```
$ composer require alanseiden/doctrine-dbal-ibmi
```

In your connection configuration, use this specific `DB2Driver` class, for
example, when configuring for a Zend Expressive application:

```php
<?php

return [
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'driverClass' => \DoctrineDbalIbmi\Driver\DB2Driver::class,
                'params' => [
                    'host'     => '...',
                    'user'     => '...',
                    'password' => '...',
                    'dbname'   => '...',
                    'persistent' => true,
                    'driverOptions' => [
                        'i5_naming' => DB2_I5_NAMING_OFF,
                        'i5_lib' => '...',
                    ],
                ],
            ],
        ],
    ],
];
```
