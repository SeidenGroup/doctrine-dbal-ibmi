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
        $em = self::getEntityManager(DB2Driver::class);

        $connection = $em->getConnection()->getWrappedConnection();

        self::assertInstanceOf(DB2IBMiConnection::class, $connection);
    }
}
