<?php

declare(strict_types=1);

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

        self::assertInstanceOf(OdbcIBMiConnection::class, $wrappedConnection);
    }

    /**
     * @return void
     */
    public function testSelect()
    {
        $connection = self::getConnection(OdbcDriver::class);
        $sql = 'SELECT TABLE_NAME, TABLE_OWNER'
            .' FROM QSYS2.SYSTABLES'
            .' WHERE TABLE_OWNER = \'ALAN\''
            .' ORDER BY TABLE_NAME DESC'
            .' LIMIT 10';

        $result = $connection
            ->executeQuery($sql)
            ->fetchAllAssociative();

        self::assertCount(10, $result);
        self::assertCount(2, $result[0]);
        self::assertArrayHasKey('TABLE_NAME', $result[0]);
        self::assertArrayHasKey('TABLE_OWNER', $result[0]);
        self::assertSame('WEATHER_RAW', $result[0]['TABLE_NAME']); // ASC: "@TP025"
    }

    /**
     * @return void
     */
    public function testPlaformMethods()
    {
        $connection = self::getConnection(OdbcDriver::class);

        $sql = $connection->getDatabasePlatform()->getListTableColumnsSQL('SYSTABLES', 'QSYS2');

        $result = $connection
            ->executeQuery($sql)
            ->fetchAllAssociative();

        self::assertCount(13, $result[0]);
        self::assertArrayHasKey('tabschema', $result[0]);
        self::assertArrayHasKey('tabname', $result[0]);

        var_dump($result);
    }
}
