<?php

$header = <<<EOF
This file is part of the Unicode Normalization project.

(c) Stephan Jorek <stephan.jorek@gmail.com>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

$finder = PhpCsFixer\Finder::create()
    ->exclude('Fixtures')
    ->exclude('vendor')
    ->in(__DIR__ . DIRECTORY_SEPARATOR . 'build')
    ->in(__DIR__ . DIRECTORY_SEPARATOR . 'src')
    ->in(__DIR__ . DIRECTORY_SEPARATOR . 'tests')
;

return PhpCsFixer\Config::create()
    ->setUsingCache(true)
    //->setUsingLinter(false)
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR2' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHP70Migration' => true,
        '@PHP70Migration:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'cast_spaces' => ['space' => 'single'],
        'concat_space' => ['spacing' => 'one'],
        'header_comment' => ['header' => $header],
        'no_superfluous_elseif' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'not_operator_with_successor_space' => false,
        'ordered_imports' => true,
        'php_unit_dedicate_assert' => true,
        'php_unit_strict' => true,
        'phpdoc_add_missing_param_annotation' => true,
        'phpdoc_order' => true,
        'phpdoc_types_order' => true,
        'psr0' => true,
    ])
    ->setFinder($finder)
;

/*
This document has been generated with
https://mlocati.github.io/php-cs-fixer-configurator/
you can change this configuration by importing this YAML code:

fixerSets:
  - '@PSR2'
  - '@Symfony'
  - '@Symfony:risky'
  - '@PHP70Migration'
  - '@PHP70Migration:risky'
risky: true
fixers:
  array_syntax:
    syntax: short
  cast_spaces:
    space: single
  concat_space:
    spacing: one
  header_comment:
    header: |
      This file is part of the Unicode Normalization project.

      (c) Stephan Jorek <stephan.jorek@gmail.com>

      For the full copyright and license information, please view the LICENSE
      file that was distributed with this source code.
  no_superfluous_elseif: true
  no_useless_else: true
  no_useless_return: true
  not_operator_with_successor_space: false
  ordered_imports: true
  php_unit_dedicate_assert: true
  php_unit_strict: true
  phpdoc_add_missing_param_annotation: true
  phpdoc_order: true
  phpdoc_types_order: true
  psr0: true

*/