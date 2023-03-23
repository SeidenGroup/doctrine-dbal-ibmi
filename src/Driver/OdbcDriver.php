<?php

/*
 * This file is part of the doctrine-dbal-ibmi package.
 * Copyright (c) 2016 Alan Seiden Consulting LLC, James Titcumb
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DoctrineDbalIbmi\Driver;

use Doctrine\DBAL\VersionAwarePlatformDriver;
use DoctrineDbalIbmi\Platform\DB2IBMiPlatform;

class OdbcDriver extends AbstractDB2Driver implements VersionAwarePlatformDriver
{
    /**
     * {@inheritdoc}
     */
    public function connect(array $params, $username = null, $password = null, array $driverOptions = [])
    {
        // @todo: Remove the following conditional block in the next major version.
        if (isset($params['username'])) {
            @trigger_error(sprintf(
                'Passing parameter "username" to "%s()" is deprecated since alanseiden/doctrine-dbal-ibmi 0.1 and its support'
                .' will be removed in version 0.2. Use "user" parameter instead.',
                __METHOD__
            ), E_USER_DEPRECATED);

            $params['user'] = $params['username'];
            unset($params['username']);
        }

        assert(is_scalar($params['user']));
        assert(is_scalar($params['password']));
        $username = (string) ($username ?? $params['user'] ?? '');
        $password = (string) ($password ?? $params['password'] ?? '');

        unset($params['user'], $params['password']);

        $params['driver'] = '{IBM i Access ODBC Driver}';
        $params['dsn'] = 'odbc:'.DataSourceName::fromConnectionParameters($params)->toString();

        unset($params['driver'], $params['host'], $params['port'], $params['protocol']);

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
}
