<?php

namespace DoctrineDbalIbmi\Tests\Platform;

use Doctrine\DBAL\Types\Types;
use DoctrineDbalIbmi\Driver\DB2Driver;
use DoctrineDbalIbmi\Tests\AbstractTestCase;

final class DB2IBMiPlatformTest extends AbstractTestCase
{
    /**
     * @return iterable<int|string, array<int, string>>
     *
     * @phpstan-return iterable<int|string, array{0: string, 1: Types::*}>
     */
    public function typeMappingProvider(): iterable
    {
        yield ['smallint', Types::SMALLINT];
        yield ['bigint', Types::BIGINT];
        yield ['integer', Types::INTEGER];
        yield ['rowid', Types::INTEGER];
        yield ['time', Types::TIME_MUTABLE];
        yield ['date', Types::DATE_MUTABLE];
        yield ['varchar', Types::STRING];
        yield ['character', Types::STRING];
        yield ['char', Types::STRING];
        yield ['nvarchar', Types::STRING];
        yield ['nchar', Types::STRING];
        yield ['char () for bit data', Types::STRING];
        yield ['varchar () for bit data', Types::STRING];
        yield ['varg', Types::STRING];
        yield ['vargraphic', Types::STRING];
        yield ['graphic', Types::STRING];
        yield ['varbinary', Types::BINARY];
        yield ['binary', Types::BINARY];
        yield ['varbin', Types::BINARY];
        yield ['clob', Types::TEXT];
        yield ['nclob', Types::TEXT];
        yield ['dbclob', Types::TEXT];
        yield ['blob', Types::BLOB];
        yield ['decimal', Types::DECIMAL];
        yield ['numeric', Types::FLOAT];
        yield ['double', Types::FLOAT];
        yield ['real', Types::FLOAT];
        yield ['float', Types::FLOAT];
        yield ['timestamp', Types::DATETIME_MUTABLE];
        yield ['timestmp', Types::DATETIME_MUTABLE];
    }

    /**
     * @requires ibm_db2
     *
     * @return void
     *
     * @dataProvider typeMappingProvider
     */
    public function testTypeMappings(string $dbType, string $expectedMapping)
    {
        $connection = self::getConnection(DB2Driver::class);
        $platform = $connection->getDatabasePlatform();

        self::assertSame($expectedMapping, $platform->getDoctrineTypeMapping($dbType));
    }

    /**
     * @return iterable<int|string, array<int, string|array<string, int|bool>>>
     */
    public function varcharTypeDeclarationProvider(): iterable
    {
        yield ['VARCHAR(1024)', ['length' => 1024]];
        yield ['VARCHAR(255)', []];
        yield ['VARCHAR(255)', ['length' => 0]];
        yield ['CLOB(1M)', ['fixed' => true, 'length' => 1024]];
        yield ['CHAR(255)', ['fixed' => true]];
        yield ['CHAR(255)', ['fixed' => true, 'length' => 0]];
        yield ['CLOB(1M)', ['length' => 5000]];
    }

    /**
     * @requires ibm_db2
     *
     * @return void
     *
     * @dataProvider varcharTypeDeclarationProvider
     */
    public function testVarcharTypeDeclarationSQLSnippet(string $expectedSql, array $fieldDef)
    {
        $connection = self::getConnection(DB2Driver::class);
        $platform = $connection->getDatabasePlatform();

        self::assertSame($expectedSql, $platform->getVarcharTypeDeclarationSQL($fieldDef));
    }

    /**
     * @return iterable<int|string, array<int, int|string|null>>
     *
     * @phpstan-return iterable<int|string, array{0: string, 1: string, 2: ?int, 3: ?int}>
     */
    public function limitQueryProvider(): iterable
    {
        yield [
            'SELECT DOCTRINE_TBL.*'
            .' FROM (SELECT DOCTRINE_TBL1.*, ROW_NUMBER() OVER() AS DOCTRINE_ROWNUM'
            .' FROM (SELECT * FROM mytable) DOCTRINE_TBL1) DOCTRINE_TBL'
            .' WHERE DOCTRINE_TBL.DOCTRINE_ROWNUM BETWEEN 3 AND 7',
            'SELECT * FROM mytable',
            5,
            2,
        ];

        yield [
            'SELECT DOCTRINE_TBL.*'
            .' FROM (SELECT DOCTRINE_TBL1.*, ROW_NUMBER() OVER() AS DOCTRINE_ROWNUM'
            .' FROM (SELECT * FROM mytable) DOCTRINE_TBL1) DOCTRINE_TBL'
            .' WHERE DOCTRINE_TBL.DOCTRINE_ROWNUM BETWEEN 2 AND 2',
            'SELECT * FROM mytable',
            1,
            1,
        ];

        yield [
            'SELECT DOCTRINE_TBL.*'
            .' FROM (SELECT DOCTRINE_TBL1.*, ROW_NUMBER() OVER() AS DOCTRINE_ROWNUM'
            .' FROM (SELECT * FROM mytable) DOCTRINE_TBL1) DOCTRINE_TBL'
            .' WHERE DOCTRINE_TBL.DOCTRINE_ROWNUM BETWEEN 3 AND 3',
            'SELECT * FROM mytable',
            1,
            2,
        ];

        yield [
            'SELECT * FROM mytable FETCH FIRST 0 ROWS ONLY',
            'SELECT * FROM mytable',
            0,
            null,
        ];

        yield [
            'SELECT * FROM mytable FETCH FIRST 1 ROWS ONLY',
            'SELECT * FROM mytable',
            1,
            0,
        ];

        yield [
            'SELECT * FROM mytable FETCH FIRST 1 ROWS ONLY',
            'SELECT * FROM mytable',
            1,
            null,
        ];

        yield [
            'SELECT DOCTRINE_TBL.*'
            .' FROM (SELECT DOCTRINE_TBL1.*, ROW_NUMBER() OVER() AS DOCTRINE_ROWNUM'
            .' FROM (SELECT * FROM mytable) DOCTRINE_TBL1) DOCTRINE_TBL'
            .' WHERE DOCTRINE_TBL.DOCTRINE_ROWNUM BETWEEN 2 AND 1',
            'SELECT * FROM mytable',
            null,
            1,
        ];

        yield [
            'SELECT * FROM mytable',
            'SELECT * FROM mytable',
            null,
            null,
        ];

        yield [
            'SELECT * FROM mytable ORDER BY created_at FETCH FIRST 1 ROWS ONLY',
            'SELECT * FROM mytable ORDER BY created_at',
            1,
            0,
        ];

        yield [
            'SELECT * FROM mytable ORDER BY created_at',
            'SELECT * FROM mytable ORDER BY created_at',
            null,
            0,
        ];

        yield [
            'SELECT * FROM mytable ORDER BY created_at FETCH FIRST 0 ROWS ONLY',
            'SELECT * FROM mytable ORDER BY created_at',
            0,
            null,
        ];
    }

    /**
     * @return void
     *
     * @dataProvider limitQueryProvider
     */
    public function testLimitQuery(string $expected, string $sql, ?int $limit = null, ?int $offset = null)
    {
        $connection = self::getConnection(DB2Driver::class);
        $platform = $connection->getDatabasePlatform();

        self::assertSame($expected, $platform->modifyLimitQuery($sql, $limit, $offset));
    }
}
