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

if ('cli' !== PHP_SAPI) {
    die('The script must be called from command line.' . PHP_EOL);
}

if (!file_exists('composer.json')) {
    die('The script must be called project root.' . PHP_EOL);
}

if (!file_exists('vendor/autoload.php')) {
    die('Please run "php composer.phar install" once.".' . PHP_EOL);
}

require 'vendor/autoload.php';

try {
    if (in_array('-h', $argv, true) || in_array('--help', $argv, true)) {
        $usage = <<<EOT
Usage:
    php %script [-c|-h|-v] [--check|--help|--verbose] [UNICODE_VERSION...]

Examples:
    php %script
    php %script --verbose %versions
    php %script --verbose %latest
    php %script --check
    php %script --help
EOT;
        echo str_replace(
            [
                '%script',
                '%latest',
                '%versions',
            ],
            [
                $argv[0],
                Helper\NormalizationTestHandler::UPDATE_CHECK_VERSION_LATEST,
                implode(' ', Helper\ConfigurationHandler::getKnownUnicodeVersions()),
            ],
            implode(PHP_EOL, explode("\n", $usage)) . PHP_EOL
        );
        exit(0);
    }

    $verbose = in_array('-v', $argv, true) || in_array('--verbose', $argv, true);
    if ($verbose) {
        $argv = array_filter($argv, function ($arg) { return !in_array($arg, ['-v', '--verbose'], true); });
    }

    if (in_array('-c', $argv, true) || in_array('--check', $argv, true)) {
        $current = Helper\NormalizationTestHandler::UPDATE_CHECK_VERSION_LATEST;
        if ($verbose) {
            echo sprintf('Current unicode version: %s' . PHP_EOL, $current);
            echo 'Detecting latest unicode version ...' . PHP_EOL;
        }
        $latest = Helper\NormalizationTestHandler::detectLatestVersion();
        if (version_compare($current, $latest, '=')) {
            if ($verbose) {
                echo 'The current unicode version is up-to-date.' . PHP_EOL;
            }
            exit(0);
        }
        if ($verbose) {
            echo sprintf('A new unicode version has been released: %s' . PHP_EOL, $latest);
        }
        exit(1);
    }

    $versions = array_slice($argv, 1) ?: Helper\ConfigurationHandler::getKnownUnicodeVersions();

    foreach ($versions as $version) {
        echo sprintf(
            'Updating unicode normalization conformance tests for version: %s' . PHP_EOL,
            $version
        );

        $updater = Helper\NormalizationTestHandler::createUpdater($version);
        echo sprintf(
            'Fetching tests from: %s' . PHP_EOL,
            $updater->getSource()
        );

        $writer = Helper\NormalizationTestHandler::createWriter($version, $updater->getSource());
        echo sprintf(
            'Writing tests to: %s' . PHP_EOL,
            $writer->getFilePath()
        );

        $amount = 0;
        foreach ($updater as $lineNumber => $data) {
            list($line, $comment) = $data;
            if ($comment) {
                $amount += 1;
                if ($verbose) {
                    echo sprintf('Processed line %s: %s' . PHP_EOL, $lineNumber, $comment);
                }
            }
            $writer->add($line);
        }

        echo sprintf(
            'Updated %d tests in: %s' . PHP_EOL . PHP_EOL,
            $amount,
            $writer->getFilePath()
        );
    }
} catch (\Exception $e) {
    echo sprintf(
        PHP_EOL . 'An error occurred: ' . PHP_EOL . PHP_EOL . '    %s' . PHP_EOL . PHP_EOL,
        $e->getMessage()
    );
    exit(1);
}
