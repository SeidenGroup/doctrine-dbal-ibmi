<?php

/*
 * This file is part of the doctrine-dbal-ibmi package.
 * Copyright (c) 2016 Alan Seiden Consulting LLC, James Titcumb
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DoctrineDbalIbmi\Tests\Functional\Driver;

use Doctrine\DBAL\DriverManager;
use DoctrineDbalIbmi\Driver\DB2Driver;
use DoctrineDbalIbmi\Driver\DB2IBMiConnection;
use DoctrineDbalIbmi\Tests\AbstractTestCase;

/**
 * @requires ibm_db2
 */
final class DB2IBMiConnectionTest extends AbstractTestCase
{
    /**
     * @return void
     */
    public function testCorrectConnectionClassIsUsed()
    {
        $connection = self::getConnection(DB2Driver::class);
        $wrappedConnection = $connection->getWrappedConnection();

        static::assertInstanceOf(DB2IBMiConnection::class, $wrappedConnection);
    }

    /**
     * Make sure the `db2cli.ini` file in the root of the `tests/` dir is loaded in your database config.
     *
     * @see https://www.php.net/manual/en/function.db2-connect.php
     *
     * @return void
     */
    public function testCatalogedConnection()
    {
        $connectionParams = [
            'driverClass' => DB2Driver::class,
            'dbname' => 'DOCTRINE_CATALOGED',
        ];

        $connection = DriverManager::getConnection($connectionParams);
        $wrappedConnection = $connection->getWrappedConnection();

        static::assertInstanceOf(DB2IBMiConnection::class, $wrappedConnection);
    }

    /**
     * @return void
     */
    public function testUncatalogedConnectionFromDsn()
    {
        $connectionParams = [
            'driverClass' => DB2Driver::class,
            'dbname' => sprintf(
                'DRIVER={IBM DB2 ODBC DRIVER};HOSTNAME=%s;PORT=%s;DATABASE=%s;UID=%s;PWD=%s;',
                getenv('db_host'),
                getenv('db_port'),
                getenv('db_name'),
                getenv('db_user'),
                getenv('db_password')
            ),
        ];

        $connection = DriverManager::getConnection($connectionParams);
        $wrappedConnection = $connection->getWrappedConnection();

        static::assertInstanceOf(DB2IBMiConnection::class, $wrappedConnection);
    }
}
