<?php

$finder = PhpCsFixer\Finder::create()
    ->in('.')
    ->name('/^artisan$/')
    ->exclude([
        '.git',
        'bootstrap',
        'storage',
        'tools/php-cs-fixer/vendor',
        'vendor',
    ]);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PhpCsFixer' => true,
        '@PHP80Migration' => true,
        /**
         * @see https://cs.symfony.com/doc/rules/import/fully_qualified_strict_types.html
         */
        'fully_qualified_strict_types' => [
            'phpdoc_tags' => [
                'param',
                'phpstan-param',
                'phpstan-property',
                'phpstan-property-read',
                'phpstan-property-write',
                'phpstan-return',
                'phpstan-var',
                'property',
                'property-read',
                'property-write',
                'psalm-param',
                'psalm-property',
                'psalm-property-read',
                'psalm-property-write',
                'psalm-return',
                'psalm-var',
                'return',
                // XXX: @see requires fully qualified strict types in VSCode 1.92.x
                // 'see',
                'throws',
                'var',
            ],
        ],
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'case',
                'constant',
                'property',
                'construct',
                'destruct',
            ],
        ],
        'phpdoc_align' => false,
        'phpdoc_separation' => false,
        'phpdoc_summary' => false,
        'phpdoc_to_comment' => [
            'ignored_tags' => [
                'see',
                'var',
            ],
        ],
        'php_unit_internal_class' => false,
        'php_unit_method_casing' => false,
        'php_unit_test_class_requires_covers' => false,
        'yoda_style' => [
            'equal' => false,
            'identical' => false,
            'less_and_greater' => false,
        ],
    ])
    ->setFinder($finder);
