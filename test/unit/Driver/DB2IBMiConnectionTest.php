<?php

namespace DoctrineDbalIbmiTest\Driver;

use DoctrineDbalIbmi\Driver\DB2IBMiConnection;
use DoctrineDbalIbmiTest\Bootstrap;

class DB2IBMiConnectionTest extends \PHPUnit_Framework_TestCase
{
    public function testCorrectConnectionClassIsUsed()
    {
        $em = Bootstrap::getEntityManager();

        $connection = $em->getConnection()->getWrappedConnection();

        self::assertInstanceOf(DB2IBMiConnection::class, $connection);
    }
}
