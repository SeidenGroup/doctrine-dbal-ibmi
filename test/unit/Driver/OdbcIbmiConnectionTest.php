<?php

declare(strict_types=1);

namespace DoctrineDbalIbmiTest\Driver;

use DoctrineDbalIbmi\Driver\DB2IBMiConnection;
use DoctrineDbalIbmi\Driver\OdbcIBMiConnection;
use DoctrineDbalIbmiTest\Bootstrap;
use PHPUnit\Framework\TestCase;

final class OdbcIbmiConnectionTest extends TestCase
{
    public function testCorrectConnectionClassIsUsed()
    {
        if (!extension_loaded('pdo')) {
            $this->markTestSkipped('pdo extension not loaded');
        }
        $em = Bootstrap::getEntityManager();

        $connection = $em->getConnection()->getWrappedConnection();

        self::assertInstanceOf(OdbcIBMiConnection::class, $connection);
    }
}