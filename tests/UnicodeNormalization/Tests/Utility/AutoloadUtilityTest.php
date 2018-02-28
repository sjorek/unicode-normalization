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

namespace Sjorek\UnicodeNormalization\Tests\Utility;

use Sjorek\UnicodeNormalization\Implementation\MacNormalizer;
use Sjorek\UnicodeNormalization\Implementation\StrictNormalizer;
use Sjorek\UnicodeNormalization\Normalizer;
use Sjorek\UnicodeNormalization\Tests\AbstractTestCase;
use Sjorek\UnicodeNormalization\Utility\AutoloadUtility;
use Sjorek\UnicodeNormalization\Utility\NormalizationUtility;
use Sjorek\UnicodeNormalization\Validation\Implementation\StringValidatorBugfix65732;
use Sjorek\UnicodeNormalization\Validation\Implementation\StringValidatorImpl;
use Sjorek\UnicodeNormalization\Validation\StringValidator;

/**
 * AutoloadUtility tests
 *
 * @coversDefaultClass \Sjorek\UnicodeNormalization\Utility\AutoloadUtility
 *
 * @author Stephan Jorek <stephan.jorek@gmail.com>
 */
class AutoloadUtilityTest extends AbstractTestCase
{
    // ///////////////////////////////////////////////////
    // Tests concerning implementation registry
    // ///////////////////////////////////////////////////

    /**
     * @covers ::register()
     *
     * @see AutoloadUtility::register()
     */
    public function testRegister()
    {
        // should already be registered by autoloader
        $this->assertFalse(AutoloadUtility::register());
    }

    /**
     * @covers ::registerNormalizer()
     *
     * @see AutoloadUtility::registerNormalizer()
     */
    public function testRegisterNormalizer()
    {
        // should already be registered by autoloader
        $this->assertFalse(AutoloadUtility::registerNormalizer());
        $this->assertSame(
            NormalizationUtility::isNfdMacCompatible(),
            is_a(Normalizer::class, MacNormalizer::class, true)
        );
        $this->assertSame(
            NormalizationUtility::isStrictImplementation(),
            // strict implementations should not inherit the strict-enforcing facade
            !is_a(Normalizer::class, StrictNormalizer::class, true)
        );
    }

    /**
     * @covers ::registerStringValidator()
     *
     * @see AutoloadUtility::registerStringValidator()
     */
    public function testRegisterStringValidator()
    {
        // should already be registered by autoloader
        $this->assertFalse(AutoloadUtility::register());
        $this->assertTrue(is_a(StringValidator::class, StringValidatorImpl::class, true));
        $this->assertSame(
            version_compare(PHP_VERSION, '7.0.11', '<'),
            // with buggy php the implementation should inherit the bugfix facade
            is_a(StringValidator::class, StringValidatorBugfix65732::class, true)
        );
    }
}
