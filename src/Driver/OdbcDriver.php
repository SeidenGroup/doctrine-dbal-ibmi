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

        // @todo: Remove the following conditional block in the next major version.
        if (isset($params['username'])) {
            @trigger_error(sprintf(
                'Passing parameter "username" to "%s()" is deprecated since alanseiden/doctrine-dbal-ibmi 0.1 and its support'
                . ' will be removed in version 0.2. Use "user" parameter instead.',
                __METHOD__
            ), E_USER_DEPRECATED);

            $params['user'] = $params['username'];
            unset($params['username']);
        }

        $username = (!is_null($username)) ? $username : $params['user'];
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
    public function getSchemaManager(\Doctrine\DBAL\Connection $conn)
    {
        return new DB2IBMiSchemaManager($conn);
    }
}
