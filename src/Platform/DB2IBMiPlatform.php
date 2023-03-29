<?php

namespace DoctrineDbalIbmi\Platform;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\DB2Platform;
use Doctrine\DBAL\Types\Types;

class DB2IBMiPlatform extends DB2Platform
{
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
            'smallint'      => Types::SMALLINT,
            'bigint'        => Types::BIGINT,
            'integer'       => Types::INTEGER,
            'rowid'         => Types::INTEGER,
            'time'          => Types::TIME_MUTABLE,
            'date'          => Types::DATE_MUTABLE,
            'varchar'       => Types::STRING,
            'character'     => Types::STRING,
            'char'          => Types::STRING,
            'nvarchar'      => Types::STRING,
            'nchar'         => Types::STRING,
            'char () for bit data' => Types::STRING,
            'varchar () for bit data' => Types::STRING,
            'varg'          => Types::STRING,
            'vargraphic'    => Types::STRING,
            'graphic'       => Types::STRING,
            'varbinary'     => Types::BINARY,
            'binary'        => Types::BINARY,
            'varbin'        => Types::BINARY,
            'clob'          => Types::TEXT,
            'nclob'         => Types::TEXT,
            'dbclob'        => Types::TEXT,
            'blob'          => Types::BLOB,
            'decimal'       => Types::DECIMAL,
            'numeric'       => Types::FLOAT,
            'double'        => Types::FLOAT,
            'real'          => Types::FLOAT,
            'float'         => Types::FLOAT,
            'timestamp'     => Types::DATETIME_MUTABLE,
            'timestmp'      => Types::DATETIME_MUTABLE,
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getVarcharTypeDeclarationSQLSnippet($length, $fixed)
    {
        return $fixed ? (0 < $length ? 'CHAR(' . $length . ')' : 'CHAR(255)')
            : (0 < $length ? 'VARCHAR(' . $length . ')' : 'VARCHAR(255)');
    }

    /**
     * This method is overridden in order to backport the fix provided in doctrine/dbal >= 2.8 for the type inference regarding
     * the maximum length for `CHAR` declared in `getCharMaxLength()`.
     *
     * @see https://github.com/doctrine/dbal/pull/3133
     *
     * {@inheritDoc}
     */
    public function getVarcharTypeDeclarationSQL(array $field)
    {
        if ( !isset($field['length'])) {
            $field['length'] = $this->getVarcharDefaultLength();
        }

        $fixed = $field['fixed'] ?? false;

        $maxLength = $fixed
            ? $this->getCharMaxLength()
            : $this->getVarcharMaxLength();

        if ($field['length'] > $maxLength) {
            return $this->getClobTypeDeclarationSQL($field);
        }

        assert(is_int($field['length']));

        return $this->getVarcharTypeDeclarationSQLSnippet($field['length'], $fixed);
    }


    /**
     * {@inheritDoc}
     */
    public function getListTableColumnsSQL($table, $database = null)
    {
        assert(null !== $database);

        return "
            SELECT DISTINCT
               c.column_def as default,
               c.table_schem as tabschema,
               c.table_name as tabname,
               c.column_name as colname,
               c.ordinal_position as colno,
               c.type_name as typename,
               c.is_nullable as nulls,
               c.column_size as length,
               c.decimal_digits as scale,
               CASE
                   WHEN c.pseudo_column = 2 THEN 'YES'
                   ELSE 'NO'
               END as identity,
               pk.constraint_type AS tabconsttype,
               pk.key_seq as colseq,
               CASE
                   WHEN c.HAS_DEFAULT = 'J' THEN 1
                   ELSE 0
               END AS autoincrement
             FROM SYSIBM.sqlcolumns as c
             LEFT JOIN
             (
                SELECT
                tc.TABLE_SCHEMA,
                tc.TABLE_NAME,
                tc.CONSTRAINT_TYPE,
                spk.COLUMN_NAME,
                spk.KEY_SEQ
                FROM SYSIBM.TABLE_CONSTRAINTS tc
                LEFT JOIN SYSIBM.SQLPRIMARYKEYS spk
                    ON tc.CONSTRAINT_NAME = spk.PK_NAME AND tc.TABLE_SCHEMA = spk.TABLE_SCHEM AND tc.TABLE_NAME = spk.TABLE_NAME
                WHERE CONSTRAINT_TYPE = 'PRIMARY KEY'
                AND UPPER(tc.TABLE_NAME) = UPPER('" . $table . "')
                AND tc.TABLE_SCHEMA = UPPER('" . $database . "')
             ) pk ON
                c.TABLE_SCHEM = pk.TABLE_SCHEMA
                AND c.TABLE_NAME = pk.TABLE_NAME
                AND c.COLUMN_NAME = pk.COLUMN_NAME
             WHERE
                UPPER(c.TABLE_NAME) = UPPER('" . $table . "')
                AND c.TABLE_SCHEM = UPPER('" . $database . "')
             ORDER BY c.ordinal_position
        ";
    }

    /**
     * @param string $database
     *
     * {@inheritDoc}
     */
    public function getListTablesSQL($database = null)
    {
        assert(null !== $database);

        return "
            SELECT
                DISTINCT NAME
            FROM
                SYSIBM.tables t
            WHERE
                table_type = 'BASE TABLE'
                AND t.TABLE_SCHEMA = UPPER('" . $database . "')
            ORDER BY NAME
        ";
    }

    /**
     * {@inheritDoc}
     */
    public function getListViewsSQL($database)
    {
        return "
            SELECT
              DISTINCT NAME,
              TEXT
            FROM QSYS2.sysviews v
            WHERE v.TABLE_SCHEMA = UPPER('" . $database . "')
            ORDER BY NAME
        ";
    }

    /**
     * {@inheritDoc}
     */
    public function getListTableIndexesSQL($table, $database = null)
    {
        assert(null !== $database);

        return "
            SELECT
                scc.CONSTRAINT_NAME as key_name,
                scc.COLUMN_NAME as column_name,
                CASE
                    WHEN sc.CONSTRAINT_TYPE = 'PRIMARY KEY' THEN 1
                    ELSE 0
                END AS primary,
                CASE
                    WHEN sc.CONSTRAINT_TYPE = 'UNIQUE' THEN 0
                    ELSE 1
                END AS non_unique
            FROM
            QSYS2.syscstcol scc
            LEFT JOIN QSYS2.syscst sc ON
                scc.TABLE_SCHEMA = sc.TABLE_SCHEMA AND scc.TABLE_NAME = sc.TABLE_NAME AND scc.CONSTRAINT_NAME = sc.CONSTRAINT_NAME
            WHERE scc.TABLE_NAME = UPPER('" . $table . "')
            AND scc.TABLE_SCHEMA = UPPER('" . $database . "')
        ";
    }

    /**
     * @param string|null $database
     *
     * {@inheritDoc}
     */
    public function getListTableForeignKeysSQL($table, $database = null)
    {
        assert(null !== $database);

        return "
            SELECT DISTINCT
                fk.COLUMN_NAME AS local_column,
                pk.TABLE_NAME AS foreign_table,
                pk.COLUMN_NAME AS foreign_column,
                fk.CONSTRAINT_NAME AS index_name,
                rc.UPDATE_RULE AS on_update,
                rc.DELETE_RULE AS on_delete
            FROM QSYS2.REFERENTIAL_CONSTRAINTS rc
            LEFT JOIN QSYS2.SYSCSTCOL fk ON
                rc.CONSTRAINT_SCHEMA = fk.CONSTRAINT_SCHEMA AND
                rc.CONSTRAINT_NAME = fk.CONSTRAINT_NAME
            LEFT JOIN QSYS2.SYSCSTCOL pk ON
                rc.UNIQUE_CONSTRAINT_SCHEMA = pk.CONSTRAINT_SCHEMA AND
                rc.UNIQUE_CONSTRAINT_NAME = pk.CONSTRAINT_NAME
            WHERE fk.TABLE_NAME = UPPER('" . $table . "')
            AND fk.TABLE_SCHEMA = UPPER('" . $database . "')
        ";
    }

    /**
     * @return string
     *
     * @throws \Doctrine\DBAL\DBALException If not supported on this platform.
     */
    public function getListDatabasesSQL()
    {
        return "
            SELECT
              DISTINCT TABLE_SCHEMA
            FROM
                SYSIBM.tables t
            ORDER BY TABLE_SCHEMA
        ";
    }

    /**
     * {@inheritDoc}
     */
    public function getCreateDatabaseSQL($database)
    {
        return "CREATE COLLECTION ".$database;
    }

    /**
     * {@inheritDoc}
     *
     * @phpstan-assert int<0, max> $limit
     */
    protected function doModifyLimitQuery($query, $limit, $offset = null)
    {
        if (null === $limit) {
            return $query;
        }

        if ($limit < 0) {
            throw new Exception(sprintf(
                'Limit must be a positive integer or zero, %d given',
                $limit
            ));
        }

        if (0 === $offset && false === strpos($query, 'ORDER BY')) {
            // In cases where an offset isn't required and there's no "ORDER BY" clause,
            // we can use the much simpler "FETCH FIRST".

            return sprintf('%s FETCH FIRST %u ROWS ONLY', $query, $limit);
        }

        $query .= sprintf(' LIMIT %u', $limit);

        if ($offset > 0) {
            $query .= sprintf(' OFFSET %u', $offset);
        }

        return $query;
    }

    /**
     * {@inheritDoc}
     */
    public function getDateTimeFormatString()
    {
        return 'Y-m-d-H.i.s.u';
    }
}
