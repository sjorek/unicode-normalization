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
// use Sjorek\UnicodeNormalization\Implementation\StrictNormalizer;
use Sjorek\UnicodeNormalization\Tests\NormalizerTestCase;

/**
 * strict normalizer implementation tests.
 *
 * @coversDefaultClass \Sjorek\UnicodeNormalization\Implementation\StrictNormalizer
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class StrictNormalizerTest extends NormalizerTestCase
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
        static::$unicodeVersion = \Sjorek\UnicodeNormalization\Implementation\StrictNormalizer::getUnicodeVersion();
        static::$normalizationForms = \Sjorek\UnicodeNormalization\Implementation\StrictNormalizer::getNormalizationForms();
    }

    /**
     * {@inheritdoc}
     *
     * @see \PHPUnit\Framework\TestCase::setUp()
     */
    protected function setUp()
    {
        $this->subject = new \Sjorek\UnicodeNormalization\Implementation\StrictNormalizer();
    }
}
