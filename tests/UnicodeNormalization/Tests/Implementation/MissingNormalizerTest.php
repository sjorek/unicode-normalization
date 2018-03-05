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

use Sjorek\UnicodeNormalization\Implementation\MissingNormalizer;
use Sjorek\UnicodeNormalization\Tests\AbstractTestCase;

/**
 * Normalizer tests.
 *
 * @coversDefaultClass \Sjorek\UnicodeNormalization\Implementation\MissingNormalizer
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class MissingNormalizerTest extends AbstractTestCase
{
    /**
     * @covers ::normalize
     *
     * @expectedException              \Sjorek\UnicodeNormalization\Exception\InvalidNormalizerImplementation
     * @expectedExceptionMessageRegExp /^A unicode normalizer implementation is missing\./
     * @expectedExceptionCode          1519658533
     */
    public function testNormalize()
    {
        MissingNormalizer::normalize(null);
    }

    /**
     * @covers ::isNormalized
     *
     * @expectedException              \Sjorek\UnicodeNormalization\Exception\InvalidNormalizerImplementation
     * @expectedExceptionMessageRegExp /^A unicode normalizer implementation is missing\./
     * @expectedExceptionCode          1519658533
     */
    public function testIsNormalized()
    {
        MissingNormalizer::isNormalized(null);
    }
}
