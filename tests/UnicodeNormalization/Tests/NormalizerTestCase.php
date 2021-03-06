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

use Sjorek\UnicodeNormalization\Implementation\NormalizationForms;
use Sjorek\UnicodeNormalization\Tests\Helper\Conformance\NormalizationTestReader;

/**
 * Normalizer test-case.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class NormalizerTestCase extends ConformanceTestCase
{
    /**
     * @return array
     */
    public function provideTestIsNormalizedData()
    {
        static::setUpNormalizationTestCase();

        $forms = static::$normalizationForms;
        $strict = static::$isStrictImplementation;

        // déjà 훈쇼™⒜你
        $s_nfc = hex2bin('64c3a96ac3a020ed9b88ec87bce284a2e2929ce4bda0');
        $s_nfd = hex2bin('6465cc816a61cc8020e18492e185aee186abe18489e185ade284a2e2929ce4bda0');
        $s_nfkc = hex2bin('64c3a96ac3a020ed9b88ec87bc544d286129e4bda0');
        $s_nfkd = hex2bin('6465cc816a61cc8020e18492e185aee186abe18489e185ad544d286129e4bda0');
        $s_mac = hex2bin('6465cc816a61cc8020e18492e185aee186abe18489e185ade284a2e2929ce4bda0');

        $f_NONE = NormalizationForms::NONE;
        $f_NFC = NormalizationForms::NFC;
        $f_NFD = NormalizationForms::NFD;
        $f_NFKC = NormalizationForms::NFKC;
        $f_NFKD = NormalizationForms::NFKD;
        $f_MAC = NormalizationForms::NFD_MAC;

        $data = [];

        $data['Empty string is normalized for default'] = [true, '', null];
        $data['ASCII string is always normalized for default'] = [true, 'abc', null];
        if ($strict) {
            $data['NFC string is strict normalized for default'] = [true, $s_nfc, null];
        } else {
            // Loose implementations return false, to prevent false positives
            $data['NFC string is loosely not normalized for default'] = [false, $s_nfc, null];
        }

        $data['Empty string is not normalized for NONE'] = [false, '', $f_NONE];
        $data['Reserved byte 0xFF is not normalized for default'] = [false, "\xFF", null];

        if (in_array($f_NFC, $forms, true)) {
            if ($strict) {
                $data['NFC string is strict normalized for NFC'] = [true, $s_nfc, $f_NFC];
                $data['NFKC string is strict normalized for NFC'] = [true, $s_nfkc, $f_NFC];
            } else {
                // Loose implementations return false, to prevent false positives
                $data['NFC string is loosely not normalized for NFC'] = [false, $s_nfc, $f_NFC];
                $data['NFKC string is loosely not normalized for NFC'] = [false, $s_nfkc, $f_NFC];
            }

            $data['NFD string is not normalized for NFC'] = [false, $s_nfd, $f_NFC];
            $data['NFKD string is not normalized for NFC'] = [false, $s_nfkd, $f_NFC];
            $data['NFD_MAC string is not normalized for NFC'] = [false, $s_mac, $f_NFC];

            $data['Empty string is normalized for NFC'] = [true, '', $f_NFC];
        }

        if (in_array($f_NFD, $forms, true)) {
            if ($strict) {
                $data['NFD string is strict normalized for NFD'] = [true, $s_nfd, $f_NFD];
                $data['NFKD string is strict normalized for NFD'] = [true, $s_nfkd, $f_NFD];
                $data['NFD_MAC string is strict normalized for NFD'] = [true, $s_mac, $f_NFD];
            } else {
                // Loose implementations return false, to prevent false positives
                $data['NFD string is loosely not normalized for NFD'] = [false, $s_nfd, $f_NFD];
                $data['NFKD string is loosely not normalized for NFD'] = [false, $s_nfkd, $f_NFD];
                $data['NFD_MAC string is loosely not normalized for NFD'] = [false, $s_mac, $f_NFD];
            }

            $data['NFC string is not normalized for NFD'] = [false, $s_nfc, $f_NFD];
            $data['NFKC string is not normalized for NFD'] = [false, $s_nfkc, $f_NFD];

            $data['Empty string is normalized for NFD'] = [true, '', $f_NFD];
        }

        if (in_array($f_NFKC, $forms, true)) {
            if ($strict) {
                $data['NFKC string is strict normalized for NFKC'] = [true, $s_nfkc, $f_NFKC];
            } else {
                // Loose implementations return false, to prevent false positives
                $data['NFKC string is loosely not normalized for NFKC'] = [false, $s_nfkc, $f_NFKC];
            }
            $data['NFC string is not normalized for NFKC'] = [false, $s_nfc, $f_NFKC];
            $data['NFD string is not normalized for NFKC'] = [false, $s_nfd, $f_NFKC];
            $data['NFKD string is not normalized for NFKC'] = [false, $s_nfkd, $f_NFKC];
            $data['NFD_MAC string is not normalized for NFKC'] = [false, $s_mac, $f_NFKC];

            $data['Empty string is normalized for NFKC'] = [true, '', $f_NFKC];
        }

        if (in_array($f_NFKD, $forms, true)) {
            if ($strict) {
                $data['NFKD string is strict normalized for NFKD'] = [true, $s_nfkd, $f_NFKD];
            } else {
                // Loose implementations return false, to prevent false positives
                $data['NFKD string is loosely not normalized for NFKD'] = [false, $s_nfkd, $f_NFKD];
            }
            $data['NFC string is not normalized for NFKD'] = [false, $s_nfc, $f_NFKD];
            $data['NFD string is not normalized for NFKD'] = [false, $s_nfd, $f_NFKD];
            $data['NFKC string is not normalized for NFKD'] = [false, $s_nfkc, $f_NFKD];
            $data['NFD_MAC string is not normalized for NFKD'] = [false, $s_mac, $f_NFKD];

            $data['Empty string is normalized for NFKD'] = [true, '', $f_NFKD];
        }

        if (in_array($f_MAC, $forms, true)) {
            if ($strict) {
                $data['NFD_MAC string is strict normalized for NFD_MAC'] = [true, $s_mac, $f_MAC];
                $data['NFD string is strict normalized for NFD_MAC'] = [true, $s_nfd, $f_MAC];
                $data['NFKD string is strict normalized for NFD_MAC'] = [true, $s_nfkd, $f_MAC];
            } else {
                // Loose implementations return false, to prevent false positives
                $data['NFD_MAC string is loosely not normalized for NFD_MAC'] = [false, $s_mac, $f_MAC];
                $data['NFD string is loosely not normalized for NFD_MAC'] = [false, $s_nfd, $f_MAC];
                $data['NFKD string is loosely not normalized for NFD_MAC'] = [false, $s_nfkd, $f_MAC];
            }
            $data['NFC string is not normalized for NFD_MAC'] = [false, $s_nfc, $f_MAC];
            $data['NFKC string is not normalized for NFD_MAC'] = [false, $s_nfkc, $f_MAC];

            $data['Empty string is normalized for NFD_MAC'] = [true, '', $f_MAC];
            $data['ASCII string is always normalized for NFD_MAC'] = [true, 'abc', $f_MAC];
            $data['Reserved byte 0xFF is not normalized for NFD_MAC'] = [false, "\xFF", $f_MAC];
        }

        return $data;
    }

    /**
     * @covers ::isNormalized
     *
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::__construct
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::callIsNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::getFormArgument
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::isNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::normalize
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::callIsNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::callNormalize
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::normalize
     * @uses \Sjorek\UnicodeNormalization\Implementation\StrictNormalizer::callIsNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\StrictNormalizer::isNormalized
     * @uses \Sjorek\UnicodeNormalization\Utility\NormalizationUtility::parseForm
     * @dataProvider provideTestIsNormalizedData
     *
     * @param bool     $assert
     * @param string   $string
     * @param null|int $form
     */
    public function testIsNormalized($assert, $string, $form)
    {
        $this->markTestSkippedIfNfdMacIsNotSupported($form);
        if ($assert) {
            $this->assertTrue($this->subject->isNormalized($string, $form));
        } else {
            $this->assertFalse($this->subject->isNormalized($string, $form));
        }
    }

    /**
     * @covers ::callIsNormalized
     *
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::__construct
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::callIsNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::callIsNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::callNormalize
     * @uses \Sjorek\UnicodeNormalization\Implementation\StrictNormalizer::callIsNormalized
     * @uses \Sjorek\UnicodeNormalization\Utility\NormalizationUtility::parseForm
     * @dataProvider provideTestIsNormalizedData
     *
     * @param bool     $assert
     * @param string   $string
     * @param null|int $form
     */
    public function testCallIsNormalized($assert, $string, $form)
    {
        if (null === $form) {
            $form = NormalizationForms::NFC;
        }
        $this->markTestSkippedIfNfdMacIsNotSupported($form);
        $actual = $this->callProtectedMethod(get_class($this->subject), 'callIsNormalized', $string, $form);
        if ($assert) {
            $this->assertTrue($actual);
        } else {
            $this->assertFalse($actual);
        }
    }

    /**
     * @return array
     */
    public function provideTestNormalizeData()
    {
        static::setUpNormalizationTestCase();

        $forms = static::$normalizationForms;

        // déjà 훈쇼™⒜你
        $s_nfc = hex2bin('64c3a96ac3a020ed9b88ec87bce284a2e2929ce4bda0');
        $s_nfd = hex2bin('6465cc816a61cc8020e18492e185aee186abe18489e185ade284a2e2929ce4bda0');
        $s_nfkc = hex2bin('64c3a96ac3a020ed9b88ec87bc544d286129e4bda0');
        $s_nfkd = hex2bin('6465cc816a61cc8020e18492e185aee186abe18489e185ad544d286129e4bda0');
        $s_mac = hex2bin('6465cc816a61cc8020e18492e185aee186abe18489e185ade284a2e2929ce4bda0');

        $f_NONE = NormalizationForms::NONE;
        $f_NFC = NormalizationForms::NFC;
        $f_NFD = NormalizationForms::NFD;
        $f_NFKC = NormalizationForms::NFKC;
        $f_NFKD = NormalizationForms::NFKD;
        $f_MAC = NormalizationForms::NFD_MAC;

        $c = $s_nfc . $s_nfd . $s_nfkc . $s_nfkd;
        $c_plus_m = $c . $s_mac;

        $data = [];

        $data['Combined string is same for NONE'] = [$c, $c, $f_NONE];
        $data['Combined string plus NFD_MAC is same for NONE'] = [$c_plus_m, $c_plus_m, $f_NONE];

        $data['Empty string is same for default'] = ['', '', null];
        $data['Reserved byte 0xFF is not normalize-able for default'] = [false, "\xFF", null];

        if (in_array($f_MAC, $forms, true)) {
            $data['Empty string is same for NFD_MAC'] = ['', '', $f_MAC];
            $data['ASCI string is same for NFD_MAC'] = ['abc', 'abc', $f_MAC];
            $data['Reserved byte 0xFF is not normalize-able for NFD_MAC'] = [false, "\xFF", $f_MAC];
        }

        // if NFC and NFD are supported
        if (empty(array_diff([$f_NFC, $f_NFD], $forms))) {
            $data['NFC string is same for default with NFD string'] = [$s_nfc, $s_nfd, null];
            $data['NFC string is same for NFC with NFD string'] = [$s_nfc, $s_nfd, $f_NFC];
            $data['NFD string is same for NFD with NFC string'] = [$s_nfd, $s_nfc, $f_NFD];
            $data['Special string is same for NFC'] = ["\xcc\x83\xc3\x92\xd5\x9b", "\xcc\x83\xc3\x92\xd5\x9b", $f_NFC];
            $data['Special string is same for NFD'] = ["\xe0\xbe\xb2\xe0\xbd\xb1\xe0\xbe\x80\xe0\xbe\x80", "\xe0\xbd\xb6\xe0\xbe\x81", $f_NFD];
        }
        // if NFC and NFKC are supported
        if (empty(array_diff([$f_NFC, $f_NFKC], $forms))) {
            $data['NFKC string is same for NFKC with NFC string'] = [$s_nfkc, $s_nfc, $f_NFKC];
            $data['NFKC string is same for NFC with NFKC string'] = [$s_nfkc, $s_nfkc, $f_NFC];
        }
        // if NFC and NFKD are supported
        if (empty(array_diff([$f_NFC, $f_NFKD], $forms))) {
            $data['NFKD string is same for NFKD with NFC string'] = [$s_nfkd, $s_nfc, $f_NFKD];
            $data['NFKD string is same for NFC with NFKD string'] = [$s_nfkc, $s_nfkd, $f_NFC];
        }
        // if NFC and NFD_MAC are supported
        if (empty(array_diff([$f_NFC, $f_MAC], $forms))) {
            $data['NFD_MAC string is same for NFD_MAC with NFC string'] = [$s_mac, $s_nfc, $f_MAC];
            $data['NFC string is same for NFC with NFD_MAC string'] = [$s_nfc, $s_mac, $f_NFC];
        }
        // if NFD and NFKC are supported
        if (empty(array_diff([$f_NFD, $f_NFKC], $forms))) {
            $data['NFKC string is same for NFKC with NFD string'] = [$s_nfkc, $s_nfd, $f_NFKC];
            $data['NFKD string is same for NFD with NFKC string'] = [$s_nfkd, $s_nfkc, $f_NFD];
        }
        // if NFD and NFKD are supported
        if (empty(array_diff([$f_NFD, $f_NFKD], $forms))) {
            $data['NFKD string is same for NFKD with NFD string'] = [$s_nfkd, $s_nfd, $f_NFKD];
            $data['NFKD string is same for NFD with NFKD string'] = [$s_nfkd, $s_nfkd, $f_NFD];
        }
        // if NFKC and NFKD are supported
        if (empty(array_diff([$f_NFKC, $f_NFKD], $forms))) {
            $data['NFKD string is same for NFKD with NFKC string'] = [$s_nfkd, $s_nfkc, $f_NFKD];
            $data['NFKC string is same for NFKC with NFKD string'] = [$s_nfkc, $s_nfkd, $f_NFKC];
        }
        // if NFC, NFD, NFKC and NFKD are supported
        if (empty(array_diff([$f_NFC, $f_NFD, $f_NFKC, $f_NFKD], $forms))) {
            $data['Combined string is same for NFC'] = [$s_nfc . $s_nfc . $s_nfkc . $s_nfkc, $c, $f_NFC];
            $data['Combined string is same for NFD'] = [$s_nfd . $s_nfd . $s_nfkd . $s_nfkd, $c, $f_NFD];
            $data['Combined string is same for NFKC'] = [$s_nfkc . $s_nfkc . $s_nfkc . $s_nfkc, $c, $f_NFKC];
            $data['Combined string is same for NFKD'] = [$s_nfkd . $s_nfkd . $s_nfkd . $s_nfkd, $c, $f_NFKD];
        }
        // if NFC, NFD, NFKC, NFKD and NFD_MAC are supported
        if (empty(array_diff([$f_NFC, $f_NFD, $f_NFKC, $f_NFKD, $f_MAC], $forms))) {
            $data['Combined plus NFD_MAC string is same for NFC'] = [$s_nfc . $s_nfc . $s_nfkc . $s_nfkc . $s_nfc, $c_plus_m, $f_NFC];
            $data['Combined plus NFD_MAC string is same for NFD'] = [$s_nfd . $s_nfd . $s_nfkd . $s_nfkd . $s_nfd, $c_plus_m, $f_NFD];
            $data['Combined plus NFD_MAC string is same for NFKC'] = [$s_nfkc . $s_nfkc . $s_nfkc . $s_nfkc . $s_nfkc, $c_plus_m, $f_NFKC];
            $data['Combined plus NFD_MAC string is same for NFKD'] = [$s_nfkd . $s_nfkd . $s_nfkd . $s_nfkd . $s_nfkd, $c_plus_m, $f_NFKD];
            $data['Combined plus NFD_MAC string is same for NFD_MAC'] = [$s_mac . $s_mac . $s_nfkd . $s_nfkd . $s_mac, $c_plus_m, $f_MAC];
        }

        return $data;
    }

    /**
     * @covers ::normalize
     *
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::__construct
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::callNormalize
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::getFormArgument
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::isNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::normalize
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::callNormalize
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::isNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::normalize
     * @uses \Sjorek\UnicodeNormalization\Utility\NormalizationUtility::parseForm
     * @dataProvider provideTestNormalizeData
     *
     * @param false|string $expect
     * @param string       $string
     * @param null|int     $form
     */
    public function testNormalize($expect, $string, $form)
    {
        $this->markTestSkippedIfNfdMacIsNotSupported($form);
        if (false !== $expect) {
            $this->assertSame($expect, $this->subject->normalize($string, $form));
        } else {
            $this->assertFalse($this->subject->normalize($string, $form));
        }
    }

    /**
     * @covers ::normalizeTo
     *
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::__construct
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::callIsNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::callNormalize
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::getFormArgument
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::isNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::normalize
     * @uses \Sjorek\UnicodeNormalization\Implementation\StrictNormalizer::callIsNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\StrictNormalizer::isNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::callIsNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::callNormalize
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::isNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::normalize
     * @uses \Sjorek\UnicodeNormalization\Utility\NormalizationUtility::parseForm
     * @dataProvider provideTestNormalizeData
     *
     * @param false|string $same
     * @param string       $string
     * @param null|int     $form
     */
    public function testNormalizeTo($same, $string, $form)
    {
        $this->markTestSkippedIfNfdMacIsNotSupported($form);
        if (false !== $same) {
            $this->assertSame($same, $this->subject->normalizeTo($string, $form));
        } else {
            $this->assertFalse($this->subject->normalizeTo($string, $form));
        }
    }

    /**
     * @covers ::normalizeStringTo
     *
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::__construct
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::callIsNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::callNormalize
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::getFormArgument
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::isNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::normalize
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::normalizeTo
     * @uses \Sjorek\UnicodeNormalization\Implementation\StrictNormalizer::callIsNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\StrictNormalizer::isNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::callIsNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::callNormalize
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::isNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::normalize
     * @uses \Sjorek\UnicodeNormalization\Utility\NormalizationUtility::parseForm
     * @dataProvider provideTestNormalizeData
     *
     * @param false|string $same
     * @param string       $string
     * @param null|int     $form
     */
    public function testNormalizeStringTo($same, $string, $form)
    {
        $this->markTestSkippedIfNfdMacIsNotSupported($form);
        if (false !== $same) {
            $this->assertSame($same, $this->subject->normalizeStringTo($string, $form));
        } else {
            $this->assertFalse($this->subject->normalizeStringTo($string, $form));
        }
    }

    /**
     * @covers ::callNormalize
     *
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::__construct
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::callNormalize
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::callNormalize
     * @uses \Sjorek\UnicodeNormalization\Utility\NormalizationUtility::parseForm
     * @dataProvider provideTestNormalizeData
     *
     * @param false|string $expect
     * @param string       $string
     * @param null|int     $form
     */
    public function testCallNormalize($expect, $string, $form)
    {
        if (null === $form) {
            $form = NormalizationForms::NFC;
        }
        $this->markTestSkippedIfNfdMacIsNotSupported($form);
        $actual = $this->callProtectedMethod(get_class($this->subject), 'callNormalize', $string, $form);
        if (false !== $expect) {
            $this->assertSame($expect, $actual);
        } else {
            $this->assertFalse($actual);
        }
    }

    /**
     * @coversNothing
     * @dataProvider provideConformanceTestData
     * @large
     * @group conformance
     *
     * @param string                  $unicodeVersion
     * @param int                     $form
     * @param NormalizationTestReader $fileIterator
     */
    public function testNormalizeConformance($unicodeVersion, $form, NormalizationTestReader $fileIterator)
    {
        $this->markTestSkippedIfUnicodeConformanceLevelIsInsufficient($unicodeVersion);
        $this->markTestSkippedIfNfdMacIsNotSupported($form);

        foreach ($fileIterator as $lineNumber => $data) {
            list($comment, $codes) = $data;
            $testIterator = $this->getConformanceTestIterator(
                $unicodeVersion, $form, $lineNumber, $comment, $codes
            );
            foreach ($testIterator as $message => $data) {
                list($expected, $string) = $data;
                $actual = $this->subject->normalize($string, $form);
                $this->assertSame(
                    sprintf('%s (%s)', $expected, strtoupper(bin2hex($expected))),
                    sprintf('%s (%s)', $actual, strtoupper(bin2hex($actual))),
                    $message
                );
            }
        }
    }
}
