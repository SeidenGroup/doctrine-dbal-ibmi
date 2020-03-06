<?php

namespace DoctrineDbalIbmi\Driver;

use Doctrine\DBAL\VersionAwarePlatformDriver;
use DoctrineDbalIbmi\Platform\DB2IBMiPlatform;
use DoctrineDbalIbmi\Schema\DB2IBMiSchemaManager;

class OdbcDriver extends AbstractDB2Driver implements VersionAwarePlatformDriver
{
    /**
     * {@inheritdoc}
     */
    public function connect(array $params, $username = null, $password = null, array $driverOptions = array())
    {
        $params['dsn'] = 'odbc:' . $params['dsn'];
        $username = (!is_null($username)) ? $username : $params['username'];
        $password = (!is_null($password)) ? $password : $params['password'];

        return new OdbcIBMiConnection($params, $username, $password, $driverOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'odbc';
    }

    public function createDatabasePlatformForVersion($version)
    {
        return new DB2IBMiPlatform();
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
        return new DB2IBMiPlatform();
    }

    /**
     * {@inheritdoc}
     */
    public function getSchemaManager(\Doctrine\DBAL\Connection $conn)
    {
        return new DB2IBMiSchemaManager($conn);
    }
}
