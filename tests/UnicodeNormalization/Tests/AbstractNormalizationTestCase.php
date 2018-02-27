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
     * @var array
     */
    protected static $unicodeVersion = null;

    /**
     * @var \Sjorek\UnicodeNormalization\Implementation\NormalizerInterface
     */
    protected $subject;

    /**
     * {@inheritdoc}
     *
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp()
    {
        self::$unicodeVersion = Normalizer::getUnicodeVersion();
        $this->subject = new Normalizer();
    }

    /**
     * @return array
     */
    public function provideConformanceTestData()
    {
        $data = [];
        foreach (ConfigurationUtility::getFixtureUnicodeVersions() as $version) {
            foreach (['NFC', 'NFD', 'NFKC', 'NFKD', 'NFD_MAC'] as $form) {
                $caption = sprintf('unicode version %s with normalization form %s', $version, $form);
                $data[$caption] = [
                    $version,
                    NormalizationUtility::parseForm($form),
                    NormalizationTestUtility::createReader($version),
                ];
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
    protected function getConformanceTestIterator($unicodeVersion, $form, $lineNumber, $comment, array $codes)
    {
        // NFD_MAC is sometimes lossy and can't be converted back to other forms
        $validForMac = 0 === preg_match('/EFBFBD/i', bin2hex($codes[5]));

        if (Normalizer::NFC === $form) {
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
                //} else {
            //    yield sprintf($message, '6 (NFD_MAC)') => [$this->subject->normalize($codes[5], $form), $codes[5]];
            }
        }

        if (Normalizer::NFD === $form) {
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
                // } else {
            //    yield sprintf($message, '6 (NFD_MAC)') => [$this->subject->normalize($codes[5], $form), $codes[5]];
            }
        }

        if (Normalizer::NFKC === $form) {
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
                // } else {
            //    yield sprintf($message, '6 (NFD_MAC)') => [$this->subject->normalize($codes[5], $form), $codes[5]];
            }
        }

        if (Normalizer::NFKD === $form) {
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
                // } else {
            //    yield sprintf($message, '6 (NFD_MAC)') => [$this->subject->normalize($codes[5], $form), $codes[5]];
            }
        }

        if (Normalizer::NFD_MAC === $form) {
            $message = sprintf(
                'Normalize to NFD_MAC for version %s line %s codepoint %%s: %s',
                $unicodeVersion, $lineNumber, $comment
            );
            yield sprintf($message, '1 (RAW)') => [$codes[5], $codes[0]];
            yield sprintf($message, '2 (NFC)') => [$codes[5], $codes[1]];
            yield sprintf($message, '3 (NFD)') => [$codes[5], $codes[2]];
            if ($validForMac) {
                yield sprintf($message, '4 (NFKC)') => [$codes[4], $codes[3]];
                yield sprintf($message, '5 (NFKD)') => [$codes[4], $codes[4]];
                // } else {
            //    yield sprintf($message, '4 (NFKC)') => [$this->subject->normalize($codes[3], $form), $codes[3]];
            //    yield sprintf($message, '5 (NFKD)') => [$this->subject->normalize($codes[4], $form), $codes[4]];
            }
            yield sprintf($message, '6 (NFD_MAC)') => [$codes[5], $codes[5]];
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

    protected function markTestSkippedIfNfdMacIsNotSupported($form)
    {
        $form = NormalizationUtility::parseForm($form);
        if (Normalizer::NFD_MAC === $form && !in_array($form, Normalizer::getNormalizationForms(), true)) {
            $this->markTestSkipped(
                'Skipped test as "iconv" extension is either not available '
                . 'or not able to handle "utf-8-mac" charset.'
            );
        }
    }
}
