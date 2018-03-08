<?php

declare(strict_types=1);

/*
 * This file is part of the Unicode Normalization project.
 *
 * (c) Stephan Jorek <stephan.jorek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sjorek\UnicodeNormalization\Tests\Helper;

use Sjorek\UnicodeNormalization\Filesystem\Filesystem;
use Sjorek\UnicodeNormalization\Tests\Helper\Conformance\NormalizationTestReader;
use Sjorek\UnicodeNormalization\Tests\Helper\Conformance\NormalizationTestUpdater;
use Sjorek\UnicodeNormalization\Tests\Helper\Conformance\NormalizationTestWriter;

/**
 * Utility functions dealing with "NormalizationTest.X.Y.Z.txt" fixture files.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class NormalizationTestHandler
{
    /**
     * @var string
     */
    const TEST_URL_TEMPLATE = 'https://www.unicode.org/Public/%s/ucd/NormalizationTest.txt';

    /**
     * @var string
     */
    const TEST_FILE_TEMPLATE = 'tests/UnicodeNormalization/Tests/Fixtures/NormalizationTest.%s.txt.gz';

    /**
     * @var string
     */
    const TEST_FILE_HEADER = <<<'EOT'
# -----------------------------------------------------------------------------
# Generator : sjorek/unicode-normalization
# Source    : %source
# Info      : This file is generated, do not change it please ;-)
# -----------------------------------------------------------------------------

EOT;

    /**
     * @var string
     */
    const UPDATE_CHECK_URL = 'https://www.unicode.org/Public/UCD/latest/ReadMe.txt';

    /**
     * @var string
     */
    const UPDATE_CHECK_VERSION_PATTERN =
        '/'
        . '(?:final\s+data\s+files\s+for\s+Version\s+)'
        . '(?P<version>[0-9]+\.[0-9]+\.[0-9]+)'
        . '(?:\s+of\s+the\s+Unicode\s+Standard)'
        . '/umisU';

    /**
     * @var string
     */
    const UPDATE_CHECK_VERSION_LATEST = '10.0.0';

    /**
     * @var string
     */
    const UPDATE_CHECK_VERSION_TIMESTAMP_FILE = '.unicode-normalization/version-check-timestamp';

    /**
     * Check once per week: 60s * 60min * 24h * 7d.
     *
     * @var int
     */
    const UPDATE_CHECK_VERSION_INTERVAL = 604800;

    /**
     * @param string $version
     *
     * @return NormalizationTestUpdater
     */
    public static function createUpdater($version)
    {
        return new NormalizationTestUpdater($version);
    }

    /**
     * @param string $version
     * @param string $source
     *
     * @return NormalizationTestWriter
     */
    public static function createWriter($version, $source)
    {
        $writer = new NormalizationTestWriter($version);
        $writer->add(str_replace(['%source'], [$source], self::TEST_FILE_HEADER));

        return $writer;
    }

    /**
     * @param string $version
     *
     * @return NormalizationTestReader
     */
    public static function createReader($version)
    {
        return new NormalizationTestReader($version);
    }

    /**
     * @param string $version
     *
     * @return string
     */
    public static function createDownloadUrl($version)
    {
        return sprintf(self::TEST_URL_TEMPLATE, $version);
    }

    /**
     * @param string $version
     *
     * @return string
     */
    public static function createFilePath($version)
    {
        return sprintf(static::TEST_FILE_TEMPLATE, $version);
    }

    /**
     * @param string $filePath
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public static function applyGzip($filePath)
    {
        if ('.gz' === strtolower(substr($filePath, -3))) {
            if (in_array('compress.zlib', stream_get_wrappers(), true)) {
                $filePath = 'compress.zlib://' . $filePath;
            } else {
                throw new \RuntimeException('Gzip compression is not supported.');
            }
        }

        return $filePath;
    }

    /**
     * @throws \RuntimeException
     *
     * @return string
     */
    public static function detectLatestVersionOnline()
    {
        $content = file_get_contents(self::UPDATE_CHECK_URL);
        if (false === $content) {
            throw new \RuntimeException(
                sprintf('Could not fetch version check url: %s', self::UPDATE_CHECK_URL)
            );
        }

        $matches = null;
        if (!preg_match(self::UPDATE_CHECK_VERSION_PATTERN, $content, $matches)) {
            throw new \RuntimeException(
                sprintf('Could not determine version from url: %s', self::UPDATE_CHECK_URL)
            );
        }

        return $matches['version'];
    }

    /**
     * @param bool $force
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public static function detectLatestVersion($force = false)
    {
        $timestampFile = self::UPDATE_CHECK_VERSION_TIMESTAMP_FILE;
        if (true === $force || !file_exists($timestampFile)) {
            if ('.' !== ($dir = dirname($timestampFile))) {
                $fs = new Filesystem();
                $fs->mkdir($dir);
            }
            $version = self::detectLatestVersionOnline();
            $content = sprintf('|%s|%u|%s' . PHP_EOL, $version, time(), date(DATE_RFC2822));
            if (false === file_put_contents($timestampFile, $content)) {
                throw new \RuntimeException(
                    sprintf('Could write version check timestamp-file: %s', $timestampFile)
                );
            }

            return $version;
        }
        $content = file_get_contents($timestampFile);
        if (false === $content) {
            throw new \RuntimeException(
                sprintf('Could read version check timestamp-file: %s', $timestampFile)
            );
        }
        $major = $minor = $patch = $timestamp = null;
        if (4 !== sscanf($content, '|%u.%u.%u|%u|', $major, $minor, $patch, $timestamp)) {
            throw new \RuntimeException(
                sprintf('Invalid format of timestamp-file content: %s', $timestampFile)
            );
        }
        if (($timestamp + self::UPDATE_CHECK_VERSION_INTERVAL) < time()) {
            return self::detectLatestVersion(true);
        }

        return sprintf('%u.%u.%u', $major, $minor, $patch);
    }
}
