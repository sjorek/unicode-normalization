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

use Sjorek\UnicodeNormalization\Implementation\NormalizerInterface;
use Sjorek\UnicodeNormalization\Tests\Validation\StringValidatorTestCase;
use Sjorek\UnicodeNormalization\Validation\Implementation\StringValidatorImpl;

/**
 * StringValidator tests.
 *
 * @coversDefaultClass \Sjorek\UnicodeNormalization\Validation\Implementation\StringValidatorImpl
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class StringValidatorImplTest extends StringValidatorTestCase
{
    /**
     * @var StringValidatorImpl
     */
    protected $subject;

    /**
     * {@inheritdoc}
     *
     * @see \Sjorek\UnicodeNormalization\Tests\Validation\StringValidatorTestCase::setUp()
     */
    protected function setUp()
    {
        $this->subject = new StringValidatorImpl();
    }

    /**
     * @covers ::__construct
     *
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::__construct
     */
    public function testConstruct()
    {
        $this->assertAttributeInstanceOf(NormalizerInterface::class, 'normalizer', $this->subject);
    }

    /**
     * @covers ::convertStringToUtf8
     *
     * @uses \Sjorek\UnicodeNormalization\Implementation\Normalizer::__construct
     * @uses \Sjorek\UnicodeNormalization\Validation\Implementation\StringValidatorImpl::__construct
     */
    public function testConvertStringToUtf8()
    {
        // é
        $iso_8859_1 = hex2bin('e9');
        // é
        $utf8 = hex2bin('c3a9');
        $this->assertSame($utf8, $this->callProtectedMethod($this->subject, 'convertStringToUtf8', $iso_8859_1));
        $invalid = chr(0b11111111);
        $this->assertFalse($this->callProtectedMethod($this->subject, 'convertStringToUtf8', $invalid));
    }
}
