<?php

/*
 * This file is part of the doctrine-dbal-ibmi package.
 * Copyright (c) 2016 Alan Seiden Consulting LLC, James Titcumb
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DoctrineDbalIbmi\Tests\Functional\Platform;

use DoctrineDbalIbmi\Driver\DB2Driver;
use DoctrineDbalIbmi\Platform\DB2IBMiPlatform;
use DoctrineDbalIbmi\Tests\AbstractTestCase;

final class DB2IBMiPlatformTest extends AbstractTestCase
{
    /**
     * @return void
     *
     * @requires PHPUnit >= 7.5.0
     */
    public function testGetListDatabasesSQL()
    {
        $connection = self::getConnection(DB2Driver::class);
        $platform = $connection->getDatabasePlatform();

        static::assertInstanceOf(DB2IBMiPlatform::class, $platform);

        $iterator = $connection
            ->executeQuery(
                $platform->getListDatabasesSQL()
            )
            ->iterateAssociative();

        static::assertInstanceOf(\Iterator::class, $iterator);

        $result = $iterator->current();

        static::assertIsArray($result);
        static::assertCount(1, $result);
        static::assertArrayHasKey('TABLE_SCHEMA', $result);
        static::assertIsString($result['TABLE_SCHEMA']);
        static::assertFalse('' === $result['TABLE_SCHEMA']);
    }
}
