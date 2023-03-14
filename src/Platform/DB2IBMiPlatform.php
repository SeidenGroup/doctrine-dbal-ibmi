<?php

namespace DoctrineDbalIbmi\Platform;

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
                ". ($database !== null ? "AND tc.TABLE_SCHEMA = UPPER('" . $database . "')" : '') ."
             ) pk ON
                c.TABLE_SCHEM = pk.TABLE_SCHEMA
                AND c.TABLE_NAME = pk.TABLE_NAME
                AND c.COLUMN_NAME = pk.COLUMN_NAME
             WHERE
                UPPER(c.TABLE_NAME) = UPPER('" . $table . "')
                ". ($database  !== null ? "AND c.TABLE_SCHEM = UPPER('" . $database . "')" : '') ."
             ORDER BY c.ordinal_position
        ";
    }

    /**
     * @param string|null $database
     *
     * {@inheritDoc}
     */
    public function getListTablesSQL($database = null)
    {
        return "
            SELECT
              DISTINCT NAME
            FROM
                SYSIBM.tables t
            WHERE
              table_type='BASE TABLE'
              ". ($database !== null ? "AND t.TABLE_SCHEMA = UPPER('" . $database . "')" : '') ."
            ORDER BY NAME
        ";
    }

    /**
     * @param string|null $database
     *
     * {@inheritDoc}
     */
    public function getListViewsSQL($database = null)
    {
        return "
            SELECT
              DISTINCT NAME,
              TEXT
            FROM QSYS2.sysviews v
            WHERE 1=1
            ". ($database !== null ? "AND v.TABLE_SCHEMA = UPPER('" . $database . "')" : '') ."
            ORDER BY NAME
        ";
    }

    /**
     * @param string|null $database
     *
     * {@inheritDoc}
     */
    public function getListTableIndexesSQL($table, $database = null)
    {
        return  "
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
            ". ($database !== null ? "AND scc.TABLE_SCHEMA = UPPER('" . $database . "')" : '') ."
        ";
    }

    /**
     * @param string|null $database
     *
     * {@inheritDoc}
     */
    public function getListTableForeignKeysSQL($table, $database = null)
    {
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
            " . ($database !== null ? "AND fk.TABLE_SCHEMA = UPPER('" . $database . "')" : '') . "
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
     */
    protected function doModifyLimitQuery($query, $limit, $offset = null)
    {
        if ($limit === null && $offset <= 0) {
            return $query;
        }

        $limit = (int) $limit;
        $offset = (int) (null !== $offset && 0 !== $offset ? $offset : 0);

        // In cases where an offset isn't required, we can use the much simpler FETCH FIRST (as the ROW_NUMBER() method
        // leaves a lot to be desired (i.e. breaks in some cases)
        if ($offset === 0) {
            return sprintf('%s FETCH FIRST %d ROWS ONLY', $query, $limit);
        }

        $orderBy = stristr($query, 'ORDER BY');

        assert(false !== $orderBy);

        $orderByBlocks = preg_split('/\s*ORDER\s+BY/', $orderBy);

        assert(false !== $orderByBlocks);

        // Reversing arrays beacause external order by is more important
        $orderByBlocks = array_reverse($orderByBlocks);

        // Splitting ORDER BY
        $orderByParts = array();
        foreach ($orderByBlocks as $orderByBlock){
            $blockArray   = explode(',', $orderByBlock);
            foreach ($blockArray as $block) {
                $block = trim($block);
                if ('' !== $block) {
                    $orderByParts[] = $block;
                }
            }
        }

        // Clear ORDER BY
        foreach ($orderByParts as &$orderByPart) {

            $orderByPart = preg_replace('/ORDER\s+BY\s+([^\)]*)(.*)/', '$1', 'ORDER BY '.$orderByPart);
        }

        $orderByColumns = array();

        // Split ORDER BY into parts
        foreach ($orderByParts as &$part) {
            if (1 === preg_match('/(([^\s]*)\.)?([^\.\s]*)\s*(ASC|DESC)?/i', trim($part), $matches)) {
                $hasTable = isset($matches[2]) && '' !== $matches[2];

                $orderByColumns[] = array(
                    'column'    => $matches[3],
                    'hasTable'  => $hasTable,
                    'sort'      => isset($matches[4]) ? $matches[4] : null,
                    'table'     => ! $hasTable ? '[^\.\s]*' : $matches[2]
                );
            }
        }

        $overColumns = [];

        // Find alias for each colum used in ORDER BY
        if ([] !== $orderByColumns) {
            foreach ($orderByColumns as $column) {
                $pattern = sprintf('/%s\.%s\s+(?:AS\s+)?([^,\s)]+)/i', $column['table'], $column['column']);

                $overColumn = 1 === preg_match($pattern, $query, $matches)
                    ? ($column['hasTable'] ? $column['table']  . '.' : '') . $column['column']
                    : $column['column'];

                if (isset($column['sort'])) {
                    $overColumn .= ' ' . $column['sort'];
                }

                $overColumns[] = $overColumn;
            }
        }

        // Replace only first occurrence of FROM with $over to prevent changing FROM also in subqueries.
        if ('' === $orderBy) {
            $over = '';
        } else {
            $over = 'ORDER BY ' . implode(', ', $overColumns);
        }

        $sql = 'SELECT DOCTRINE_TBL.* FROM (SELECT DOCTRINE_TBL1.*, ROW_NUMBER() OVER(' . $over . ') AS DOCTRINE_ROWNUM '.
            'FROM (' . $query . ') DOCTRINE_TBL1) DOCTRINE_TBL WHERE DOCTRINE_TBL.DOCTRINE_ROWNUM BETWEEN ' . ($offset+1) .' AND ' . ($offset+$limit);

        return $sql;
    }

    /**
     * {@inheritDoc}
     */
    public function getDateTimeFormatString()
    {
        return 'Y-m-d-H.i.s.u';
    }
}
