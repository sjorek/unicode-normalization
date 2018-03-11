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

// DO NOT USE HERE, TO PREVENT TOO EARLY AUTOLOADING
// use Sjorek\UnicodeNormalization\Implementation\Normalizer;
use Sjorek\UnicodeNormalization\Exception\InvalidFormFailure;
use Sjorek\UnicodeNormalization\Implementation\NormalizationForms;
use Sjorek\UnicodeNormalization\Implementation\NormalizerInterface;
use Sjorek\UnicodeNormalization\Tests\NormalizerTestCase;
use Sjorek\UnicodeNormalization\Utility\AutoloadUtility;

/**
 * normalizer implementation tests.
 *
 * @coversDefaultClass \Sjorek\UnicodeNormalization\Implementation\Normalizer
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class NormalizerTest extends NormalizerTestCase
{
    /**
     * This method is called before the first test of this test class is run.
     *
     * @beforeClass
     */
    public static function setUpNormalizationTestCase()
    {
        NormalizerTestCase::tearDownNormalizationTestCase();
        NormalizerTestCase::setUpNormalizationTestCase();
        static::$isStrictImplementation = AutoloadUtility::isStrictImplementation();
        static::$unicodeVersion = \Sjorek\UnicodeNormalization\Implementation\Normalizer::getUnicodeVersion();
        static::$normalizationForms = \Sjorek\UnicodeNormalization\Implementation\Normalizer::getNormalizationForms();
    }

    /**
     * {@inheritdoc}
     *
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp()
    {
        $this->subject = new \Sjorek\UnicodeNormalization\Implementation\Normalizer();
    }

    // ////////////////////////////////////////////////////////////////
    // tests concerning constructor
    // ////////////////////////////////////////////////////////////////

    public function provideTestConstructData()
    {
        return [
            'construct without argument' => [[], NormalizationForms::NFC],
            'construct with form argument' => [[NormalizationForms::NFD], NormalizationForms::NFD],
        ];
    }

    /**
     * @covers ::__construct
     *
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::getFormArgument
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::setForm
     * @dataProvider provideTestConstructData
     *
     * @param int[] $arguments
     * @param int   $expect
     */
    public function testConstruct(array $arguments, $expect)
    {
        $subject = new \Sjorek\UnicodeNormalization\Implementation\Normalizer(...$arguments);
        $this->assertInstanceOf(\Sjorek\UnicodeNormalization\Implementation\NormalizerInterface::class, $subject);
        $this->assertAttributeSame($expect, 'form', $subject);
    }

    // ////////////////////////////////////////////////////////////////
    // tests concerning normalization form
    // ////////////////////////////////////////////////////////////////

    public function provideTestSetFormData()
    {
        return [
            'set form to value of NONE' => [NormalizationForms::NONE, NormalizationForms::NONE],
            'set form to value of NFD' => [NormalizationForms::NFD, NormalizationForms::NFD],
            'set form to value of NFKD' => [NormalizationForms::NFKD, NormalizationForms::NFKD],
            'set form to value of NFC' => [NormalizationForms::NFC, NormalizationForms::NFC],
            'set form to value of NFKC' => [NormalizationForms::NFKC, NormalizationForms::NFKC],
            'set form to expression NONE' => ['NONE', NormalizationForms::NONE],
            'set form to expression NFD' => ['NFD', NormalizationForms::NFD],
            'set form to expression NFKD' => ['NFKD', NormalizationForms::NFKD],
            'set form to expression NFC' => ['NFC', NormalizationForms::NFC],
            'set form to expression NFKC' => ['NFKC', NormalizationForms::NFKC],
        ];
    }

    /**
     * @covers ::setForm
     *
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::__construct
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::getFormArgument
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

    public function provideTestSetFormThrowsInvalidFormFailureData()
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
                'NFD_MAC', sprintf($message2, NormalizationForms::NFD_MAC), $code2,
            ],
            'throw exception for unsupported value' => [
                NormalizationForms::NFD_MAC, sprintf($message2, NormalizationForms::NFD_MAC), $code2,
            ],
        ];
    }

    /**
     * @covers ::setForm
     *
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::__construct
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::getFormArgument
     * @uses \Sjorek\UnicodeNormalization\Utility\NormalizationUtility::parseForm
     * @dataProvider provideTestSetFormThrowsInvalidFormFailureData
     *
     * @param int    $form
     * @param string $message
     * @param int    $code
     */
    public function testSetFormThrowsInvalidFormFailure($form, $message, $code)
    {
        $this->expectException(InvalidFormFailure::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode($code);
        $this->subject->setForm($form);
    }

    /**
     * @covers ::getForm
     *
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::__construct
     */
    public function testGetForm()
    {
        $this->setProtectedProperty($this->subject, 'form', -1);
        $this->assertSame(-1, $this->subject->getForm());
    }

    /**
     * @covers ::getFormArgument
     *
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::__construct
     */
    public function testGetFormArgument()
    {
        $this->setProtectedProperty($this->subject, 'form', -1);
        $this->assertSame(
            -1, $this->callProtectedMethod($this->subject, 'getFormArgument', null)
        );
        $this->assertSame(
            NormalizationForms::NONE,
            $this->callProtectedMethod($this->subject, 'getFormArgument', NormalizationForms::NONE)
        );
        $this->expectException(InvalidFormFailure::class);
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
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::__construct
     * @uses \Sjorek\UnicodeNormalization\Utility\NormalizationUtility::detectUnicodeVersion
     */
    public function testGetUnicodeVersion()
    {
        $this->setProtectedProperty(
            \Sjorek\UnicodeNormalization\Implementation\Normalizer::class,
            'unicodeVersion',
            null
        );
        $version = \Sjorek\UnicodeNormalization\Implementation\Normalizer::getUnicodeVersion();
        $this->assertTrue(version_compare('0.0.0', $version, '<'));
        $this->assertTrue(version_compare('99.0.0', $version, '>'));
        $this->assertAttributeSame(
            $version,
            'unicodeVersion',
            \Sjorek\UnicodeNormalization\Implementation\Normalizer::class
        );
        // covers subsequent call
        $this->assertSame($version, \Sjorek\UnicodeNormalization\Implementation\Normalizer::getUnicodeVersion());
    }

    /**
     * @covers ::getNormalizationForms
     *
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::__construct
     */
    public function testGetNormalizationForms()
    {
        $forms = \Sjorek\UnicodeNormalization\Implementation\Normalizer::getNormalizationForms();
        $this->assertSame(NormalizerInterface::NORMALIZATION_FORMS, $forms);
        $this->assertSame(static::$normalizationForms, $forms);
    }
}
