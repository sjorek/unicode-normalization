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
use Sjorek\UnicodeNormalization\Utility\NormalizationUtility;
use Sjorek\UnicodeNormalization\Implementation\BaseNormalizer;

/**
 * Normalizer tests
 *
 * @coversDefaultClass \Sjorek\UnicodeNormalization\Implementation\BaseNormalizer
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class BaseNormalizerTest extends NormalizerTest
{

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp()
    {
        $this->subject = new BaseNormalizer();
    }

    /**
     * @return boolean
     */
    protected function isStrictImplementation()
    {
        return NormalizationUtility::isStrictImplementation();
    }

    /**
     * @return boolean
     */
    protected function getNormalizationForms()
    {
        return BaseNormalizer::getNormalizationForms();
    }
}
