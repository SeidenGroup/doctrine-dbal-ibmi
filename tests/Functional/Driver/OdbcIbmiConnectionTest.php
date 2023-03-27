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
    public function testGetListTableColumnsSQL()
    {
        $connection = self::getConnection(OdbcDriver::class);

        $sql = $connection->getDatabasePlatform()->getListTableColumnsSQL('SYSTABLES', 'QSYS2');

        $result = $connection
            ->executeQuery($sql)
            ->fetchAllAssociative();

        self::assertCount(32, $result);
        self::assertCount(13, $result[0]);
        self::assertArrayHasKey('TABSCHEMA', $result[0]);
        self::assertArrayHasKey('TABNAME', $result[0]);
        self::assertSame('QSYS2', $result[0]['TABSCHEMA']);
        self::assertSame('SYSTABLES', $result[0]['TABNAME']);
    }

    /**
     * @return void
     */
    public function testGetListTableIndexesSQL()
    {
        $connection = self::getConnection(OdbcDriver::class);

        $sql = $connection->getDatabasePlatform()->getListTableIndexesSQL('SYSTABLES', 'QSYS2');

        $result = $connection
            ->executeQuery($sql)
            ->fetchAllAssociative();

        // self::assertCount(32, $result);
        self::assertCount(4, $result[0]);
        self::assertArrayHasKey('KEY_NAME', $result[0]);
        self::assertArrayHasKey('COLUMN_NAME', $result[0]);

        var_dump($result);
    }

    /**
     * @return void
     */
    public function testGetListTableForeignKeysSQL()
    {
        $connection = self::getConnection(OdbcDriver::class);

        $sql = $connection->getDatabasePlatform()->getListTableForeignKeysSQL('SYSTABLES');

        $result = $connection
            ->executeQuery($sql)
            ->fetchAllAssociative();

        // self::assertCount(32, $result);
        self::assertCount(6, $result[0]);
        self::assertArrayHasKey('LOCAL_COLUMN', $result[0]);
        self::assertArrayHasKey('FOREIGN_TABLE', $result[0]);

        var_dump($result);
    }
}
