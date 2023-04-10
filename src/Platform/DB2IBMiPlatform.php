<?php

/*
 * This file is part of the doctrine-dbal-ibmi package.
 * Copyright (c) 2016 Alan Seiden Consulting LLC, James Titcumb
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DoctrineDbalIbmi\Platform;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\DB2Platform;
use Doctrine\DBAL\Types\Types;

class DB2IBMiPlatform extends DB2Platform
{
    /**
     * @see https://www.ibm.com/docs/en/rdfi/9.6.0?topic=views-sqlcolumns#d115487e7
     */
    private const SQLCOLUMNS_PSEUDO_COLUMN_IS_IDENTITY = 2;
    private const SQLCOLUMNS_HAS_DEFAULT_AS_IDENTITY_GENERATED_BY_DEFAULT = 'J';

    /**
     * @see https://www.ibm.com/docs/en/rdfi/9.6.0?topic=views-syscst#d152672e7
     */
    private const SYSCST_CONSTRAINT_TYPE_PRIMARY_KEY = 'PRIMARY KEY';
    private const SYSCST_CONSTRAINT_TYPE_UNIQUE = 'UNIQUE';

    /**
     * @see https://www.ibm.com/docs/en/rdfi/9.6.0?topic=views-tables#d265436e7
     */
    private const TABLES_TABLE_TYPE_BASE_TABLE = 'BASE TABLE';

    /**
     * This method is overridden in order to avoid behavior changes when using doctrine/dbal >= 2.8, because a change
     * introduced in the limit for the `CHAR` type in the parent class.
     *
     * @see https://github.com/doctrine/dbal/pull/3133
     *
     * {@inheritDoc}
     */
    public function getCharMaxLength(): int
    {
        // The maximum length of `CHAR` in bytes is 32765 in DB2 for i, but we are using 255 here in order to keep
        // consistency with the default values provided by doctrine/dbal 2.x.
        // @see https://www.ibm.com/docs/en/i/7.1?topic=reference-sql-limits#rbafzlimtabs__btable2.

        return 255;
    }

    /**
     * {@inheritDoc}
     */
    public function initializeDoctrineTypeMappings()
    {
        $this->doctrineTypeMapping = [
            'smallint' => Types::SMALLINT,
            'bigint' => Types::BIGINT,
            'integer' => Types::INTEGER,
            'rowid' => Types::INTEGER,
            'time' => Types::TIME_MUTABLE,
            'date' => Types::DATE_MUTABLE,
            'varchar' => Types::STRING,
            'character' => Types::STRING,
            'char' => Types::STRING,
            'nvarchar' => Types::STRING,
            'nchar' => Types::STRING,
            'char () for bit data' => Types::STRING,
            'varchar () for bit data' => Types::STRING,
            'varg' => Types::STRING,
            'vargraphic' => Types::STRING,
            'graphic' => Types::STRING,
            'varbinary' => Types::BINARY,
            'binary' => Types::BINARY,
            'varbin' => Types::BINARY,
            'clob' => Types::TEXT,
            'nclob' => Types::TEXT,
            'dbclob' => Types::TEXT,
            'blob' => Types::BLOB,
            'decimal' => Types::DECIMAL,
            'numeric' => Types::FLOAT,
            'double' => Types::FLOAT,
            'real' => Types::FLOAT,
            'float' => Types::FLOAT,
            'timestamp' => Types::DATETIME_MUTABLE,
            'timestmp' => Types::DATETIME_MUTABLE,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getListTableColumnsSQL($table, $database = null)
    {
        assert(null !== $database);

        return sprintf(
            <<<'SQL'
SELECT DISTINCT
    c.COLUMN_DEF AS default,
    c.TABLE_SCHEM AS tabschema,
    c.TABLE_NAME AS tabname,
    c.COLUMN_NAME AS colname,
    c.ORDINAL_POSITION AS colno,
    c.TYPE_NAME AS typename,
    c.IS_NULLABLE AS nulls,
    c.COLUMN_SIZE AS length,
    c.DECIMAL_DIGITS AS scale,
    CASE
        WHEN c.PSEUDO_COLUMN = %1$u THEN 'YES'
        ELSE 'NO'
    END AS identity,
    pk.CONSTRAINT_TYPE AS tabconsttype,
    pk.KEY_SEQ AS colseq,
    CASE
        WHEN c.HAS_DEFAULT = '%2$s' THEN 1
        ELSE 0
    END AS autoincrement
FROM
    SYSIBM.sqlcolumns AS c
LEFT JOIN (
    SELECT
        tc.TABLE_SCHEMA,
        tc.TABLE_NAME,
        tc.CONSTRAINT_TYPE,
        spk.COLUMN_NAME,
        spk.KEY_SEQ
    FROM
        SYSIBM.TABLE_CONSTRAINTS tc
    LEFT JOIN
        SYSIBM.SQLPRIMARYKEYS spk
        ON tc.CONSTRAINT_NAME = spk.PK_NAME
        AND tc.TABLE_SCHEMA = spk.TABLE_SCHEM
        AND tc.TABLE_NAME = spk.TABLE_NAME
    WHERE
        CONSTRAINT_TYPE = '%3$s'
        AND tc.TABLE_SCHEMA = UPPER('%4$s')
        AND UPPER(tc.TABLE_NAME) = UPPER('%5$s')
    ) pk
    ON c.TABLE_SCHEM = pk.TABLE_SCHEMA
    AND c.TABLE_NAME = pk.TABLE_NAME
    AND c.COLUMN_NAME = pk.COLUMN_NAME
WHERE
    c.TABLE_SCHEM = UPPER('%4$s')
    AND UPPER(c.TABLE_NAME) = UPPER('%5$s')
ORDER BY
    c.ORDINAL_POSITION
SQL
            ,
            self::SQLCOLUMNS_PSEUDO_COLUMN_IS_IDENTITY,
            self::SQLCOLUMNS_HAS_DEFAULT_AS_IDENTITY_GENERATED_BY_DEFAULT,
            self::SYSCST_CONSTRAINT_TYPE_PRIMARY_KEY,
            $database,
            $table
        );
    }

    /**
     * @param string $database
     *
     * {@inheritDoc}
     */
    public function getListTablesSQL($database = null)
    {
        assert(null !== $database);

        return sprintf(
            <<<'SQL'
SELECT DISTINCT
    t.NAME
FROM
    SYSIBM.tables t
WHERE
    t.TABLE_TYPE = '%s'
    AND t.TABLE_SCHEMA = UPPER('%s')
ORDER BY
    t.NAME
SQL
            ,
            self::TABLES_TABLE_TYPE_BASE_TABLE,
            $database
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getListViewsSQL($database)
    {
        return sprintf(
            <<<'SQL'
SELECT DISTINCT
    v.NAME,
    v.TEXT
FROM
    QSYS2.sysviews v
WHERE
    v.TABLE_SCHEMA = UPPER('%s')
ORDER BY
    v.NAME
SQL
            ,
            $database
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getListTableIndexesSQL($table, $database = null)
    {
        assert(null !== $database);

        return sprintf(
            <<<'SQL'
SELECT
    scc.CONSTRAINT_NAME AS key_name,
    scc.COLUMN_NAME AS column_name,
    CASE
        WHEN sc.CONSTRAINT_TYPE = '%s' THEN 1
        ELSE 0
    END AS primary,
    CASE
        WHEN sc.CONSTRAINT_TYPE = '%s' THEN 0
        ELSE 1
    END AS non_unique
FROM
    QSYS2.syscstcol scc
LEFT JOIN
    QSYS2.syscst sc
    ON scc.TABLE_SCHEMA = sc.TABLE_SCHEMA
    AND scc.TABLE_NAME = sc.TABLE_NAME
    AND scc.CONSTRAINT_NAME = sc.CONSTRAINT_NAME
WHERE
    scc.TABLE_SCHEMA = UPPER('%s')
    AND scc.TABLE_NAME = UPPER('%s')
SQL
            ,
            self::SYSCST_CONSTRAINT_TYPE_PRIMARY_KEY,
            self::SYSCST_CONSTRAINT_TYPE_UNIQUE,
            $database,
            $table
        );
    }

    /**
     * @param string|null $database
     *
     * {@inheritDoc}
     */
    public function getListTableForeignKeysSQL($table, $database = null)
    {
        assert(null !== $database);

        return sprintf(
            <<<'SQL'
SELECT DISTINCT
    fk.COLUMN_NAME AS local_column,
    pk.TABLE_NAME AS foreign_table,
    pk.COLUMN_NAME AS foreign_column,
    fk.CONSTRAINT_NAME AS index_name,
    rc.UPDATE_RULE AS on_update,
    rc.DELETE_RULE AS on_delete
FROM
    QSYS2.REFERENTIAL_CONSTRAINTS rc
LEFT JOIN
    QSYS2.SYSCSTCOL fk
    ON rc.CONSTRAINT_SCHEMA = fk.CONSTRAINT_SCHEMA
    AND rc.CONSTRAINT_NAME = fk.CONSTRAINT_NAME
LEFT JOIN
    QSYS2.SYSCSTCOL pk
    ON rc.UNIQUE_CONSTRAINT_SCHEMA = pk.CONSTRAINT_SCHEMA
    AND rc.UNIQUE_CONSTRAINT_NAME = pk.CONSTRAINT_NAME
WHERE
    fk.TABLE_SCHEMA = UPPER('%s')
    AND fk.TABLE_NAME = UPPER('%s')
SQL
            ,
            $database,
            $table
        );
    }

    /**
     * @return string
     *
     * @throws DBALException if not supported on this platform
     */
    public function getListDatabasesSQL()
    {
        return <<<'SQL'
SELECT DISTINCT
    t.TABLE_SCHEMA
FROM
    SYSIBM.tables t
ORDER BY
    t.TABLE_SCHEMA
SQL
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function getCreateDatabaseSQL($database)
    {
        return sprintf('CREATE COLLECTION %s', $database);
    }

    /**
     * {@inheritDoc}
     */
    public function getDateTimeFormatString()
    {
        return 'Y-m-d-H.i.s.u';
    }

    /**
     * {@inheritDoc}
     */
    protected function getVarcharTypeDeclarationSQLSnippet($length, $fixed)
    {
        return $fixed ? (0 < $length ? 'CHAR('.$length.')' : 'CHAR(255)')
            : (0 < $length ? 'VARCHAR('.$length.')' : 'VARCHAR(255)');
    }

    /**
     * {@inheritDoc}
     *
     * @phpstan-assert int<0, max> $limit
     */
    protected function doModifyLimitQuery($query, $limit, $offset = null)
    {
        if ($offset > 0) {
            $query .= sprintf(' OFFSET %u ROW%s', $offset, 1 === $offset ? '' : 'S');
        }

        if (null !== $limit) {
            if ($limit < 0) {
                throw new Exception(sprintf('Limit must be a positive integer or zero, %d given', $limit));
            }

            $query .= sprintf(' FETCH %s %u ROW%s ONLY', 0 === $offset ? 'FIRST' : 'NEXT', $limit, 1 === $limit ? '' : 'S');
        }

        return $query;
    }
}
