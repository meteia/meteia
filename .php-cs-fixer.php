<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->ignoreDotFiles(false)
    ->ignoreVCSIgnored(true)
    ->in(__DIR__);

$config = new Config();

return $config
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@PHP80Migration:risky' => true,
        '@PHP81Migration' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha', 'imports_order' => ['const', 'class', 'function']],
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
        'yoda_style' => false,
        'global_namespace_import' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments', 'parameters']],
        'concat_space' => ['spacing' => 'one'],
        'phpdoc_summary' => false,
        'increment_style' => false,
        'echo_tag_syntax' => ['format' => 'short', 'shorten_simple_statements_only' => false],

        // green-field
        'strict_comparison' => true,
        'declare_strict_types' => true,
        'strict_param' => true,
    ])
    ->setFinder($finder);
