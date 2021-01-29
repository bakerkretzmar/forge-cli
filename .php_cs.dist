<?php

use PhpCsFixer\Config;
use Symfony\Component\Finder\Finder;

$finder = Finder::create()->in(__DIR__)
    ->name('*.php')
    ->exclude(['builds', 'docs', 'vendor'])
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return Config::create()
    ->setRules([
        '@PSR2' => true,
        'array_indentation' => true,
        'array_syntax' => ['syntax' => 'short'],
        'binary_operator_spaces' => [
            'default' => 'single_space',
            'operators' => ['=' => null],
        ],
        'blank_line_after_opening_tag' => true,
        'blank_line_after_namespace' => true,
        'blank_line_before_return' => true,
        'braces' => true,
        'concat_space' => ['spacing' => 'one'],
        'function_typehint_space' => true,
        'lowercase_cast' => true,
        'lowercase_static_reference' => true,
        'native_function_casing' => true,
        'no_extra_blank_lines' => true,
        'no_leading_import_slash' => true,
        'no_leading_namespace_whitespace' => true,
        'no_spaces_around_offset' => true,
        'no_unused_imports' => true,
        'no_useless_else' => true,
        'no_whitespace_in_blank_line' => true,
        'not_operator_with_successor_space' => true,
        'object_operator_without_whitespace' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'single_blank_line_before_namespace' => true,
        'single_quote' => true,
        'ternary_operator_spaces' => true,
        'trailing_comma_in_multiline_array' => true,
        'whitespace_after_comma_in_array' => true,
    ])
    ->setFinder($finder);
