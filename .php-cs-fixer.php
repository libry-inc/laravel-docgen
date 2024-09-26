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
