<?php

namespace DoctrineDbalIbmi\Driver;

use Doctrine\DBAL\Driver\IBMDB2\DB2Connection;

class DB2Driver extends AbstractDB2Driver
{
    /**
     * {@inheritdoc}
     */
    public function connect(array $params, $username = null, $password = null, array $driverOptions = array())
    {
        if ('' === ($params['protocol'] ?? '')) {
            $params['protocol'] = 'TCPIP';
        }

        if ('' === ($username ?? '')) {
            $username = $params['user'] ?? null;
        }

        if ('' === ($password ?? '')) {
            $password = $params['password'] ?? null;
        }

        // Check if the "dbname" parameter has an uncataloged database DSN.
        if (isset($params['host']) && false === strpos($params['dbname'], '=')) {
            $params['dbname'] = 'DRIVER={IBM DB2 ODBC DRIVER}' .
                ';DATABASE=' . $params['dbname'] .
                ';HOSTNAME=' . $params['host'] .
                ';PROTOCOL=' . $params['protocol'] .
                ';UID=' . $username .
                ';PWD=' . $password . ';';

            if (isset($params['port'])) {
                $params['dbname'] .= 'PORT=' . $params['port'];
            }

            unset($params['user'], $params['password'], $params['host'], $params['port'], $params['protocol']);
            $username = null;
            $password = null;
        }

        return new DB2IBMiConnection($params, $username, $password, $driverOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ibm_db2';
    }
}
