<?php

namespace DoctrineDbalIbmiTest\Driver;

use DoctrineDbalIbmi\Driver\DB2IBMiConnection;
use DoctrineDbalIbmiTest\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DoctrineDbalIbmi\Driver\DB2IBMiConnection
 *
 * @requires ibm_db2
 *
 * @group ibm_db2
 */
class DB2IBMiConnectionTest extends TestCase
{
    /**
     * @return void
     */
    public function testCorrectConnectionClassIsUsed()
    {
        if (!extension_loaded('ibm_db2')) {
            self::markTestSkipped('ibm_db2 extension not loaded');
        }
        $connection = Bootstrap::getConnection();
        $wrappedConnection = $connection->getWrappedConnection();

        self::assertInstanceOf(DB2IBMiConnection::class, $wrappedConnection);
    }
}
