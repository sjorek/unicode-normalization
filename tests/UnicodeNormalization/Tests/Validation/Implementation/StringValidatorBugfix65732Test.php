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

namespace Sjorek\UnicodeNormalization\Tests\Validation\Implementation;

use Sjorek\UnicodeNormalization\Implementation\NormalizationForms;
use Sjorek\UnicodeNormalization\Tests\Validation\StringValidatorTestCase;
use Sjorek\UnicodeNormalization\Validation\Implementation\StringValidatorBugfix65732;

/**
 * StringValidator tests.
 *
 * @coversDefaultClass \Sjorek\UnicodeNormalization\Validation\Implementation\StringValidatorBugfix65732
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class StringValidatorBugfix65732Test extends StringValidatorTestCase
{
    /**
     * @var StringValidatorBugfix65732
     */
    protected $subject;

    /**
     * {@inheritdoc}
     *
     * @see \Sjorek\UnicodeNormalization\Tests\Validation\StringValidatorTestCase::setUp()
     */
    protected function setUp()
    {
        $this->subject = new StringValidatorBugfix65732();
    }

    // ///////////////////////////////////////
    // Tests concerning filtered utf-8 strings
    // ///////////////////////////////////////

    public function provideTestFilterBug65732Data()
    {
        static::setUpValidationTestCase();

        // é
        $utf8_nfc = hex2bin('c3a9');
        // Test https://bugs.php.net/65732
        $bugs_65732 = "\n\r" . $utf8_nfc . "\n\r";

        $f_NONE = NormalizationForms::NONE;
        $f_NFC = NormalizationForms::NFC;

        return [
            'test bug https://bugs.php.net/65732 without normalization' => [
                $bugs_65732, $bugs_65732, $f_NONE,
            ],
            'test bug https://bugs.php.net/65732 for NFC normalization' => [
                $bugs_65732, $bugs_65732, $f_NFC,
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
     * @uses \Sjorek\UnicodeNormalization\Utility\NormalizationUtility::parseForm
     * @uses \Sjorek\UnicodeNormalization\Validation\Implementation\StringValidatorImpl::__construct
     * @uses \Sjorek\UnicodeNormalization\Validation\Implementation\StringValidatorImpl::convertStringToUtf8
     * @uses \Sjorek\UnicodeNormalization\Validation\Implementation\StringValidatorImpl::filter
     * @dataProvider provideTestFilterBug65732Data
     *
     * @param bool        $expected
     * @param string      $string
     * @param int         $form
     * @param null|string $charset
     *
     * @see https://bugs.php.net/65732
     */
    public function testFilterBug65732($expected, $string, $form, $charset = null)
    {
        $this->testFilter($expected, $string, $form, $charset);
    }

    // //////////////////////////////////////////
    // Tests concerning validity of utf-8 strings
    // //////////////////////////////////////////

    public function provideTestIsValidBug65732Data()
    {
        static::setUpValidationTestCase();

        // é
        $utf8_nfc = hex2bin('c3a9');
        // Test https://bugs.php.net/65732
        $bugs_65732 = "\n\r" . $utf8_nfc . "\n\r";

        $f_NONE = NormalizationForms::NONE;
        $f_NFC = NormalizationForms::NFC;

        return [
            'test bug https://bugs.php.net/65732 without normalization' => [
                true, $bugs_65732, $f_NONE,
            ],
            'test bug https://bugs.php.net/65732 for NFC normalization' => [
                true, $bugs_65732, $f_NFC,
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
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::normalizeStringTo
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::normalizeTo
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::normalize
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::callIsNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::callNormalize
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::isNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\MacNormalizer::normalize
     * @uses \Sjorek\UnicodeNormalization\Implementation\StrictNormalizer::callIsNormalized
     * @uses \Sjorek\UnicodeNormalization\Implementation\StrictNormalizer::isNormalized
     * @uses \Sjorek\UnicodeNormalization\Utility\NormalizationUtility::parseForm
     * @uses \Sjorek\UnicodeNormalization\Validation\Implementation\StringValidatorBugfix65732::filter
     * @uses \Sjorek\UnicodeNormalization\Validation\Implementation\StringValidatorImpl::__construct
     * @uses \Sjorek\UnicodeNormalization\Validation\Implementation\StringValidatorImpl::convertStringToUtf8
     * @uses \Sjorek\UnicodeNormalization\Validation\Implementation\StringValidatorImpl::filter
     * @dataProvider provideTestIsValidBug65732Data
     *
     * @param bool        $expected
     * @param string      $string
     * @param int         $form
     * @param null|string $charset
     *
     * @see https://bugs.php.net/65732
     */
    public function testIsValidBug65732($expected, $string, $form, $charset = null)
    {
        $this->testIsValid($expected, $string, $form, $charset);
    }
}
