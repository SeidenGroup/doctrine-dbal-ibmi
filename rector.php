<?php

declare(strict_types=1);

/*
 * This file is part of the Doctrine Behavioral Extensions package.
 * (c) Gediminas Morkevicius <gediminas.morkevicius@gmail.com> http://www.gediminasm.org
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Rector\Config\RectorConfig;
use Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__.'/src',
        __DIR__.'/tests',
        __DIR__.'/.php-cs-fixer.dist.php',
        __DIR__.'/rector.php',
    ]);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_71,
    ]);

    $rectorConfig->skip([
        RemoveExtraParametersRector::class => [
            __DIR__.'/src/Schema/DB2IBMiSchemaManager.php',
        ],
    ]);
    $rectorConfig->importNames();
    $rectorConfig->disableImportShortClasses();
};
