<?php

/*
 * This file is part of the doctrine-dbal-ibmi package.
 * Copyright (c) 2016 Alan Seiden Consulting LLC, James Titcumb
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DoctrineDbalIbmi\Driver;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\VersionAwarePlatformDriver;
use DoctrineDbalIbmi\Platform\DB2IBMiPlatform;
use DoctrineDbalIbmi\Schema\DB2IBMiSchemaManager;

abstract class AbstractDB2Driver implements Driver, VersionAwarePlatformDriver
{
    public const SYSTEM_IBMI = 'AIX';
    public const SYSTEM_IBMI_OS400 = 'OS400';

    /**
     * @return true
     */
    public static function isIbmi()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabase(Connection $conn)
    {
        $params = $conn->getParams();

        if (DB2Driver::class === $params['driverClass'] && isset($params['driverOptions']) && is_array($params['driverOptions'])
            && isset($params['driverOptions']['i5_lib'])) {
            // In iSeries systems, with SQL naming, the default database name is specified in ['driverOptions' => ['i5_lib' = ?]]
            $database = $params['driverOptions']['i5_lib'];
        } else {
            $dsnParams = DataSourceName::fromConnectionParameters($params)
                ->getConnectionParameters();
            $database = $dsnParams['DATABASE'];
        }

        assert(is_string($database));

        return $database;
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabasePlatform()
    {
        return new DB2IBMiPlatform();
    }

    /**
     * {@inheritdoc}
     */
    public function getSchemaManager(Connection $conn)
    {
        return new DB2IBMiSchemaManager($conn);
    }

    /**
     * {@inheritdoc}
     */
    public function createDatabasePlatformForVersion($version)
    {
        if (version_compare($this->getVersionNumber($version), '7.3', '>=')) {
            return $this->getDatabasePlatform();
        }

        throw new Exception(sprintf('The version %s is not supported, you must use version 7.3 or higher.', $version));
    }

    /**
     * Detects IBM DB2 for i server version
     *
     * @param string $versionString Version string as returned by IBM DB2 server, i.e. '07.04.0015'
     *
     * @throws Exception
     */
    private function getVersionNumber(string $versionString): string
    {
        if (
            0 === preg_match(
                '/^(?:[^\s]+\s)?(?P<major>\d+)\.(?P<minor>\d+)\.(?P<patch>\d+)/i',
                $versionString,
                $versionParts
            )
        ) {
            throw Exception::invalidPlatformVersionSpecified($versionString, '^(?:[^\s]+\s)?<major_version>.<minor_version>.<patch_version>');
        }

        return $versionParts['major'].'.'.$versionParts['minor'].'.'.$versionParts['patch'];
    }
}
