<?php

declare(strict_types=1);

/*
 * This file is part of the doctrine-dbal-ibmi package.
 * Copyright (c) 2016 Alan Seiden Consulting LLC, James Titcumb
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DoctrineDbalIbmi\Tests\Functional\Driver;

use DoctrineDbalIbmi\Driver\OdbcDriver;
use DoctrineDbalIbmi\Driver\OdbcIBMiConnection;
use DoctrineDbalIbmi\Tests\AbstractTestCase;

/**
 * @requires pdo_odbc
 */
final class OdbcIbmiConnectionTest extends AbstractTestCase
{
    /**
     * @return void
     */
    public function testCorrectConnectionClassIsUsed()
    {
        $connection = self::getConnection(OdbcDriver::class);
        $wrappedConnection = $connection->getWrappedConnection();

        static::assertInstanceOf(OdbcIBMiConnection::class, $wrappedConnection);
    }
}
