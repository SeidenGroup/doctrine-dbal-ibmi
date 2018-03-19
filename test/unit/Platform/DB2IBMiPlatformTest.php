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
        $em = Bootstrap::getEntityManager();
        /** @var DB2IBMiPlatform $platform */
        $platform = $em->getConnection()->getDatabasePlatform();

        self::assertSame($expectedSql, $platform->getVarcharTypeDeclarationSQL($fieldDef));
    }

    public function testModifiesLimitQueryWithOrderBy()
    {
        $em = Bootstrap::getEntityManager();

        /** @var DB2IBMiPlatform $platform */
        $platform = $em->getConnection()->getDatabasePlatform();

        // no limit, no offset, order by id
        self::assertEquals(
            'SELECT * FROM user ORDER BY id',
            $platform->modifyLimitQuery('SELECT * FROM user ORDER BY id', null, null)
        );

        // 10 row limit, no offset, order by id
        self::assertEquals(
            'SELECT db22.* FROM (SELECT db21.*, ROW_NUMBER() OVER(ORDER BY id) AS DC_ROWNUM FROM (SELECT * FROM user ORDER BY id) db21) db22 WHERE db22.DC_ROWNUM <= 10',
            $platform->modifyLimitQuery('SELECT * FROM user ORDER BY id', 10)
        );

        // 0 limit, 10 rows offset, order by id
        self::assertEquals(
            'SELECT db22.* FROM (SELECT db21.*, ROW_NUMBER() OVER(ORDER BY id) AS DC_ROWNUM FROM (SELECT * FROM user ORDER BY id) db21) db22 WHERE db22.DC_ROWNUM >= 11 AND db22.DC_ROWNUM <= 10',
            $platform->modifyLimitQuery('SELECT * FROM user ORDER BY id', 0, 10)
        );

        // select specific columns
        self::assertEquals(
            'SELECT db22.* FROM (SELECT db21.*, ROW_NUMBER() OVER(ORDER BY USERID_3 ASC) AS DC_ROWNUM FROM (SELECT t0.manager AS MANAGER_1, t0.name AS NAME_2, t0.userId AS USERID_3, t0.email AS EMAIL_4 FROM user t0 ORDER BY t0.userId ASC) db21) db22 WHERE db22.DC_ROWNUM >= 11 AND db22.DC_ROWNUM <= 20',
            $platform->modifyLimitQuery('SELECT t0.manager AS MANAGER_1, t0.name AS NAME_2, t0.userId AS USERID_3, t0.email AS EMAIL_4 FROM user t0 ORDER BY t0.userId ASC', 10, 10)
        );

        // select specific columns, order on 3 columns in different directions
        self::assertEquals(
            'SELECT db22.* FROM (SELECT db21.*, ROW_NUMBER() OVER(ORDER BY USERID_3 ASC,MANAGER_1 DESC,EMAIL_4 ASC) AS DC_ROWNUM FROM (SELECT t0.manager AS MANAGER_1, t0.name AS NAME_2, t0.userId AS USERID_3, t0.email AS EMAIL_4 FROM driverManagerEmployed t0 ORDER BY t0.userId ASC, t0.manager DESC, t0.email ASC) db21) db22 WHERE db22.DC_ROWNUM >= 11 AND db22.DC_ROWNUM <= 20',
            $platform->modifyLimitQuery('SELECT t0.manager AS MANAGER_1, t0.name AS NAME_2, t0.userId AS USERID_3, t0.email AS EMAIL_4 FROM driverManagerEmployed t0 ORDER BY t0.userId ASC, t0.manager DESC, t0.email ASC', 10, 10)
        );
    }
}
