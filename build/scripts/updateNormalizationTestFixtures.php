#!/usr/bin/env php
<?php

/*
 * This file is part of the Unicode Normalization project.
 *
 * (c) Stephan Jorek <stephan.jorek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sjorek\UnicodeNormalization\Conformance;

if ('cli' !== php_sapi_name()) {
    die('Script must be called from command line.' . PHP_EOL);
}

if (1 !== $argc) {
    die('Invalid amount of command line arguments.' . PHP_EOL);
}

require 'vendor/autoload.php';

$unicodeVersions = ['6.3.0', '7.0.0', '8.0.0', '9.0.0', '10.0.0'];

try {
    foreach ($unicodeVersions as $unicodeVersion) {
        $updater = new NormalizationTestUpdater(
            $unicodeVersion
        );
        echo sprintf('Fetching unicode version %s from: %s', $unicodeVersion, $updater->source) . PHP_EOL;

        $writer = new NormalizationTestWriter(
            $unicodeVersion,
            'sjorek/unicode-normalization',
            $updater->source
        );
        echo sprintf('Importing unicode version %s to %s', $unicodeVersion, $writer->filePath) . PHP_EOL . PHP_EOL;

        foreach ($updater as $lineNumber => $data) {
            $line = array_shift($data);
            $comment = array_shift($data);
            if ($comment) {
                echo sprintf('Processed line %s: %s', $lineNumber, $comment) . PHP_EOL;
            }
            $writer->add($line);
        }
        echo sprintf('Imported unicode version %s to %s', $unicodeVersion, $writer->filePath) . PHP_EOL . PHP_EOL;
    }
} catch (\Exception $e) {
    die(sprintf('An error occurred: %s', $e->getMessage()) . PHP_EOL);
}
