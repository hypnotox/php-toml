<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
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
        '@Symfony'               => true,
        '@Symfony:risky'         => true,
        '@PSR12'                 => true,
        '@PSR12:risky'           => true,
        'binary_operator_spaces' => [
            'operators' => [
                '=>' => 'align',
            ],
        ],
        'single_line_throw'      => false,
        'fopen_flags'            => [
            'b_mode' => true,
        ],
        'ordered_imports'        => [
            'sort_algorithm' => 'alpha',
        ],
        'phpdoc_align'           => true,
        'phpdoc_to_comment'      => false,
        'declare_strict_types'   => true,
    ])
    ->setFinder($finder);
