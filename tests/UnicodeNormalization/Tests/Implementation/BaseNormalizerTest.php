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

namespace Sjorek\UnicodeNormalization\Tests\Implementation;

use Sjorek\UnicodeNormalization\Exception\InvalidNormalizationForm;
use Sjorek\UnicodeNormalization\Implementation\BaseNormalizer;
use Sjorek\UnicodeNormalization\Tests\NormalizerTestCase;
use Sjorek\UnicodeNormalization\Utility\NormalizationUtility;

/**
 * BaseNormalizer tests.
 *
 * @coversDefaultClass \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class BaseNormalizerTest extends NormalizerTestCase
{
    /**
     * {@inheritdoc}
     *
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp()
    {
        $this->subject = new BaseNormalizer();
    }

    // ////////////////////////////////////////////////////////////////
    // tests concerning constructor
    // ////////////////////////////////////////////////////////////////

    public function provideTestConstructData()
    {
        return [
            'construct without argument' => [[], BaseNormalizer::NFC],
            'construct with form argument' => [[BaseNormalizer::NFD], BaseNormalizer::NFD],
        ];
    }

    /**
     * @covers ::__construct
     *
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::getFormArgument
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::setForm
     * @dataProvider provideTestConstructData
     *
     * @param int[] $arguments
     * @param int   $expect
     */
    public function testConstruct(array $arguments, $expect)
    {
        $subject = new BaseNormalizer(...$arguments);
        $this->assertInstanceOf(BaseNormalizer::class, $subject);
        $this->assertAttributeSame($expect, 'form', $subject);
    }

    // ////////////////////////////////////////////////////////////////
    // tests concerning normalization form
    // ////////////////////////////////////////////////////////////////

    public function provideTestSetFormData()
    {
        return [
            'set form to value of NONE' => [BaseNormalizer::NONE, BaseNormalizer::NONE],
            'set form to value of NFD' => [BaseNormalizer::NFD, BaseNormalizer::NFD],
            'set form to value of NFKD' => [BaseNormalizer::NFKD, BaseNormalizer::NFKD],
            'set form to value of NFC' => [BaseNormalizer::NFC, BaseNormalizer::NFC],
            'set form to value of NFKC' => [BaseNormalizer::NFKC, BaseNormalizer::NFKC],
            'set form to expression NONE' => ['NONE', BaseNormalizer::NONE],
            'set form to expression NFD' => ['NFD', BaseNormalizer::NFD],
            'set form to expression NFKD' => ['NFKD', BaseNormalizer::NFKD],
            'set form to expression NFC' => ['NFC', BaseNormalizer::NFC],
            'set form to expression NFKC' => ['NFKC', BaseNormalizer::NFKC],
        ];
    }

    /**
     * @covers ::setForm
     *
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::__construct
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::getFormArgument
     * @uses \Sjorek\UnicodeNormalization\Utility\NormalizationUtility::parseForm
     * @dataProvider provideTestSetFormData
     *
     * @param int $form
     * @param int $expect
     */
    public function testSetForm($form, $expect)
    {
        $this->subject->setForm($form);
        $this->assertAttributeSame($expect, 'form', $this->subject);
    }

    public function provideTestSetFormThrowsInvalidNormalizationFormData()
    {
        $message1 = 'Invalid unicode normalization form value: %s';
        $code1 = 1398603947;

        $message2 = 'Unsupported unicode-normalization form: %s.';
        $code2 = 1398603948;

        return [
            'throw exception for invalid expression' => [
                'nonsense', sprintf($message1, 'nonsense'), $code1,
            ],
            'throw exception for unsupported value' => [
                -1, sprintf($message2, '-1'), $code2,
            ],
            'throw exception for unsupported expression' => [
                'NFD_MAC', sprintf($message2, BaseNormalizer::NFD_MAC), $code2,
            ],
            'throw exception for unsupported value' => [
                BaseNormalizer::NFD_MAC, sprintf($message2, BaseNormalizer::NFD_MAC), $code2,
            ],
        ];
    }

    /**
     * @covers ::setForm
     *
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::__construct
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::getFormArgument
     * @uses \Sjorek\UnicodeNormalization\Utility\NormalizationUtility::parseForm
     * @dataProvider provideTestSetFormThrowsInvalidNormalizationFormData
     *
     * @param int    $form
     * @param string $message
     * @param int    $code
     */
    public function testSetFormThrowsInvalidNormalizationForm($form, $message, $code)
    {
        $this->expectException(InvalidNormalizationForm::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode($code);
        $this->subject->setForm($form);
    }

    /**
     * @covers ::getForm
     *
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::__construct
     */
    public function testGetForm()
    {
        $this->setProtectedProperty($this->subject, 'form', 0);
        $this->assertSame(0, $this->subject->getForm());
    }

    /**
     * @covers ::getFormArgument
     *
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::__construct
     */
    public function testGetFormArgument()
    {
        $this->setProtectedProperty($this->subject, 'form', -1);
        $this->assertSame(
            -1, $this->callProtectedMethod($this->subject, 'getFormArgument', null)
        );
        $this->assertSame(
            BaseNormalizer::NONE,
            $this->callProtectedMethod($this->subject, 'getFormArgument', BaseNormalizer::NONE)
        );
        $this->expectException(InvalidNormalizationForm::class);
        $this->expectExceptionMessage('Unsupported unicode-normalization form: -1.');
        $this->expectExceptionCode(1398603948);
        $this->callProtectedMethod($this->subject, 'getFormArgument', -1);
    }

    // ////////////////////////////////////////////////////////////////
    // tests concerning capabilities
    // ////////////////////////////////////////////////////////////////

    /**
     * @covers ::getUnicodeVersion
     *
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::__construct
     * @uses \Sjorek\UnicodeNormalization\Utility\NormalizationUtility::detectUnicodeVersion
     */
    public function testGetUnicodeVersion()
    {
        $version = BaseNormalizer::getUnicodeVersion();
        $this->assertTrue(version_compare('0.0.0', $version, '<'));
        $this->assertTrue(version_compare('99.0.0', $version, '>'));
        $this->assertAttributeSame(BaseNormalizer::getUnicodeVersion(), 'unicodeVersion', BaseNormalizer::class);
    }

    /**
     * @covers ::getNormalizationForms
     *
     * @uses \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer::__construct
     */
    public function testGetNormalizationForms()
    {
        $this->assertSame(BaseNormalizer::NORMALIZATION_FORMS, BaseNormalizer::getNormalizationForms());
    }

    // ////////////////////////////////////////////////////////////////
    // utility methods
    // ////////////////////////////////////////////////////////////////

    /**
     * @return bool
     */
    protected static function isStrictImplementation()
    {
        return NormalizationUtility::isStrictImplementation();
    }

    /**
     * @return string
     */
    protected static function getUnicodeVersion()
    {
        return BaseNormalizer::getUnicodeVersion();
    }

    /**
     * @return bool
     */
    protected static function getNormalizationForms()
    {
        return BaseNormalizer::getNormalizationForms();
    }
}
