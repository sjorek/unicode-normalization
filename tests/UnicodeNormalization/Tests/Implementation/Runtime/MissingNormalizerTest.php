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

namespace Sjorek\UnicodeNormalization\Tests\Implementation\Runtime;

use Sjorek\UnicodeNormalization\Exception\InvalidRuntimeFailure;
use Sjorek\UnicodeNormalization\Implementation\Runtime\MissingNormalizer;
use Sjorek\UnicodeNormalization\Tests\AbstractTestCase;

/**
 * Normalizer tests.
 *
 * @coversDefaultClass \Sjorek\UnicodeNormalization\Implementation\Runtime\MissingNormalizer
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class MissingNormalizerTest extends AbstractTestCase
{
    /**
     * @covers ::normalize
     * @covers ::createException
     */
    public function testNormalizeThrowsInvalidRuntimeFailure()
    {
        $this->expectInvalidRuntimeFailure();
        MissingNormalizer::normalize(null);
    }

    /**
     * @covers ::isNormalized
     * @covers ::createException
     */
    public function testIsNormalizedThrowsInvalidRuntimeFailure()
    {
        $this->expectInvalidRuntimeFailure();
        MissingNormalizer::isNormalized(null);
    }

    protected function expectInvalidRuntimeFailure()
    {
        $this->expectException(InvalidRuntimeFailure::class);
        $this->expectExceptionMessageRegExp('/^A unicode normalizer implementation is missing\\./');
        $this->expectExceptionCode(1519658533);
    }
}
