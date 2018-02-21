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

use Sjorek\UnicodeNormalization\Implementation\NormalizerInterface;
use Sjorek\UnicodeNormalization\NormalizationUtility;
use Sjorek\UnicodeNormalization\Normalizer;
use Sjorek\UnicodeNormalization\Validation\Conformance\NormalizationTestReader;

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
    const IMPLEMENTATION_CLASS = null;

    /**
     * @var array
     */
    protected static $capabilities = null;

    /**
     * @var NormalizerInterface
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
        self::$capabilities = NormalizationUtility::detectCapabilities(static::IMPLEMENTATION_CLASS);
    }

    /**
     * @return array
     */
    public function provideConformanceTestData()
    {
        static $iterators;
        $this->setUpDataProvider();
        $data = [];
        foreach (static::$capabilities['forms'] as $form) {
            if (NormalizerInterface::NONE === $form) {
                continue;
            }
            foreach (['6.3.0', '7.0.0', '8.0.0', '9.0.0', '10.0.0'] as $unicodeVersion) {
                $caption = 'unicode version %s with normalization form %s (%s)';
                switch ($form) {
                    case NormalizerInterface::NFC:
                        $caption = sprintf($caption, $unicodeVersion, $form, 'NFC');
                        break;
                    case NormalizerInterface::NFD:
                        $caption = sprintf($caption, $unicodeVersion, $form, 'NFD');
                        break;
                    case NormalizerInterface::NFKC:
                        $caption = sprintf($caption, $unicodeVersion, $form, 'NFKC');
                        break;
                    case NormalizerInterface::NFKD:
                        $caption = sprintf($caption, $unicodeVersion, $form, 'NFKD');
                        break;
                    case NormalizerInterface::NFD_MAC:
                        $caption = sprintf($caption, $unicodeVersion, $form, 'NFD_MAC');
                        break;
                }
                if (!isset($iterators[$unicodeVersion])) {
                    $iterators[$unicodeVersion] = new NormalizationTestReader($unicodeVersion);
                }
                // append an additional '.0' as the php implementation internally uses a version quadruple
                $data[$caption] = [$unicodeVersion . '.0', $form, $iterators[$unicodeVersion]];
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
        // $f_NONE = NormalizerInterface::NONE;
        $f_NFC = NormalizerInterface::NFC;
        $f_NFD = NormalizerInterface::NFD;
        $f_NFKC = NormalizerInterface::NFKC;
        $f_NFKD = NormalizerInterface::NFKD;
        $f_MAC = NormalizerInterface::NFD_MAC;

        $validForMac = preg_match('/^(EFBFBD)+$/', bin2hex($codes[5]));

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
        if (version_compare($unicodeVersion, static::$capabilities['level'], '>')) {
            $this->markTestSkipped(
                sprintf(
                    'Skipped test as unicode version %s is higher than the supported unicode conformance level %s.',
                    $unicodeVersion,
                    static::$capabilities['level']
                )
            );
        }
    }

    protected function markTestSkippedIfAppleIconvIsNotAvailable($form)
    {
        if (NormalizerInterface::NFD_MAC === NormalizationUtility::parseForm($form) &&
            !NormalizationUtility::appleIconvIsAvailable()) {
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
            null === static::IMPLEMENTATION_CLASS ||
            (
                class_exists(static::IMPLEMENTATION_CLASS, true) &&
                (
                    NormalizationUtility::registerImplementation(static::IMPLEMENTATION_CLASS) ||
                    NormalizationUtility::getImplementation() === static::IMPLEMENTATION_CLASS
                )
            )
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
