<?php

namespace DoctrineDbalIbmi\Driver;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Platforms\DB2Platform;
use DoctrineDbalIbmi\Platform\DB2IBMiPlatform;
use DoctrineDbalIbmi\Schema\DB2IBMiSchemaManager;
use DoctrineDbalIbmi\Schema\DB2LUWSchemaManager;

abstract class AbstractDB2Driver implements Driver
{
    const SYSTEM_IBMI = 'AIX';
    const SYSTEM_IBMI_OS400 = 'OS400';

    public static function isIbmi()
    {
        return (PHP_OS === static::SYSTEM_IBMI || PHP_OS === static::SYSTEM_IBMI_OS400);
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabase(\Doctrine\DBAL\Connection $conn)
    {
        $params = $conn->getParams();

        return $params['dbname'];
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabasePlatform()
    {
        if (static::isIbmi()) {
            return new DB2IBMiPlatform();
        } else {
            return new DB2Platform();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSchemaManager(\Doctrine\DBAL\Connection $conn)
    {
        if (static::isIbmi()) {
            return new DB2IBMiSchemaManager($conn);
        } else {
            return new DB2LUWSchemaManager($conn);
        }
    }
}
