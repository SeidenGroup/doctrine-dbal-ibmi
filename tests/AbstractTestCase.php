<?php

namespace DoctrineDbalIbmi\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use DoctrineDbalIbmi\Driver\DB2Driver;
use DoctrineDbalIbmi\Driver\OdbcDriver;
use PHPUnit\Framework\SkippedTestError;
use PHPUnit\Framework\TestCase;

abstract class AbstractTestCase extends TestCase
{
    private const EXTENSION_MAP = [
        OdbcDriver::class => 'pdo_odbc',
        DB2Driver::class => 'ibm_db2',
    ];

    /**
     * @var array<key-of<self::EXTENSION_MAP>, Connection>
     */
    private static $connections = [];

    /**
     * @param key-of<self::EXTENSION_MAP> $driver
     *
     * @throws Exception
     * @throws SkippedTestError
     */
    final protected static function getConnection(string $driver): Connection
    {
        if (!isset(self::$connections[$driver])) {
            $extension = self::EXTENSION_MAP[$driver];

            if (!extension_loaded($extension)) {
                throw new SkippedTestError(sprintf('The extension "%s" is not loaded, skipping test.', $extension));
            }

            $connectionParams = self::getConnectionParams($driver);

            $testConn = DriverManager::getConnection($connectionParams);

            self::$connections[$driver] = $testConn;
        }

        return self::$connections[$driver];
    }

    /**
     * @param key-of<self::EXTENSION_MAP> $driver
     */
    private static function getConnectionParams(string $driver): array
    {
        $connection = [
            'driverClass' => $driver,
            'persistent' => false,
            'driver' => getenv('db_driver'),
            'dbname' => getenv('db_name'),
            'host' => getenv('db_host'),
            'user' => getenv('db_user'),
            'password' => getenv('db_password'),
        ];

        if (OdbcDriver::class === $driver) {
            $connection['dsn'] = getenv('db_dsn');
        }

        return $connection;
    }
}
