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
            if (!extension_loaded('ibm_db2')) {
                throw new \PHPUnit_Framework_SkippedTestError('DB2 connection is unavailable, skipping test');
            }

            $configuration = Setup::createAnnotationMetadataConfiguration([
                __DIR__ . '/entity/',
            ], true);

            $connection = require __DIR__ . '/config/local.php';

            self::$entityManager = EntityManager::create($connection, $configuration);
        }

        self::$entityManager->clear();
        return self::$entityManager;
    }
}
