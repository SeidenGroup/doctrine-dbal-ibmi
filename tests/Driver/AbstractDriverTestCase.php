<?php

declare(strict_types=1);

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

        self::assertSame($expected, $connection->getDatabase());
    }
}
