<?php

namespace DoctrineDbalIbmiTest;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Setup;

require __DIR__ . '/../vendor/autoload.php';

class Bootstrap
{
    /**
     * @var EntityManagerInterface
     */
    private static $entityManager;

    /**
     * @return EntityManagerInterface
     * @throws \PHPUnit_Framework_SkippedTestError
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\ORMException
     */
    public static function getEntityManager()
    {
        if (null === self::$entityManager) {
            if (!extension_loaded('ibm_db2') && !extension_loaded('pdo')) {
                throw new \PHPUnit_Framework_SkippedTestError('Neither DB2 nor PDO connections are unavailable, skipping test');
            }

            $configuration = Setup::createAnnotationMetadataConfiguration([
                __DIR__ . '/entity/',
            ], true);

            if (!file_exists(__DIR__ . '/config/local.php')) {
                throw new \PHPUnit_Framework_SkippedTestError('test/config/local.php not found');
            }

            $connection = require __DIR__ . '/config/local.php';

            self::$entityManager = EntityManager::create($connection, $configuration);
        }

        self::$entityManager->clear();
        return self::$entityManager;
    }
}
