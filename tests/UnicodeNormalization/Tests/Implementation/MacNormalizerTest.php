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

use Sjorek\UnicodeNormalization\Implementation\MacNormalizer;
use Sjorek\UnicodeNormalization\Tests\NormalizerTest;

/**
 * Normalizer tests.
 *
 * @coversDefaultClass \Sjorek\UnicodeNormalization\Implementation\MacNormalizer
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class MacNormalizerTest extends NormalizerTest
{
    /**
     * {@inheritdoc}
     *
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp()
    {
        $this->subject = new MacNormalizer();
    }

    /**
     * @return string
     */
    protected static function getUnicodeVersion()
    {
        return MacNormalizer::getUnicodeVersion();
    }

    /**
     * @return bool
     */
    protected static function getNormalizationForms()
    {
        return MacNormalizer::getNormalizationForms();
    }
}
