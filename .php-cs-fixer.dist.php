<?php

declare(strict_types=1);

/*
 * This file is part of the doctrine-dbal-ibmi package.
 * Copyright (c) 2016 Alan Seiden Consulting LLC, James Titcumb
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$header = <<<'HEADER'
This file is part of the doctrine-dbal-ibmi package.
Copyright (c) 2016 Alan Seiden Consulting LLC, James Titcumb
For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
HEADER;

$finder = Finder::create()
    ->in([
        __DIR__.'/src',
        __DIR__.'/tests',
    ]);

return (new Config())
    ->setRules([
        '@DoctrineAnnotation' => true,
        '@PHP71Migration' => true,
        '@PHP71Migration:risky' => true,
        '@PHPUnit60Migration:risky' => true,
        '@PSR2' => true,
        '@Symfony' => true,
        'array_indentation' => true,
        'array_syntax' => ['syntax' => 'short'],
        'blank_line_before_statement' => true,
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        // @todo: Change the following rule to `true` in the next major release.
        'declare_strict_types' => false,
        'error_suppression' => true,
        'header_comment' => ['header' => $header],
        'is_null' => false,
        'list_syntax' => ['syntax' => 'short'],
        'modernize_types_casting' => true,
        'no_homoglyph_names' => true,
        'no_null_property_initialization' => true,
        'no_superfluous_elseif' => true,
        'no_unset_on_property' => true,
        'no_useless_else' => true,
        'ordered_class_elements' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'phpdoc_summary' => false,
        'phpdoc_to_comment' => false,
        'php_unit_construct' => true,
        'php_unit_dedicate_assert' => true,
        'php_unit_dedicate_assert_internal_type' => true,
        'php_unit_method_casing' => false,
        'php_unit_mock' => true,
        'php_unit_namespaced' => true,
        'php_unit_set_up_tear_down_visibility' => true,
        'php_unit_strict' => true,
        'php_unit_test_annotation' => ['style' => 'prefix'],
        'php_unit_test_case_static_method_calls' => true,
        'psr_autoloading' => true,
        'random_api_migration' => true,
        'self_accessor' => true,
        'static_lambda' => true,
        'strict_param' => true,
        'ternary_to_null_coalescing' => true,
        // @todo: Change the following rule to `true` in the next major release.
        'void_return' => false,
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setUsingCache(true);
