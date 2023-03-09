<?php

namespace DoctrineDbalIbmi\Tests\Driver;

use DoctrineDbalIbmi\Driver\DB2Driver;
use DoctrineDbalIbmi\Driver\DB2IBMiConnection;
use DoctrineDbalIbmi\Tests\AbstractTestCase;

/**
 * @requires ibm_db2
 *
 * @group ibm_db2
 */
class DB2IBMiConnectionTest extends AbstractTestCase
{
    /**
     * @return void
     */
    public function testCorrectConnectionClassIsUsed()
    {
        $connection = self::getConnection(DB2Driver::class);

        $wrappedConnection = $connection->getWrappedConnection();

        self::assertInstanceOf(DB2IBMiConnection::class, $wrappedConnection);
    }
}
