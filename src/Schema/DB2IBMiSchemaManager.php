<?php

namespace DoctrineDbalIbmi\Schema;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Type;

class DB2IBMiSchemaManager extends DB2LUWSchemaManager
{
    /**
     * {@inheritdoc}
     */
    public function listTableNames()
    {
        $sql = $this->_platform->getListTablesSQL($this->getDatabase());

        $tables = $this->_conn->fetchAll($sql);
        $tableNames = $this->_getPortableTablesList($tables);

        return $this->filterAssetNames($tableNames);
    }

    /**
     * {@inheritdoc}
     */
    public function listSequences($database = null)
    {
        if (is_null($database)) {
            $database = $this->getDatabase();
        }
        $sql = $this->_platform->getListSequencesSQL($database);

        $sequences = $this->_conn->fetchAll($sql);

        return $this->filterAssetNames($this->_getPortableSequencesList($sequences));
    }

    /**
     * {@inheritdoc}
     */
    public function listTableColumns($table, $database = null)
    {
        if ( ! $database) {
            $database = $this->getDatabase();
        }

        $sql = $this->_platform->getListTableColumnsSQL($table, $database);

        $tableColumns = $this->_conn->fetchAll($sql);

        return $this->_getPortableTableColumnList($table, $database, $tableColumns);
    }

    /**
     * {@inheritdoc}
     */
    public function listTableIndexes($table)
    {
        $sql = $this->_platform->getListTableIndexesSQL($table, $this->getDatabase());

        $tableIndexes = $this->_conn->fetchAll($sql);

        return $this->_getPortableTableIndexesList($tableIndexes, $table);
    }

    /**
     * {@inheritdoc}
     */
    public function listViews()
    {
        $database = $this->getDatabase();
        $sql = $this->_platform->getListViewsSQL($database);
        $views = $this->_conn->fetchAll($sql);

        return $this->_getPortableViewsList($views);
    }

    /**
     * {@inheritdoc}
     */
    public function listTableForeignKeys($table, $database = null)
    {
        if (is_null($database)) {
            $database = $this->getDatabase();
        }
        $sql = $this->_platform->getListTableForeignKeysSQL($table, $database);
        $tableForeignKeys = $this->_conn->fetchAll($sql);

        return $this->_getPortableTableForeignKeysList($tableForeignKeys);
    }

    /**
     * {@inheritdoc}
     */
    protected function _getPortableTableColumnDefinition($tableColumn)
    {
        $tableColumn = array_change_key_case($tableColumn, \CASE_LOWER);

        $length = null;
        $fixed = null;
        $unsigned = false;
        $scale = false;
        $precision = false;

        $default = null;

        if (null !== $tableColumn['default'] && 'NULL' != $tableColumn['default']) {
            $default = trim($tableColumn['default'], "'");
        }

        $type = $this->_platform->getDoctrineTypeMapping($tableColumn['typename']);

        $length = $tableColumn['length'];

        switch (strtolower($tableColumn['typename'])) {
            case 'smallint':
                break;
            case 'bigint':
                break;
            case 'integer':
                break;
            case 'time':
                break;
            case 'date':
                break;
            case 'string':
                $fixed = true;
                break;
            case 'binary':
                break;
            case 'text':
                break;
            case 'blob':
                break;
            case 'decimal':
                $scale = $tableColumn['scale'];
                $precision = $tableColumn['length'];
                break;
            case 'float':
                $scale = $tableColumn['scale'];
                $precision = $tableColumn['length'];
                break;
            case 'datetime':
                break;
            default:
        }

        $options = array(
            'length'        => $length,
            'unsigned'      => (bool) $unsigned,
            'fixed'         => (bool) $fixed,
            'default'       => $default,
            'autoincrement' => (boolean) $tableColumn['autoincrement'],
            'notnull'       => (bool) ($tableColumn['nulls'] == 'N'),
            'scale'         => null,
            'precision'     => null,
            'platformOptions' => array(),
        );

        if ($scale !== null && $precision !== null) {
            $options['scale'] = $scale;
            $options['precision'] = $precision;
        }

        return new Column($tableColumn['colname'], Type::getType($type), $options);
    }

    /**
     * Returns database name
     */
    protected function getDatabase()
    {
        //In iSeries systems, with SQL naming, the default database name is specified in driverOptions['i5_lib']
        $dbParams = $this->_conn->getParams();
        if (array_key_exists('driverOptions', $dbParams) && array_key_exists('i5_lib', $dbParams['driverOptions'])) {
            return $dbParams['driverOptions']['i5_lib'];
        } else {
            return null;
        }
    }
}
