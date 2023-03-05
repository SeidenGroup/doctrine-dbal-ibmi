<?php

declare(strict_types=1);

namespace DoctrineDbalIbmi\Tests\Driver;

use Doctrine\DBAL\DriverManager;
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
        self::assertSame('pdo_odbc_ibm_db2_i', (new OdbcDriver())->getName());
    }
}
