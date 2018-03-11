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

use Sjorek\UnicodeNormalization\Exception\InvalidRuntimeFailure;
use Sjorek\UnicodeNormalization\Implementation\InvalidNormalizer;
use Sjorek\UnicodeNormalization\Tests\AbstractTestCase;

/**
 * InvalidNormalizer tests.
 *
 * @coversDefaultClass \Sjorek\UnicodeNormalization\Implementation\InvalidNormalizer
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class InvalidNormalizerTest extends AbstractTestCase
{
    /**
     * @covers ::__construct()
     * @covers ::createException
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testConstructThrowsInvalidRuntimeFailureException()
    {
        $this->expectException(InvalidRuntimeFailure::class);
        $this->expectExceptionMessage('This unicode normalizer implementation is invalid. Do not skip the autoloader!');
        $this->expectExceptionCode(1520071585);
        new InvalidNormalizer();
    }
}
