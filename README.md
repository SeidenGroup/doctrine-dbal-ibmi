# doctrine-dbal-ibmi

[![Build Status](http://idevusr016.idevcloud.com:9090/buildStatus/icon?job=doctrine-dbal-ibmi)](http://idevusr016.idevcloud.com:9090/job/doctrine-dbal-ibmi)

Doctrine DBAL module for DB2 on the IBM i platform.

Based on the original work by [@cassvail](https://github.com/cassvail) in [doctrine/dbal#910](https://github.com/doctrine/dbal/pull/910).

# Usage

First, install with Composer:

```
$ composer require alanseiden/doctrine-dbal-ibmi
```

## Configuration

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
                        'i5_commit' => DB2_I5_TXN_NO_COMMIT,
                        'i5_lib' => '...',
                    ],
                ],
            ],
        ],
    ],
];
```

## Manual Configuration

You can manually configure an `EntityManager` like so:

```php
<?php

$configuration = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration([
    __DIR__ . '/../path/to/your/entities/',
], true);

$connection = [
    'driverClass' => \DoctrineDbalIbmi\Driver\DB2Driver::class,
    'host' => '...', // Replace this
    'user' => '...', // Replace this
    'password' => '...', // Replace this
    'dbname' => '...', // Look up value with WRKRDBDIRE
    'persistent' => true,
    'driverOptions' => [
        'i5_lib' => '...', // Replace this
        'i5_naming' => DB2_I5_NAMING_OFF,
        'i5_commit' => DB2_I5_TXN_NO_COMMIT,
    ],
];

$entityManager = \Doctrine\ORM\EntityManager::create($connection, $configuration);
```

You can then use this instance of `$entityManager`.
