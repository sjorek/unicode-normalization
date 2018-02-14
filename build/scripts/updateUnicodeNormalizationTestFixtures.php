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

namespace Sjorek\UnicodeNormalization\Tests;

$unicodeVersions = array('6.3.0', '7.0.0', '8.0.0', '9.0.0', '10.0.0');

if (php_sapi_name() !== 'cli') {
    die('Script must be called from command line.' . chr(10));
}

if ($argc !== 1) {
    die('Invalid amount of command line arguments.' . chr(10));
}

$fixturePath = dirname(dirname(__DIR__)) . '/tests/UnicodeNormalization/Tests/Fixtures';
if (!is_dir($fixturePath)) {
    die(
        sprintf(
            'Path to fixtures "%s" does not exist. Please run this script from the project root.' . chr(10),
            $fixturePath
        )
    );
}

require 'vendor/autoload.php';

UnicodeNormalizationTestUpdater::setup();

foreach ($unicodeVersions as $unicodeVersion) {
    try {
        $updater = new UnicodeNormalizationTestUpdater(
            $unicodeVersion
        );
        echo sprintf('Fetching unicode version %s from: %s', $unicodeVersion, $updater->source) . chr(10);

        $writer = new UnicodeNormalizationTestWriter(
            $unicodeVersion,
            basename(__FILE__),
            $updater->source
        );
        echo sprintf('Importing unicode version %s to %s', $unicodeVersion, $writer->filePath) . chr(10) . chr(10);

        foreach ($updater as $lineNumber => $data) {
            $line = array_shift($data);
            $comment = array_shift($data);
            if ($comment) {
                echo sprintf('Processed line %s: %s', $lineNumber, $comment) . chr(10);
            }
            $writer->add($line);
        }
        echo sprintf('Imported unicode version %s to %s', $unicodeVersion, $writer->filePath) . chr(10) . chr(10);
    } catch (\Exception $e) {
        die(sprintf('An error occurred: %s', $e->getMessage()) . chr(10));
    }
}
