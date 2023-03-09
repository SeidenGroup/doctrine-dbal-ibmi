<?php

declare(strict_types=1);

namespace DoctrineDbalIbmiTest\Driver;

use DoctrineDbalIbmi\Driver\OdbcIBMiConnection;
use DoctrineDbalIbmiTest\Bootstrap;
use PHPUnit\Framework\TestCase;

final class OdbcIbmiConnectionTest extends TestCase
{
    /**
     * @return void
     */
    public function testCorrectConnectionClassIsUsed()
    {
        if (!extension_loaded('pdo_odbc')) {
            self::markTestSkipped('pdo_odbc extension not loaded');
        }
        $connection = Bootstrap::getConnection();
        $wrappedConnection = $connection->getWrappedConnection();

        self::assertInstanceOf(OdbcIBMiConnection::class, $wrappedConnection);
    }
}
