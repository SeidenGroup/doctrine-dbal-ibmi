<?php

declare(strict_types=1);

/*
 * This file is part of the doctrine-dbal-ibmi package.
 * Copyright (c) 2016 Alan Seiden Consulting LLC, James Titcumb
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DoctrineDbalIbmi\Tests\Driver;

use DoctrineDbalIbmi\Driver\OdbcDriver;

final class OdbcDriverTest extends AbstractDriverTestCase
{
    /**
     * @return iterable<int|string, array<int, string|array<string, string|int|bool>>>
     *
     * @phpstan-return iterable<int|string, array{0: string, 1: array<string, string|int|bool>}>
     */
    public function getDatabaseProvider(): iterable
    {
        yield [
            'MY_DB',
            [
                'driverClass' => OdbcDriver::class,
                'persistent' => false,
                'driver' => '{IBM i Access ODBC Driver}',
                'dbname' => 'MY_DB',
                'host' => 'local_host',
                'user' => 'me',
                'password' => '53cr37',
            ],
        ];

        yield [
            'MY_OTHER_DB',
            [
                'driverClass' => OdbcDriver::class,
                'persistent' => false,
                'driver' => '{OTHER DRIVER}',
                'dsn' => 'DRIVER={IBM i Access ODBC Driver};SYSTEM=127.0.0.1;DATABASE=MY_OTHER_DB',
                'user' => 'un',
                'password' => 'known',
                'protocol' => 'TCPIP',
                'port' => 60000,
            ],
        ];
    }

    public function testGetName(): void
    {
        static::assertSame('pdo_odbc_ibm_db2_i', (new OdbcDriver())->getName());
    }
}
