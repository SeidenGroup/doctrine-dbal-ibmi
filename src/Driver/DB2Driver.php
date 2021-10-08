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
        if ( ! isset($params['protocol'])) {
            $params['protocol'] = 'TCPIP';
        }

        if ($params['host'] !== 'localhost' && $params['host'] != '127.0.0.1') {
            // if the host isn't localhost, use extended connection params
            $params['dbname'] = 'DRIVER={IBM DB2 ODBC DRIVER}' .
                ';DATABASE=' . $params['dbname'] .
                ';HOSTNAME=' . $params['host'] .
                ';PROTOCOL=' . $params['protocol'] .
                ';UID='      . $username .
                ';PWD='      . $password .';';
            if (isset($params['port'])) {
                $params['dbname'] .= 'PORT=' . $params['port'];
            }

            $username = null;
            $password = null;
        }

        if (static::isIbmi()) {
            return new DB2IBMiConnection($params, $username, $password, $driverOptions);
        } else {
            return new DB2Connection($params, $username, $password, $driverOptions);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ibm_db2';
    }
}
