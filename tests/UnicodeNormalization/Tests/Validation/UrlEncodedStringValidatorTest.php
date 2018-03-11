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

// DO NOT USE HERE, TO PREVENT TOO EARLY AUTOLOADING
// use Sjorek\UnicodeNormalization\Validation\StringValidator;
use Sjorek\UnicodeNormalization\Implementation\NormalizationForms;
use Sjorek\UnicodeNormalization\Validation\UrlEncodedStringValidator;

/**
 * UrlEncodedStringValidator tests.
 *
 * @coversDefaultClass \Sjorek\UnicodeNormalization\Validation\UrlEncodedStringValidator
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class UrlEncodedStringValidatorTest extends ValidationTestCase
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

    /**
     * @covers ::__construct
     *
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::__construct
     * @uses \Sjorek\UnicodeNormalization\Validation\Implementation\StringValidatorImpl::__construct
     */
    public function testConstruct()
    {
        $this->assertAttributeInstanceOf(
            \Sjorek\UnicodeNormalization\Validation\StringValidator::class, 'stringValidator', $this->subject
        );
    }

    // ////////////////////////////////////
    // Tests concerning filtered utf-8 urls
    // ////////////////////////////////////

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

        $f_NONE = NormalizationForms::NONE;
        $f_NFC = NormalizationForms::NFC;

        $leading_combinator = \Sjorek\UnicodeNormalization\Validation\StringValidator::LEADING_COMBINATOR;

        return [
            'empty uri returns early' => [
                '', '', $f_NONE,
            ],
            'ascii uri returns early' => [
                'abc', 'abc', $f_NONE,
            ],
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
     * @covers ::filter
     *
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::__construct
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::callIsNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::getFormArgument
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::isNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::normalize
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::normalizeStringTo
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::normalizeTo
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::callIsNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::callNormalize
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::isNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::normalize
     * @uses \Sjorek\UnicodeNormalization\Implementation\StrictNormalizer::callIsNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\StrictNormalizer::isNormalized
     * @uses \Sjorek\UnicodeNormalization\Validation\Implementation\StringValidatorImpl::__construct
     * @uses \Sjorek\UnicodeNormalization\Validation\Implementation\StringValidatorImpl::convertStringToUtf8
     * @uses \Sjorek\UnicodeNormalization\Validation\Implementation\StringValidatorImpl::filter
     * @uses \Sjorek\UnicodeNormalization\Validation\UrlEncodedStringValidator::__construct
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
        static::setUpValidationTestCase();

        // é
        $iso_8859_1 = hex2bin('e9');
        // é
        $utf8_nfc = hex2bin('c3a9');
        // é
        $utf8_nfd = hex2bin('65cc81');
        // é
        $utf8_nfd_without_leading_combinator = substr($utf8_nfd, 1);

        $f_NONE = NormalizationForms::NONE;
        $f_NFC = NormalizationForms::NFC;

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
     * @covers ::isValid
     *
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::__construct
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::callIsNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::getFormArgument
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::isNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::normalize
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::normalizeStringTo
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::normalizeTo
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::callIsNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::callNormalize
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::isNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::normalize
     * @uses \Sjorek\UnicodeNormalization\Implementation\StrictNormalizer::callIsNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\StrictNormalizer::isNormalized
     * @uses \Sjorek\UnicodeNormalization\Validation\Implementation\StringValidatorImpl::__construct
     * @uses \Sjorek\UnicodeNormalization\Validation\Implementation\StringValidatorImpl::convertStringToUtf8
     * @uses \Sjorek\UnicodeNormalization\Validation\Implementation\StringValidatorImpl::filter
     * @uses \Sjorek\UnicodeNormalization\Validation\UrlEncodedStringValidator::__construct
     * @uses \Sjorek\UnicodeNormalization\Validation\UrlEncodedStringValidator::filter
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
