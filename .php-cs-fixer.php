<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

require_once implode(DIRECTORY_SEPARATOR, [__DIR__, 'tools', 'PrettierPHPFixer.php']);

$finder = Finder::create()
    ->ignoreDotFiles(false)
    ->ignoreVCSIgnored(true)
    ->in(__DIR__)
;
$prettierPhpFixer = new PrettierPHPFixer();
$config = new Config();

return $config
    ->registerCustomFixers([
        $prettierPhpFixer,
    ])
    ->setRiskyAllowed(true)
    ->setRules([
        '@PHP80Migration:risky' => true,
        '@PHP83Migration' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,

        'concat_space' => ['spacing' => 'one'],
        'increment_style' => false,
        'single_line_empty_body' => false,
        'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments', 'match', 'parameters']],
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
    ])
    ->setFinder($finder)
;
