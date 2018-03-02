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

use Sjorek\UnicodeNormalization\Tests\Validation\StringValidatorTest;
use Sjorek\UnicodeNormalization\Validation\Implementation\StringValidatorImpl;

/**
 * StringValidator tests
 *
 * @coversDefaultClass \Sjorek\UnicodeNormalization\Validation\Implementation\StringValidatorImpl
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class StringValidatorImplTest extends StringValidatorTest
{
    /**
     * @var StringValidatorImpl
     */
    protected $subject;

    /**
     * {@inheritDoc}
     * @see \Sjorek\UnicodeNormalization\Tests\Validation\StringValidatorTestCase::setUp()
     */
    protected function setUp()
    {
        $this->subject = new StringValidatorImpl();
    }
}
