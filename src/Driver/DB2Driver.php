<?php

/*
 * This file is part of the doctrine-dbal-ibmi package.
 * Copyright (c) 2016 Alan Seiden Consulting LLC, James Titcumb
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DoctrineDbalIbmi\Driver;

class DB2Driver extends AbstractDB2Driver
{
    /**
     * {@inheritdoc}
     */
    public function connect(array $params, $username = null, $password = null, array $driverOptions = [])
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
