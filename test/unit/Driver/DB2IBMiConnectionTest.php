<?php

namespace DoctrineDbalIbmiTest\Driver;

use DoctrineDbalIbmi\Driver\DB2IBMiConnection;
use DoctrineDbalIbmiTest\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @covers \DoctrineDbalIbmi\Driver\DB2IBMiConnection
 */
class DB2IBMiConnectionTest extends TestCase
{
    public function testCorrectConnectionClassIsUsed()
    {
        if (!extension_loaded('ibm_db2')) {
            $this->markTestSkipped('ibm_db2 extension not loaded');
        }
        $em = Bootstrap::getEntityManager();

        $connection = $em->getConnection()->getWrappedConnection();

        self::assertInstanceOf(DB2IBMiConnection::class, $connection);
    }
}
