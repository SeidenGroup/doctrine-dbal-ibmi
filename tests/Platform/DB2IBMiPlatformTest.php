<?php

namespace DoctrineDbalIbmi\Tests\Platform;

use DoctrineDbalIbmi\Driver\DB2Driver;
use DoctrineDbalIbmi\Platform\DB2IBMiPlatform;
use DoctrineDbalIbmi\Tests\AbstractTestCase;

class DB2IBMiPlatformTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed, array<int, string>>
     */
    public function typeMappingProvider(): iterable
    {
        yield ['smallint', 'smallint'];
        yield ['bigint', 'bigint'];
        yield ['integer', 'integer'];
        yield ['rowid', 'integer'];
        yield ['time', 'time'];
        yield ['date', 'date'];
        yield ['varchar', 'string'];
        yield ['character', 'string'];
        yield ['char', 'string'];
        yield ['nvarchar', 'string'];
        yield ['nchar', 'string'];
        yield ['char () for bit data', 'string'];
        yield ['varchar () for bit data', 'string'];
        yield ['varg', 'string'];
        yield ['vargraphic', 'string'];
        yield ['graphic', 'string'];
        yield ['varbinary', 'binary'];
        yield ['binary', 'binary'];
        yield ['varbin', 'binary'];
        yield ['clob', 'text'];
        yield ['nclob', 'text'];
        yield ['dbclob', 'text'];
        yield ['blob', 'blob'];
        yield ['decimal', 'decimal'];
        yield ['numeric', 'float'];
        yield ['double', 'float'];
        yield ['real', 'float'];
        yield ['float', 'float'];
        yield ['timestamp', 'datetime'];
        yield ['timestmp', 'datetime'];
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\ORMException
     *
     * @return void
     *
     * @dataProvider typeMappingProvider
     *
     * @requires ibm_db2
     */
    public function testTypeMappings(string $dbType, string $expectedMapping)
    {
        $em = self::getEntityManager(DB2Driver::class);
        $platform = $em->getConnection()->getDatabasePlatform();

        self::assertSame($expectedMapping, $platform->getDoctrineTypeMapping($dbType));
    }

    /**
     * @return iterable<mixed, array<int, string|array<string, int|bool>>>
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
     * @throws \Doctrine\ORM\ORMException
     *
     * @return void
     *
     * @dataProvider varcharTypeDeclarationProvider
     *
     * @requires ibm_db2
     */
    public function testVarcharTypeDeclarationSQLSnippet(string $expectedSql, array $fieldDef)
    {
        $em = self::getEntityManager(DB2Driver::class);
        $platform = $em->getConnection()->getDatabasePlatform();

        self::assertSame($expectedSql, $platform->getVarcharTypeDeclarationSQL($fieldDef));
    }
}
