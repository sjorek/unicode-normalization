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

namespace Sjorek\UnicodeNormalization\Tests;

use Sjorek\UnicodeNormalization\NormalizationUtility;
use Sjorek\UnicodeNormalization\Normalizer;
use Sjorek\UnicodeNormalization\Tests\Utility\ConfigurationUtility;
use Sjorek\UnicodeNormalization\Tests\Utility\NormalizationTestUtility;

/**
 * Base test case class for all unit-tests.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class AbstractNormalizationTestCase extends AbstractTestCase
{
    /**
     * @var string
     */
    const IMPLEMENTATION_CLASS = 'Normalizer';

    /**
     * @var array
     */
    protected static $unicodeVersion = null;

    /**
     * @var \Sjorek\UnicodeNormalization\Implementation\NormalizerInterface
     */
    protected $subject;

    /**
     * This method will be called before any dataProvider continues its setUp.
     * Override as needed.
     */
    protected function setUpDataProvider()
    {
        $this->setUpCommon();
    }

    /**
     * {@inheritdoc}
     *
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp()
    {
        $this->markTestSkippedIfImplementationIsUnavailable();
        $this->setUpCommon();
        $this->subject = new Normalizer();
    }

    /**
     * Setup common stuff. Called by setUpDataProvider and setUp.
     * Override as needed.
     */
    protected function setUpCommon()
    {
        self::$unicodeVersion = Normalizer::getUnicodeVersion();
    }

    /**
     * @return array
     */
    public function provideConformanceTestData()
    {
        static $iterators;
        $this->setUpDataProvider();
        $data = [];
        $versions = ConfigurationUtility::getFixtureUnicodeVersions();
        $forms = [Normalizer::NFC, Normalizer::NFD, Normalizer::NFKC, Normalizer::NFKD, Normalizer::NFD_MAC];
        foreach ($forms as $form) {
            foreach ($versions as $version) {
                $caption = 'unicode version %s with normalization form %s (%s)';
                switch ($form) {
                    case Normalizer::NFC:
                        $caption = sprintf($caption, $version, $form, 'NFC');
                        break;
                    case Normalizer::NFD:
                        $caption = sprintf($caption, $version, $form, 'NFD');
                        break;
                    case Normalizer::NFKC:
                        $caption = sprintf($caption, $version, $form, 'NFKC');
                        break;
                    case Normalizer::NFKD:
                        $caption = sprintf($caption, $version, $form, 'NFKD');
                        break;
                    case Normalizer::NFD_MAC:
                        $caption = sprintf($caption, $version, $form, 'NFD_MAC');
                        break;
                }
                if (!isset($iterators[$version])) {
                    $iterators[$version] = NormalizationTestUtility::createReader($version);
                }
                $data[$caption] = [$version, $form, $iterators[$version]];
            }
        }
        return $data;
    }

    /**
     * @param string $unicodeVersion
     * @param int    $form
     * @param int    $lineNumber
     * @param string $comment
     * @param array  $codes
     *
     * @return \Generator
     */
    protected function getConformanceTestIterator(
        $unicodeVersion, $form, $lineNumber, $comment, array $codes)
    {
        // $f_NONE = Normalizer::NONE;
        $f_NFC = Normalizer::NFC;
        $f_NFD = Normalizer::NFD;
        $f_NFKC = Normalizer::NFKC;
        $f_NFKD = Normalizer::NFKD;
        $f_MAC = Normalizer::NFD_MAC;

        $validForMac = preg_match('/EFBFBD/', bin2hex($codes[5])) === 0;

        if ($form === $f_NFC) {
            $message = sprintf(
                'Normalize to NFC for version %s line %s codepoint %%s: %s',
                $unicodeVersion, $lineNumber, $comment
            );
            yield sprintf($message, '1 (RAW)') => [$codes[1], $codes[0]];
            yield sprintf($message, '2 (NFC)') => [$codes[1], $codes[1]];
            yield sprintf($message, '3 (NFD)') => [$codes[1], $codes[2]];
            yield sprintf($message, '4 (NFKC)') => [$codes[3], $codes[3]];
            yield sprintf($message, '5 (NFKD)') => [$codes[3], $codes[4]];
            if ($validForMac) {
                yield sprintf($message, '6 (NFD_MAC)') => [$codes[1], $codes[5]];
            }
        }

        if ($form === $f_NFD) {
            $message = sprintf(
                'Normalize to NFD for version %s line %s codepoint %%s: %s',
                $unicodeVersion, $lineNumber, $comment
            );
            yield sprintf($message, '1 (RAW)') => [$codes[2], $codes[0]];
            yield sprintf($message, '2 (NFC)') => [$codes[2], $codes[1]];
            yield sprintf($message, '3 (NFD)') => [$codes[2], $codes[2]];
            yield sprintf($message, '4 (NFKC)') => [$codes[4], $codes[3]];
            yield sprintf($message, '5 (NFKD)') => [$codes[4], $codes[4]];
            if ($validForMac) {
                yield sprintf($message, '6 (NFD_MAC)') => [$codes[2], $codes[5]];
            }
        }

        if ($form === $f_NFKC) {
            $message = sprintf(
                'Normalize to NFKC for version %s line %s codepoint %%s: %s',
                $unicodeVersion, $lineNumber, $comment
            );
            yield sprintf($message, '1 (RAW)') => [$codes[3], $codes[0]];
            yield sprintf($message, '2 (NFC)') => [$codes[3], $codes[1]];
            yield sprintf($message, '3 (NFD)') => [$codes[3], $codes[2]];
            yield sprintf($message, '4 (NFKC)') => [$codes[3], $codes[3]];
            yield sprintf($message, '5 (NFKD)') => [$codes[3], $codes[4]];
            if ($validForMac) {
                yield sprintf($message, '6 (NFD_MAC)') => [$codes[3], $codes[5]];
            }
        }

        if ($form === $f_NFKD) {
            $message = sprintf(
                'Normalize to NFKD for version %s line %s codepoint %%s: %s',
                $unicodeVersion, $lineNumber, $comment
            );
            yield sprintf($message, '1 (RAW)') => [$codes[4], $codes[0]];
            yield sprintf($message, '2 (NFC)') => [$codes[4], $codes[1]];
            yield sprintf($message, '3 (NFD)') => [$codes[4], $codes[2]];
            yield sprintf($message, '4 (NFKC)') => [$codes[4], $codes[3]];
            yield sprintf($message, '5 (NFKD)') => [$codes[4], $codes[4]];
            if ($validForMac) {
                yield sprintf($message, '6 (NFD_MAC)') => [$codes[4], $codes[5]];
            }
        }

        if ($form === $f_MAC) {
            $message = sprintf(
                'Normalize to NFD_MAC for version %s line %s codepoint %%s: %s',
                $unicodeVersion, $lineNumber, $comment
            );
            yield sprintf($message, '1 (RAW)') => [$codes[5], $codes[0]];
            yield sprintf($message, '2 (NFC)') => [$codes[5], $codes[1]];
            yield sprintf($message, '3 (NFD)') => [$codes[5], $codes[2]];
            if ($validForMac) {
                yield sprintf($message, '4 (NFKC)') => [$codes[3], $codes[3]];
                yield sprintf($message, '5 (NFKD)') => [$codes[4], $codes[4]];
                yield sprintf($message, '6 (NFD_MAC)') => [$codes[5], $codes[5]];
            }
        }
    }

    // ////////////////////////////////////////////////////////////////
    // utility methods
    // ////////////////////////////////////////////////////////////////

    /**
     * @param string $unicodeVersion
     */
    protected function markTestSkippedIfUnicodeConformanceLevelIsInsufficient($unicodeVersion)
    {
        if (version_compare($unicodeVersion, static::$unicodeVersion, '>')) {
            $this->markTestSkipped(
                sprintf(
                    'Skipped test as unicode version %s is higher than the supported unicode conformance level %s.',
                    $unicodeVersion,
                    static::$unicodeVersion
                )
            );
        }
    }

    protected function markTestSkippedIfAppleIconvIsNotAvailable($form)
    {
        $form = NormalizationUtility::parseForm($form);
        if (Normalizer::NFD_MAC === $form && !NormalizationUtility::isNfdMacCompatible()) {
            $this->markTestSkipped(
                'Skipped test as "iconv" extension is either not available '
                . 'or not able to handle "utf-8-mac" charset.'
            );
        }
    }

    /**
     * @return bool
     */
    protected function implementationIsAvailable()
    {
        return
            class_exists(static::IMPLEMENTATION_CLASS, true) &&
            is_a('Normalizer', static::IMPLEMENTATION_CLASS, true)
        ;
    }

    protected function markTestSkippedIfImplementationIsUnavailable()
    {
        if (!$this->implementationIsAvailable()) {
            $this->markTestSkipped(
                sprintf('Skipped test, as "%s" is not available.', static::IMPLEMENTATION_CLASS)
            );
        }
    }
}
