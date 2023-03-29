<?php

declare(strict_types=1);

namespace DoctrineDbalIbmi\Tests\Driver;

use Doctrine\DBAL\DriverManager;
use DoctrineDbalIbmi\Driver\DB2Driver;

final class DB2DriverTest extends AbstractDriverTestCase
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
                'driverClass' => DB2Driver::class,
                'persistent' => false,
                'dbname' => 'DRIVER={IBM DB2 ODBC DRIVER};HOSTNAME=127.1.1.2;DATABASE=MY_DB;PORT=55000;PROTOCOL=SOCKETS;UID=me;PWD=53cr37',
                'user' => 'un',
                'password' => 'known',
                'protocol' => 'TCPIP',
                'port' => 60000,
            ],
        ];

        yield [
            'MY_OTHER_DB',
            [
                'driverClass' => DB2Driver::class,
                'persistent' => false,
                'driver' => '{OTHER DRIVER}',
                'dbname' => 'MY_OTHER_DB',
                'user' => 'un',
                'password' => 'known',
                'protocol' => 'TCPIP',
                'port' => 60000,
                'host' => 'local_host',
            ],
        ];

        yield [
            'NEW_DB',
            [
                'driverClass' => DB2Driver::class,
                'persistent' => false,
                'driver' => '{OTHER DRIVER}',
                'dbname' => 'DRIVER={IBM DB2 ODBC DRIVER};HOSTNAME=127.1.1.2;DATABASE=NEW_DB;UID=me;PWD=53cr37',
                'dsn' => 'DRIVER={IBM i Access ODBC Driver};SYSTEM=127.0.0.1;DATABASE=NEW_DB',
                'user' => 'un',
                'password' => 'known',
                'protocol' => 'TCPIP',
                'port' => 60000,
                'host' => 'local_host',
            ],
        ];
    }
}
