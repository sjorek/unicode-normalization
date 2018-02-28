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

use Sjorek\UnicodeNormalization\Validation\StringValidator;
use Sjorek\UnicodeNormalization\Tests\NormalizationTestCase;
use Sjorek\UnicodeNormalization\Implementation\NormalizerInterface;

/**
 * Testcase for Sjorek\UnicodeNormalization\Validation\StringValidator.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class StringValidatorTest extends NormalizationTestCase
{
    /**
     * @var StringValidator
     */
    protected $subject;

    /**
     *
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp() {
        $this->subject = new StringValidator();
    }

    // ///////////////////////////////////////
    // Tests concerning filtered utf-8 strings
    // ///////////////////////////////////////

    public function provideTestFilterData()
    {
        // é
        $iso_8859_1 = hex2bin('e9');
        // é
        $utf8_nfc = hex2bin('c3a9');
        // é
        $utf8_nfd = hex2bin('65cc81');
        // é
        $utf8_nfd_without_leading_combinator = substr($utf8_nfd, 1);
        // Test https://bugs.php.net/65732
        $bugs_65732 = "\n\r" . $utf8_nfc . "\n\r";
        // A number guaranteed to be random as specified by RFC 1149.5
        $number = '4';

        $f_NONE = NormalizerInterface::NONE;
        $f_NFC = NormalizerInterface::NFC;

        $leading_combinator = StringValidator::LEADING_COMBINATOR;
        return [
            'ISO-8859-1 string is same as UTF8-NFC string without normalization' =>
                [$utf8_nfc, $iso_8859_1, $f_NONE],
            'UTF8-NFC string is same as UTF8-NFC string without normalization' =>
                [$utf8_nfc, $utf8_nfc, $f_NONE],
            'test bug https://bugs.php.net/65732 without normalization' =>
                [$bugs_65732, $bugs_65732, $f_NONE],
            'number stays a number without normalization' =>
                [$number, $number, $f_NONE],
            'UTF8-NFD string gets a leading combinator without normalization' =>
                [
                    $leading_combinator . $utf8_nfd_without_leading_combinator,
                    $utf8_nfd_without_leading_combinator,
                    $f_NONE,
                ],
            'UTF8-NFC string is same as UTF8-NFC string for NFC normalization' =>
                [$utf8_nfc, $utf8_nfc, $f_NFC],
            'test bug https://bugs.php.net/65732 for NFC normalization' =>
                [$bugs_65732, $bugs_65732, $f_NFC],
            'number stays a number for NFC normalization' =>
                [$number, $number, $f_NFC],
            'UTF8-NFD string is same as UTF8-NFC string for NFC normalization' =>
                [$utf8_nfc, $utf8_nfd, $f_NFC],
            'UTF8-NFD string gets a leading combinator for NFC normalization' =>
                [
                    $leading_combinator . $utf8_nfd_without_leading_combinator,
                    $utf8_nfd_without_leading_combinator,
                    $f_NFC
                ],
        ];
    }

    /**
     * @dataProvider provideTestFilterData
     *
     * @param boolean $expected
     * @param string $string
     * @param integer $form
     * @param null|string $charset
     * @return void
     */
    public function testFilterUtf8String($expected, $string, $form, $charset = null)
    {
        $this->markTestSkippedIfNfdMacIsNotSupported($form);
        $actual = $this->subject->filter($string, $form, $charset);
        $this->assertSame(
            sprintf('%s (%s)', $expected, bin2hex($expected)),
            sprintf('%s (%s)', $actual, bin2hex($actual))
        );
    }

    // //////////////////////////////////////////
    // Tests concerning validity of utf-8 strings
    // //////////////////////////////////////////

    public function provideTestIsValidData()
    {
        // é
        $iso_8859_1 = hex2bin('e9');
        // é
        $utf8_nfc = hex2bin('c3a9');
        // é
        $utf8_nfd = hex2bin('65cc81');
        // é
        $utf8_nfd_without_leading_combinator = substr($utf8_nfd, 1);
        // Test https://bugs.php.net/65732
        $bugs_65732 = "\n\r" . $utf8_nfc . "\n\r";
        // A number guaranteed to be random as specified by RFC 1149.5
        $number = '4';

        $f_NONE = NormalizerInterface::NONE;
        $f_NFC = NormalizerInterface::NFC;

        return [
            'ISO-8859-1 string is not well-formed UTF8 without normalization' =>
                [false, $iso_8859_1, $f_NONE],
            'UTF8-NFC string is well-formed UTF8 string without normalization' =>
                [true, $utf8_nfc, $f_NONE],
            'test bug https://bugs.php.net/65732 without normalization' =>
                [true, $bugs_65732, $f_NONE],
            'number is well-formed UTF8 without normalization' =>
                [true, $number, $f_NONE],
            'UTF8-NFD string without leading combinator not well-formed UTF8 without normalization' =>
                [false, $utf8_nfd_without_leading_combinator, $f_NONE],
            'UTF8-NFC string is well-formed UTF8 for NFC normalization' =>
                [true, $utf8_nfc, $f_NFC, null],
            'test bug https://bugs.php.net/65732 for NFC normalization' =>
                [true, $bugs_65732, $f_NFC, null],
            'number is well-formed UTF8 for NFC normalization' =>
                [true, $number, $f_NFC, null],
            'UTF8-NFD string is not well-formed UTF8 for NFC normalization' =>
                [false, $utf8_nfd, $f_NFC, null],
            'UTF8-NFD string without leading combinator is not well-formed UTF8 for NFC normalization' =>
                [false, $utf8_nfd_without_leading_combinator, $f_NFC],
        ];
    }

    /**
     * @dataProvider provideTestIsValidData
     *
     * @param boolean $expected
     * @param string $string
     * @param integer $form
     * @param null|string $charset
     * @return void
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
