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

use Sjorek\UnicodeNormalization\Tests\NormalizerTest;
use Sjorek\UnicodeNormalization\Implementation\StrictNormalizer;

/**
 * Normalizer tests
 *
 * @coversDefaultClass \Sjorek\UnicodeNormalization\Implementation\StrictNormalizer
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class StrictNormalizerTest extends NormalizerTest
{

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp()
    {
        $this->subject = new StrictNormalizer();
    }

    /**
     * @return boolean
     */
    protected function getNormalizationForms()
    {
        return StrictNormalizer::getNormalizationForms();
    }
}
