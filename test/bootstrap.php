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

            if (!file_exists(__DIR__ . '/config/local.php')) {
                throw new \PHPUnit_Framework_SkippedTestError('test/config/local.php not found');
            }

            $connection = require __DIR__ . '/config/local.php';

            self::$entityManager = EntityManager::create($connection, $configuration);
        }

        self::$entityManager->clear();
        return self::$entityManager;
    }

    /**
     * @return \Doctrine\DBAL\Connection
     * @throws \Doctrine\ORM\ORMException
     */
    public static function getDbalConnection()
    {
        return self::getEntityManager()->getConnection();
    }

    /**
     * Loads a SQL file containing fixtures for tests and executes it using the DBAL
     *
     * @param string $fixtureFile
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\ORMException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \PHPUnit_Framework_SkippedTestError
     */
    public static function executeSqlFixture($fixtureFile)
    {
        if (!file_exists($fixtureFile) || !is_readable($fixtureFile)) {
            throw new \RuntimeException('Fixture file does not exist or not readable: ' . $fixtureFile);
        }
        $fixtureFile = realpath($fixtureFile);

        $dbal = self::getDbalConnection();

        $sql = file_get_contents($fixtureFile);
        $statements = explode(';', $sql);

        foreach ($statements as $statement) {
            $statement = trim($statement);

            if ($statement === '') {
                continue;
            }

            $stmt = $dbal->prepare($statement);
            if (!$stmt->execute()) {
                throw new \RuntimeException(sprintf(
                    'Failed to execute statement in fixture "%s": %s',
                    $fixtureFile,
                    $statement
                ));
            }
        }
    }
}
