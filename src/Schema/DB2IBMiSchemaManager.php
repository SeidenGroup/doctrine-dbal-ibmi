<?php

/*
 * This file is part of the doctrine-dbal-ibmi package.
 * Copyright (c) 2016 Alan Seiden Consulting LLC, James Titcumb
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DoctrineDbalIbmi\Schema;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\DB2SchemaManager;
use Doctrine\DBAL\Types\Type;
use DoctrineDbalIbmi\Platform\DB2IBMiPlatform;

/**
 * @property DB2IBMiPlatform $_platform
 */
class DB2IBMiSchemaManager extends DB2SchemaManager
{
    /**
     * {@inheritdoc}
     */
    public function listTableNames()
    {
        $sql = $this->_platform->getListTablesSQL($this->_conn->getDatabase());

        $tables = $this->_conn->fetchAllAssociative($sql);
        /** @var string[] $tableNames */
        $tableNames = $this->filterAssetNames($this->_getPortableTablesList($tables));

        return $tableNames;
    }

    /**
     * {@inheritdoc}
     */
    protected function _getPortableTableColumnDefinition($tableColumn)
    {
        $tableColumn = array_change_key_case($tableColumn, \CASE_LOWER);

        $length = null;
        $fixed = false;
        $unsigned = false;
        $scale = false;
        $precision = false;

        $default = null;

        if ('NULL' !== $tableColumn['default'] && is_string($tableColumn['default'])) {
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
        }

        $options = [
            'length' => $length,
            'unsigned' => $unsigned,
            'fixed' => $fixed,
            'default' => $default,
            'autoincrement' => (bool) $tableColumn['autoincrement'],
            'notnull' => 'N' === $tableColumn['nulls'],
            'scale' => null,
            'precision' => null,
            'platformOptions' => [],
        ];

        if (null !== $scale && null !== $precision) {
            $options['scale'] = $scale;
            $options['precision'] = $precision;
        }

        return new Column($tableColumn['colname'], Type::getType($type), $options);
    }

    /**
     * Returns database name
     *
     * @deprecated to be removed in the next major version, use `Connection::getDatabase()` instead
     *
     * @return string
     */
    protected function getDatabase()
    {
        return $this->_conn->getDatabase();
    }
}
