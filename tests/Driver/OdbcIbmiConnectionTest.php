<?php

declare(strict_types=1);

namespace DoctrineDbalIbmi\Tests\Driver;

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

        self::assertInstanceOf(OdbcIBMiConnection::class, $wrappedConnection);
    }
}
