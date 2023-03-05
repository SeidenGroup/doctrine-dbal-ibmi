# doctrine-dbal-ibmi

[![Lint](https://github.com/SeidenGroup/doctrine-dbal-ibmi/actions/workflows/lint.yml/badge.svg)](https://github.com/SeidenGroup/doctrine-dbal-ibmi/actions/workflows/lint.yml)
[![Quality assurance](https://github.com/SeidenGroup/doctrine-dbal-ibmi/actions/workflows/qa.yml/badge.svg)](https://github.com/SeidenGroup/doctrine-dbal-ibmi/actions/workflows/qa.yml)
[![Build and test](https://github.com/SeidenGroup/doctrine-dbal-ibmi/actions/workflows/test.yml/badge.svg)](https://github.com/SeidenGroup/doctrine-dbal-ibmi/actions/workflows/test.yml)

[Doctrine DBAL](https://www.doctrine-project.org/projects/doctrine-dbal/en/current/reference/introduction.html#introduction)
drivers for [DB2 on the IBM i platform](https://www.ibm.com/docs/en/i/7.4?topic=overview-db2-i).

Based on the original work by [@cassvail](https://github.com/cassvail) in [doctrine/dbal#910](https://github.com/doctrine/dbal/pull/910).

## Usage

First, install with Composer:

```shell
composer require alanseiden/doctrine-dbal-ibmi
```

## Configuration

This package provides 2 drivers: `OdbcDriver` and `DB2Driver`.

`OdbcDriver` requires the [`pdo_odbc`](https://www.php.net/manual/en/ref.pdo-odbc.php)
extension, and is the recommended driver.

`DB2Driver` requires the [`ibm_db2`](https://www.php.net/manual/en/book.ibm-db2.php) extension, and is [not recommended for
new connections](https://github.com/php/pecl-database-ibm_db2#new-implementations).

These drivers can be configured using the instructions described in the [Doctrine DBAL docs](https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#getting-a-connection).

```php
<?php

use Doctrine\DBAL\DriverManager;
use DoctrineDbalIbmi\Driver\OdbcDriver;

$connectionParams = [
    'driverClass' => OdbcDriver::class,
    'host' => 'localhost',
    'dbname' => 'mydb',
    'username' => 'user',
    'password' => 'secret',
];

$conn = DriverManager::getConnection($connectionParams);
```

## Examples

### Doctrine ORM

You can manually configure an `EntityManager` like so:

```php
<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use DoctrineDbalIbmi\Driver\OdbcDriver;

$configuration = Setup::createAnnotationMetadataConfiguration([
    __DIR__ . '/../path/to/your/entities/',
], true);

$connection = [
    'driverClass' => OdbcDriver::class,
    'host' => 'localhost',
    'dbname' => 'mydb',
    'username' => 'user',
    'password' => 'secret',
    'persistent' => false,
];

$entityManager = EntityManager::create($connection, $configuration);
```

You can then use this instance of `\Doctrine\ORM\EntityManager`.

### Zend Expressive

In your connection configuration, use these settings when configuring a Zend Expressive application:

```php
<?php

use DoctrineDbalIbmi\Driver\OdbcDriver;

return [
    'doctrine' => [
        'connection' => [
            'orm_default' => [
                'driverClass' => OdbcDriver::class,
                'params' => [
                    'host' => 'localhost',
                    'dbname' => 'mydb',
                    'username' => 'user',
                    'password' => 'secret',
                    'persistent' => false,
                ],
            ],
        ],
    ],
];
```
