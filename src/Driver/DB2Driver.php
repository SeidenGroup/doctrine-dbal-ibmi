<?php

namespace DoctrineDbalIbmi\Driver;

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

        $params['user'] = $username;
        $params['password'] = $password;
        $params['driver'] = '{IBM DB2 ODBC DRIVER}';
        $params['dbname'] = DataSourceName::fromConnectionParameters($params)->toString();

        unset($params['driver'], $params['user'], $params['password'], $params['host'], $params['port'], $params['protocol']);
        $username = null;
        $password = null;

        return new DB2IBMiConnection($params, $username, $password, $driverOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ibm_db2_i';
    }
}
