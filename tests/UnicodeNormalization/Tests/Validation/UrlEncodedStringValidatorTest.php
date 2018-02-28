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
use Sjorek\UnicodeNormalization\Tests\NormalizationTestCase;
use Sjorek\UnicodeNormalization\Validation\StringValidator;
use Sjorek\UnicodeNormalization\Validation\UrlEncodedStringValidator;

/**
 * Testcase for Sjorek\UnicodeNormalization\Validation\UrlEncodedStringValidator.
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class UrlEncodedStringValidatorTest extends NormalizationTestCase
{
    /**
     * @var UrlEncodedStringValidator
     */
    protected $subject;

    /**
     * {@inheritdoc}
     *
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp()
    {
        $this->subject = new UrlEncodedStringValidator();
    }

    // ////////////////////////////////////
    // Tests concerning filtered utf-8 urls
    // ////////////////////////////////////

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

        $f_NONE = NormalizerInterface::NONE;
        $f_NFC = NormalizerInterface::NFC;

        $leading_combinator = StringValidator::LEADING_COMBINATOR;

        return [
            'url-encoded ISO-8859-1 uri is same as UTF8-NFC uri without normalization' => [
                urlencode($utf8_nfc), urlencode($iso_8859_1), $f_NONE,
            ],
            'raw ISO-8859-1 uri is same as UTF8-NFC uri without normalization' => [
                urlencode($utf8_nfc), $iso_8859_1, $f_NONE,
            ],
            'url-encoded UTF8-NFC uri is same as UTF8-NFC uri without normalization' => [
                urlencode($utf8_nfc), urlencode($utf8_nfc), $f_NONE,
            ],
            'raw UTF8-NFC uri is same as UTF8-NFC uri without normalization' => [
                urlencode($utf8_nfc), $utf8_nfc, $f_NONE,
            ],
            'url-encoded UTF8-NFD uri gets a leading combinator without normalization' => [
                urlencode($leading_combinator . $utf8_nfd_without_leading_combinator),
                urlencode($utf8_nfd_without_leading_combinator),
                $f_NONE,
            ],
            'raw UTF8-NFD uri gets a leading combinator without normalization' => [
                urlencode($leading_combinator . $utf8_nfd_without_leading_combinator),
                $utf8_nfd_without_leading_combinator,
                $f_NONE,
            ],
            'url-encoded UTF8-NFC uri is same as UTF8-NFC uri for NFC normalization' => [
                urlencode($utf8_nfc), urlencode($utf8_nfc), $f_NFC,
            ],
            'raw UTF8-NFC uri is same as UTF8-NFC uri for NFC normalization' => [
                urlencode($utf8_nfc), $utf8_nfc, $f_NFC,
            ],
            'url-encoded UTF8-NFD uri is same as UTF8-NFC uri for NFC normalization' => [
                urlencode($utf8_nfc), urlencode($utf8_nfd), $f_NFC,
            ],
            'raw UTF8-NFD uri is same as UTF8-NFC uri for NFC normalization' => [
                urlencode($utf8_nfc), $utf8_nfd, $f_NFC,
            ],
            'url-encoded UTF8-NFD uri gets a leading combinator for NFC normalization' => [
                urlencode($leading_combinator . $utf8_nfd_without_leading_combinator),
                urlencode($utf8_nfd_without_leading_combinator),
                $f_NFC,
            ],
            'raw UTF8-NFD uri without leading combinator gets a leading combinator for NFC normalization' => [
                urlencode($leading_combinator . $utf8_nfd_without_leading_combinator),
                $utf8_nfd_without_leading_combinator,
                $f_NFC,
            ],
        ];
    }

    /**
     * @dataProvider provideTestFilterData
     *
     * @param bool        $expected
     * @param string      $url
     * @param int         $form
     * @param null|string $charset
     */
    public function testFilter($expected, $url, $form, $charset = null)
    {
        $this->assertSame($expected, $this->subject->filter($url, $form, $charset));
    }

    // ///////////////////////////////////////
    // Tests concerning validity of utf-8 urls
    // ///////////////////////////////////////

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

        $f_NONE = NormalizerInterface::NONE;
        $f_NFC = NormalizerInterface::NFC;

        return [
            'url-encoded ISO-8859-1 uri is not a valid UTF8-NFC uri without normalization' => [
                false, urlencode($iso_8859_1), $f_NONE,
            ],
            'raw ISO-8859-1 uri is is not a valid UTF8-NFC uri without normalization' => [
                false, $iso_8859_1, $f_NONE,
            ],
            'url-encoded UTF8-NFC uri is a valid UTF8-NFC uri without normalization' => [
                true, urlencode($utf8_nfc), $f_NONE,
            ],
            'raw UTF8-NFC uri is not a valid UTF8-NFC uri without normalization' => [
                false, $utf8_nfc, $f_NONE,
            ],
            'url-encoded UTF8-NFD uri without leading combinator is not a valid uri without normalization' => [
                false, urlencode($utf8_nfd_without_leading_combinator), $f_NONE,
            ],
            'raw UTF8-NFD uri without leading combinator is not a valid uri without normalization' => [
                false, $utf8_nfd_without_leading_combinator, $f_NONE,
            ],
            'url-encoded UTF8-NFC uri is a valid UTF8-NFC uri for NFC normalization' => [
                true, urlencode($utf8_nfc), $f_NFC,
            ],
            'raw UTF8-NFC uri is not a valid UTF8-NFC uri for NFC normalization' => [
                false, $utf8_nfc, $f_NFC,
            ],
            'url-encoded UTF8-NFD uri is not a valid UTF8-NFC uri for NFC normalization' => [
                false, urlencode($utf8_nfd), $f_NFC,
            ],
            'raw UTF8-NFD uri is not a valid UTF8-NFC uri for NFC normalization' => [
                false, $utf8_nfd, $f_NFC,
            ],
            'url-encoded UTF8-NFD uri without leading combinator is not a valid uri for NFC normalization' => [
                false, urlencode($utf8_nfd_without_leading_combinator), $f_NFC,
            ],
            'raw UTF8-NFD uri without leading combinator is not a valid uri for NFC normalization' => [
                false, $utf8_nfd_without_leading_combinator, $f_NFC,
            ],
        ];
    }

    /**
     * @dataProvider provideTestIsValidData
     *
     * @param bool        $expected
     * @param string      $url
     * @param int         $form
     * @param null|string $charset
     */
    public function testIsValid($expected, $url, $form, $charset = null)
    {
        $actual = $this->subject->isValid($url, $form, $charset);
        if ($expected) {
            $this->assertTrue($actual);
        } else {
            $this->assertFalse($actual);
        }
    }
}
