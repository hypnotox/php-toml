<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->notPath('src/Parser/Token/TokenType.php')
    ->exclude(
        [
            'assets',
            'bin',
            'node_modules',
            'public',
            'templates',
            'var',
        ]
    );

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12'                                           => true,
        '@PSR12:risky'                                     => true,
        '@Symfony'                                         => true,
        '@Symfony:risky'                                   => true,
        'control_structure_continuation_position'          => true,
        'date_time_immutable'                              => true,
        'declare_parentheses'                              => true,
        'global_namespace_import'                          => ['import_classes' => true, 'import_constants' => false, 'import_functions' => false],
        'nullable_type_declaration_for_default_null_value' => ['use_nullable_type_declaration' => false],
        'self_static_accessor'                             => true,
        'simplified_if_return'                             => true,
        'simplified_null_return'                           => true,
        'static_lambda'                                    => true,
        'single_line_throw'                                => false,
        'fopen_flags'                                      => [
            'b_mode' => true,
        ],
        'ordered_imports'                                  => [
            'sort_algorithm' => 'alpha',
        ],
        'phpdoc_align'                                     => true,
        'phpdoc_to_comment'                                => false,
        'declare_strict_types'                             => true,
        'final_class'                                      => true,
    ])
    ->setFinder($finder);
