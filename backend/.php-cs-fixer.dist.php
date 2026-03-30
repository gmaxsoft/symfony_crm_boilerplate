<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([__DIR__ . '/src', __DIR__ . '/tests'])
    ->name('*.php')
    ->notPath('var/')
    ->notPath('vendor/');

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony'                          => true,
        '@Symfony:risky'                    => true,
        '@PHP83Migration'                   => true,

        // Imports
        'ordered_imports'                   => ['sort_algorithm' => 'alpha'],
        'no_unused_imports'                 => true,
        'global_namespace_import'           => ['import_classes' => false, 'import_functions' => false],

        // Arrays
        'array_syntax'                      => ['syntax' => 'short'],
        'trailing_comma_in_multiline'       => ['elements' => ['arrays', 'parameters', 'arguments']],

        // Strings
        'single_quote'                      => true,
        'explicit_string_variable'          => true,

        // Classes / methods
        'declare_strict_types'              => true,
        'final_class'                       => false,
        'visibility_required'               => ['elements' => ['property', 'method', 'const']],
        'ordered_class_elements'            => [
            'order' => [
                'use_trait', 'case', 'constant_public', 'constant_protected', 'constant_private',
                'property_public', 'property_protected', 'property_private',
                'construct', 'destruct', 'magic', 'phpunit', 'method_public', 'method_protected', 'method_private',
            ],
        ],

        // PHPDoc
        'phpdoc_align'                      => false,
        'phpdoc_summary'                    => false,
        'phpdoc_to_comment'                 => false,

        // Misc
        'concat_space'                      => ['spacing' => 'one'],
        'yoda_style'                        => false,
        'increment_style'                   => ['style' => 'post'],
        'not_operator_with_successor_space' => false,
        'blank_line_before_statement'       => [
            'statements' => ['return', 'throw', 'try'],
        ],
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__ . '/.php-cs-fixer.cache');
