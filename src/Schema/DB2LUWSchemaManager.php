<?php

namespace DoctrineDbalIbmi\Schema;

use Doctrine\DBAL\Schema\DB2SchemaManager;

class DB2LUWSchemaManager extends DB2SchemaManager
{
    protected function _getPortableTableColumnDefinition($tableColumn)
    {
        $columnDefinition = parent::_getPortableTableColumnDefinition($tableColumn);

        if ($columnDefinition->getNotnull() === true && empty($columnDefinition->getDefault())) {
            $columnDefinition->setDefault(null);
        }

        return $columnDefinition;
    }
}
