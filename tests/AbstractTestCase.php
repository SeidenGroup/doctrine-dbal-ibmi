<?php

namespace DoctrineDbalIbmi\Tests;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\ORMSetup;
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
     * @var array<key-of<self::EXTENSION_MAP>, EntityManagerInterface>
     */
    private static $entityManagers = [];

    /**
     * @param key-of<self::EXTENSION_MAP> $driver
     *
     * @throws ORMException
     * @throws SkippedTestError
     */
    final protected static function getEntityManager(string $driver): EntityManagerInterface
    {
        if (!isset(self::$entityManagers[$driver])) {
            $extension = self::EXTENSION_MAP[$driver];

            if (!extension_loaded($extension)) {
                throw new SkippedTestError(sprintf('The extension "%s" is not loaded, skipping test.', $extension));
            }
            
            $connection = self::getConnection($driver);

            $configuration = ORMSetup::createAnnotationMetadataConfiguration([
                __DIR__.'/fixtures/App/Entity/',
            ], true);

            self::$entityManagers[$driver] = EntityManager::create($connection, $configuration);
        }

        self::$entityManagers[$driver]->clear();

        return self::$entityManagers[$driver];
    }

    /**
     * @param key-of<self::EXTENSION_MAP> $driver
     */
    private static function getConnection(string $driver): array
    {
        $connection = [
            'driverClass' => $driver,
            'persistent' => false,
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
