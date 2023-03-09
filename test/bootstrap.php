<?php

namespace DoctrineDbalIbmiTest;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use PHPUnit\Framework\SkippedTestError;

require __DIR__ . '/../vendor/autoload.php';

class Bootstrap
{
    /**
     * @var Connection
     */
    private static $connection;

    /**
     * @throws Exception
     * @throws SkippedTestError
     */
    public static function getConnection(): Connection
    {
        if (null === self::$connection) {
            if (!extension_loaded('ibm_db2') && !extension_loaded('pdo_odbc')) {
                throw new SkippedTestError('Neither ibm_db2 nor pdo_odbc extensions are available, skipping test');
            }

            if (!file_exists(__DIR__ . '/config/local.php')) {
                throw new SkippedTestError('test/config/local.php not found');
            }

            $connectionParams = require __DIR__.'/config/local.php';

            self::$connection = DriverManager::getConnection($connectionParams);
        }

        return self::$connection;
    }
}
