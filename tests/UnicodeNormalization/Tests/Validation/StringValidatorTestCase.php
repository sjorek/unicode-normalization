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

namespace Sjorek\UnicodeNormalization\Tests\Validation;

use Sjorek\UnicodeNormalization\Implementation\NormalizerInterface;

/**
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class StringValidatorTestCase extends ValidationTestCase
{
    /**
     * @var StringValidator
     */
    protected $subject;

    /**
     * {@inheritdoc}
     *
     * @see \Sjorek\UnicodeNormalization\Tests\NormalizationTestCase::setUp()
     */
    protected function setUp()
    {
        $this->subject = new StringValidator();
    }

    // ///////////////////////////////////////
    // Tests concerning filtered utf-8 strings
    // ///////////////////////////////////////

    public function provideTestFilterData()
    {
        static::setUpValidationTestCase();

        // é
        $iso_8859_1 = hex2bin('e9');
        // é
        $utf8_nfc = hex2bin('c3a9');
        // é
        $utf8_nfd = hex2bin('65cc81');
        // é
        $utf8_nfd_without_leading_combinator = substr($utf8_nfd, 1);
        // A number guaranteed to be random as specified by RFC 1149.5
        $number = '4';

        $f_NONE = NormalizerInterface::NONE;
        $f_NFC = NormalizerInterface::NFC;

        $leading_combinator = StringValidator::LEADING_COMBINATOR;

        return [
            'ISO-8859-1 string is same as UTF8-NFC string without normalization' => [
                $utf8_nfc, $iso_8859_1, $f_NONE,
            ],
            'UTF8-NFC string is same as UTF8-NFC string without normalization' => [
                $utf8_nfc, $utf8_nfc, $f_NONE,
            ],
            'number stays a number without normalization' => [
                $number, $number, $f_NONE,
            ],
            'UTF8-NFD string gets a leading combinator without normalization' => [
                $leading_combinator . $utf8_nfd_without_leading_combinator,
                $utf8_nfd_without_leading_combinator,
                $f_NONE,
            ],
            'UTF8-NFC string is same as UTF8-NFC string for NFC normalization' => [
                $utf8_nfc, $utf8_nfc, $f_NFC,
            ],
            'number stays a number for NFC normalization' => [
                $number, $number, $f_NFC,
            ],
            'UTF8-NFD string is same as UTF8-NFC string for NFC normalization' => [
                $utf8_nfc, $utf8_nfd, $f_NFC,
            ],
            'UTF8-NFD string gets a leading combinator for NFC normalization' => [
                $leading_combinator . $utf8_nfd_without_leading_combinator,
                $utf8_nfd_without_leading_combinator,
                $f_NFC,
            ],
            'invalid input returns false' => [
                false, chr(0b11111111), $f_NONE,
            ],
        ];
    }

    /**
     * @covers ::filter
     *
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::__construct
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::getFormArgument
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::isNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::normalize
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::normalizeStringTo
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::normalizeTo
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::isNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::normalize
     * @uses \Sjorek\UnicodeNormalization\Implementation\StrictNormalizer::isNormalized
     * @uses \Sjorek\UnicodeNormalization\Utility\NormalizationUtility::parseForm
     * @uses \Sjorek\UnicodeNormalization\Validation\Implementation\StringValidatorImpl::__construct
     * @uses \Sjorek\UnicodeNormalization\Validation\Implementation\StringValidatorImpl::convertStringToUtf8
     * @uses \Sjorek\UnicodeNormalization\Validation\Implementation\StringValidatorImpl::filter
     *
     * @dataProvider provideTestFilterData
     *
     * @param bool        $expected
     * @param string      $string
     * @param int         $form
     * @param null|string $charset
     */
    public function testFilter($expected, $string, $form, $charset = null)
    {
        $this->markTestSkippedIfNfdMacIsNotSupported($form);
        $actual = $this->subject->filter($string, $form, $charset);
        if (false === $expected) {
            $this->assertFalse($actual);
        } else {
            $this->assertSame(
                sprintf('%s (%s)', $expected, bin2hex($expected)),
                sprintf('%s (%s)', $actual, bin2hex($actual))
            );
        }
    }

    // //////////////////////////////////////////
    // Tests concerning validity of utf-8 strings
    // //////////////////////////////////////////

    public function provideTestIsValidData()
    {
        static::setUpValidationTestCase();

        // é
        $iso_8859_1 = hex2bin('e9');
        // é
        $utf8_nfc = hex2bin('c3a9');
        // é
        $utf8_nfd = hex2bin('65cc81');
        // é
        $utf8_nfd_without_leading_combinator = substr($utf8_nfd, 1);
        // A number guaranteed to be random as specified by RFC 1149.5
        $number = '4';

        $f_NONE = NormalizerInterface::NONE;
        $f_NFC = NormalizerInterface::NFC;

        return [
            'ISO-8859-1 string is not well-formed UTF8 without normalization' => [
                false, $iso_8859_1, $f_NONE,
            ],
            'UTF8-NFC string is well-formed UTF8 string without normalization' => [
                true, $utf8_nfc, $f_NONE,
            ],
            'number is well-formed UTF8 without normalization' => [
                true, $number, $f_NONE,
            ],
            'UTF8-NFD string without leading combinator not well-formed UTF8 without normalization' => [
                false, $utf8_nfd_without_leading_combinator, $f_NONE,
            ],
            'UTF8-NFC string is well-formed UTF8 for NFC normalization' => [
                true, $utf8_nfc, $f_NFC,
            ],
            'number is well-formed UTF8 for NFC normalization' => [
                true, $number, $f_NFC,
            ],
            'UTF8-NFD string is not well-formed UTF8 for NFC normalization' => [
                false, $utf8_nfd, $f_NFC,
            ],
            'UTF8-NFD string without leading combinator is not well-formed UTF8 for NFC normalization' => [
                false, $utf8_nfd_without_leading_combinator, $f_NFC,
            ],
        ];
    }

    /**
     * @covers ::isValid
     *
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::__construct
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::getFormArgument
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::isNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::normalizeStringTo
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::normalizeTo
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::normalize
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::isNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::normalize
     * @uses \Sjorek\UnicodeNormalization\Implementation\StrictNormalizer::isNormalized
     * @uses \Sjorek\UnicodeNormalization\Utility\NormalizationUtility::parseForm
     * @uses \Sjorek\UnicodeNormalization\Validation\Implementation\StringValidatorBugfix65732::filter
     * @uses \Sjorek\UnicodeNormalization\Validation\Implementation\StringValidatorImpl::__construct
     * @uses \Sjorek\UnicodeNormalization\Validation\Implementation\StringValidatorImpl::convertStringToUtf8
     * @uses \Sjorek\UnicodeNormalization\Validation\Implementation\StringValidatorImpl::filter
     *
     * @dataProvider provideTestIsValidData
     *
     * @param bool        $expected
     * @param string      $string
     * @param int         $form
     * @param null|string $charset
     */
    public function testIsValid($expected, $string, $form, $charset = null)
    {
        $this->markTestSkippedIfNfdMacIsNotSupported($form);
        $actual = $this->subject->isValid($string, $form, $charset);
        if ($expected) {
            $this->assertTrue($actual);
        } else {
            $this->assertFalse($actual);
        }
    }
}
