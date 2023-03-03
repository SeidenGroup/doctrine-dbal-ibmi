<?php

declare(strict_types=1);

namespace DoctrineDbalIbmi\Tests\Driver;

use DoctrineDbalIbmi\Driver\DataSourceName;
use DoctrineDbalIbmi\Driver\DB2Driver;
use DoctrineDbalIbmi\Driver\OdbcDriver;
use PHPUnit\Framework\TestCase;

final class DataSourceNameTest extends TestCase
{
    public function fromConnectionParametersProvider(): iterable
    {
        yield [
            'DRIVER={IBM i Access ODBC Driver};SYSTEM=local_host;DATABASE=MY_DB;UID=me;PWD=53cr37',
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
            'DRIVER={IBM i Access ODBC Driver};SYSTEM=127.0.0.1;DATABASE=MY_DB',
            [
                'driverClass' => OdbcDriver::class,
                'persistent' => false,
                'dsn' => 'DRIVER={IBM i Access ODBC Driver};SYSTEM=127.0.0.1;DATABASE=MY_DB',
                'dbname' => 'OTHER_DB',
                'user' => 'me',
                'password' => '53cr37',
            ],
        ];

        yield [
            'DRIVER={IBM DB2 ODBC DRIVER};HOSTNAME=127.1.1.2;DATABASE=MY_DB;PORT=55000;PROTOCOL=SOCKETS;UID=me;PWD=53cr37',
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
            'DRIVER={IBM DB2 ODBC DRIVER};HOSTNAME=127.1.1.2;DATABASE=MY_DB;PORT=55000;PROTOCOL=SOCKETS;UID=me;PWD=53cr37',
            [
                'driverClass' => DB2Driver::class,
                'persistent' => false,
                'driver' => '{OTHER DRIVER}',
                'dbname' => 'DRIVER={IBM DB2 ODBC DRIVER};HOSTNAME=127.1.1.2;DATABASE=MY_DB;PORT=55000;PROTOCOL=SOCKETS;UID=me;PWD=53cr37',
                'user' => 'un',
                'password' => 'known',
                'protocol' => 'TCPIP',
                'port' => 60000,
            ],
        ];

        yield [
            'DRIVER={OTHER DRIVER};HOSTNAME=local_host;PORT=60000;PROTOCOL=TCPIP;DATABASE=MY_DB;UID=un;PWD=known',
            [
                'driverClass' => DB2Driver::class,
                'persistent' => false,
                'driver' => '{OTHER DRIVER}',
                'dbname' => 'MY_DB',
                'dsn' => 'DRIVER={IBM i Access ODBC Driver};SYSTEM=127.0.0.1;DATABASE=MY_DB',
                'user' => 'un',
                'password' => 'known',
                'protocol' => 'TCPIP',
                'port' => 60000,
                'host' => 'local_host',
            ],
        ];
    }

    /**
     * @return void
     *
     * @dataProvider fromConnectionParametersProvider
     */
    public function testFromConnectionParameters(string $expected, array $params)
    {
        self::assertSame($expected, DataSourceName::fromConnectionParameters($params)->toString());
    }
}
