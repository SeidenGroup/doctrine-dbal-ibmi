<?php

namespace DoctrineDbalIbmiTest\Platform;

use DoctrineDbalIbmi\Platform\DB2IBMiPlatform;
use DoctrineDbalIbmiTest\Bootstrap;

/**
 * @covers \DoctrineDbalIbmi\Platform\DB2IBMiPlatform
 */
class DB2IBMiPlatformTest extends \PHPUnit_Framework_TestCase
{
    public function typeMappingProvider()
    {
        return [
            ['smallint', 'smallint'],
            ['bigint', 'bigint'],
            ['integer', 'integer'],
            ['rowid', 'integer'],
            ['time', 'time'],
            ['date', 'date'],
            ['varchar', 'string'],
            ['character', 'string'],
            ['char', 'string'],
            ['nvarchar', 'string'],
            ['nchar', 'string'],
            ['char () for bit data', 'string'],
            ['varchar () for bit data', 'string'],
            ['varg', 'string'],
            ['vargraphic', 'string'],
            ['graphic', 'string'],
            ['varbinary', 'binary'],
            ['binary', 'binary'],
            ['varbin', 'binary'],
            ['clob', 'text'],
            ['nclob', 'text'],
            ['dbclob', 'text'],
            ['blob', 'blob'],
            ['decimal', 'decimal'],
            ['numeric', 'float'],
            ['double', 'float'],
            ['real', 'float'],
            ['float', 'float'],
            ['timestamp', 'datetime'],
            ['timestmp', 'datetime'],
        ];
    }

    /**
     * @param string $dbType
     * @param string $expectedMapping
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\ORMException
     * @dataProvider typeMappingProvider
     */
    public function testTypeMappings($dbType, $expectedMapping)
    {
        if (!extension_loaded('ibm_db2')) {
            $this->markTestSkipped('ibm_db2 extension not loaded');
        }
        $em = Bootstrap::getEntityManager();
        /** @var DB2IBMiPlatform $platform */
        $platform = $em->getConnection()->getDatabasePlatform();

        self::assertSame($expectedMapping, $platform->getDoctrineTypeMapping($dbType));
    }

    public function varcharTypeDeclarationProvider()
    {
        return [
            ['VARCHAR(1024)', ['length' => 1024]],
            ['VARCHAR(255)', []],
            ['VARCHAR(255)', ['length' => 0]],
            ['CHAR(1024)', ['fixed' => true, 'length' => 1024]],
            ['CHAR(255)', ['fixed' => true]],
            ['CHAR(255)', ['fixed' => true, 'length' => 0]],
            ['CLOB(1M)', ['length' => 5000]],
        ];
    }

    /**
     * @param string $expectedSql
     * @param array $fieldDef
     * @throws \Doctrine\ORM\ORMException
     * @dataProvider varcharTypeDeclarationProvider
     */
    public function testVarcharTypeDeclarationSQLSnippet($expectedSql, array $fieldDef)
    {
        if (!extension_loaded('ibm_db2')) {
            $this->markTestSkipped('ibm_db2 extension not loaded');
        }
        $em = Bootstrap::getEntityManager();
        /** @var DB2IBMiPlatform $platform */
        $platform = $em->getConnection()->getDatabasePlatform();

        self::assertSame($expectedSql, $platform->getVarcharTypeDeclarationSQL($fieldDef));
    }
}
