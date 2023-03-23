<?php

declare(strict_types=1);

/*
 * This file is part of the doctrine-dbal-ibmi package.
 * Copyright (c) 2016 Alan Seiden Consulting LLC, James Titcumb
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DoctrineDbalIbmi\Tests\Driver;

use Doctrine\DBAL\DriverManager;
use PHPUnit\Framework\TestCase;

abstract class AbstractDriverTestCase extends TestCase
{
    /**
     * @return iterable<int|string, array<int, string|array<string, string|int|bool>>>
     *
     * @phpstan-return iterable<int|string, array{0: string, 1: array<string, string|int|bool>}>
     */
    abstract public function getDatabaseProvider(): iterable;

    /**
     * @return void
     *
     * @dataProvider getDatabaseProvider
     */
    public function testGetDatabase(string $expected, array $params)
    {
        $connection = DriverManager::getConnection($params);

        static::assertSame($expected, $connection->getDatabase());
    }
}
