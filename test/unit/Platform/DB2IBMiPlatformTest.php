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
        $em = Bootstrap::getEntityManager();
        /** @var DB2IBMiPlatform $platform */
        $platform = $em->getConnection()->getDatabasePlatform();

        self::assertSame($expectedMapping, $platform->getDoctrineTypeMapping($dbType));
    }
}
