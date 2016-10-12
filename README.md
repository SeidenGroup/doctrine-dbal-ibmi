# doctrine-dbal-ibmi

[![Build Status](http://idevusr016.idevcloud.com:9090/buildStatus/icon?job=doctrine-dbal-ibmi)](http://idevusr016.idevcloud.com:9090/job/doctrine-dbal-ibmi)

Doctrine DBAL module for DB2 on the IBM i platform.

Based on the original work by [@cassvail](https://github.com/cassvail) in [doctrine/dbal#910](https://github.com/doctrine/dbal/pull/910).

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
