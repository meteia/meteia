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
        'ordered_imports' => ['sort_algorithm' => 'alpha', 'imports_order' => ['const', 'class', 'function']],
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
        'yoda_style' => false,
        'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments', 'parameters']],
        'concat_space' => ['spacing' => 'one'],
        'phpdoc_summary' => false,
        'increment_style' => false,

        // green-field
        'strict_comparison' => true,
        'declare_strict_types' => true,
        'strict_param' => true,
    ])
    ->setFinder($finder);
