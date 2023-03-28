<?php

namespace DoctrineDbalIbmi\Driver;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use DoctrineDbalIbmi\Platform\DB2IBMiPlatform;
use DoctrineDbalIbmi\Schema\DB2IBMiSchemaManager;

abstract class AbstractDB2Driver implements Driver
{
    const SYSTEM_IBMI = 'AIX';
    const SYSTEM_IBMI_OS400 = 'OS400';

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
        $params = DataSourceName::fromConnectionParameters($conn->getParams())
            ->getConnectionParameters();

        assert(is_string($params['dbname']));

        return $params['dbname'];
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
}
